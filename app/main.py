import logging
from fastapi import FastAPI, Request, Depends, HTTPException, Form, UploadFile, File, Query
from fastapi.templating import Jinja2Templates
from fastapi.staticfiles import StaticFiles
from sqlalchemy import desc, or_, func
from sqlalchemy.orm import Session
from dotenv import load_dotenv
from fastapi.responses import StreamingResponse, RedirectResponse, HTMLResponse, JSONResponse, FileResponse
from authlib.integrations.starlette_client import OAuth
from starlette.middleware.sessions import SessionMiddleware
import os
import random
import string
import secrets
import qrcode
from io import BytesIO
from .Model import *
from bs4 import BeautifulSoup
import requests
from datetime import datetime, timedelta
from requests.exceptions import SSLError, RequestException
from collections import Counter

# 防止DDoS
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.errors import RateLimitExceeded
from slowapi.util import get_remote_address
from slowapi.middleware import SlowAPIMiddleware

#python3 引用模組的寫法要用 from .檔名 import 方法／類別
from .schemas import *
from .Model import URL, Click, OGData, get_db, get_url_by_short_code, record_click 
from .redis_connection import *  # 从 redis_connection 导入函数

import mimetypes
from urllib.parse import urlparse
from typing import List

# 初始化Limiter
limiter = Limiter(key_func=get_remote_address)
app = FastAPI()

# 配置 logging 來記錄錯誤訊息
logging.basicConfig(level=logging.ERROR)
logger = logging.getLogger(__name__)

# 設定最大文件大小為 500KB
MAX_FILE_SIZE = 500 * 1024  # 500KB

templates = Jinja2Templates(directory=os.path.join(os.getcwd(), "app/templates"))
app.mount("/static", StaticFiles(directory=os.path.join(os.getcwd(), "app/static")), name="static")

# 加入Limiter的插件
app.add_middleware(SlowAPIMiddleware)
# 将速率限制的异常处理程序添加到 FastAPI 应用中
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)


# 加載 .env 文件中的變量
load_dotenv()

# 從環境變量獲取 DOMAIN
DOMAIN = os.getenv("DOMAIN")

# 加入会话中间件以存储 OAuth 的状态
app.add_middleware(SessionMiddleware, secret_key=os.getenv("SECRET_KEY"))

nonce = secrets.token_urlsafe(16)

# 初始化 OAuth 客户端
oauth = OAuth()
oauth.register(
    name="google",
    client_id=os.getenv("GOOGLE_CLIENT_ID"),
    client_secret=os.getenv("GOOGLE_CLIENT_SECRET"),
    authorize_url="https://accounts.google.com/o/oauth2/auth",
    authorize_params=None,
    access_token_url="https://oauth2.googleapis.com/token",
    access_token_params=None,
    refresh_token_url=None,
    redirect_uri=os.getenv("GOOGLE_AUTH_REDIRECT_URI"),
    client_kwargs={"scope": "openid email profile", "nonce": nonce},
    jwks_uri="https://www.googleapis.com/oauth2/v3/certs",
    server_metadata_url="https://accounts.google.com/.well-known/openid-configuration"
)

# 处理根目录的 robots.txt 文件
@app.get("/robots.txt")
async def robots():
    file_path = os.path.join(os.getcwd(), "app/robots.txt")  # 确认路径指向项目根目录的 robots.txt
    return FileResponse(file_path)

# 处理根目录的 ads.txt 文件
@app.get("/ads.txt")
async def ads():
    file_path = os.path.join(os.getcwd(), "app/ads.txt")  # 确认路径指向项目根目录的 ads.txt
    return FileResponse(file_path)

# 登入路由
@app.get("/login")
async def login(request: Request):
    redirect_uri = os.getenv("GOOGLE_AUTH_REDIRECT_URI")
    return await oauth.google.authorize_redirect(request, redirect_uri, scope="openid email profile")

# 登出路由
@app.get("/logout")
async def logout(request: Request):
    # 清除用户的会话信息
    request.session.clear()
    
    # 重定向到主页或其他页面
    return RedirectResponse(url="/")

# 回调路由
@app.get("/auth/callback")
async def auth_callback(request: Request, db: Session = Depends(get_db)):
    token = await oauth.google.authorize_access_token(request)

    # 直接从 token 中获取用户信息
    userinfo = token['userinfo']
    email = userinfo['email']
    name = userinfo['name']
    
    # 在数据库中查找用户，如果不存在则创建
    user = db.query(User).filter(User.email == email).first()
    if not user:
        new_user = User(email=email, name=name)
        db.add(new_user)
        db.commit()
        db.refresh(new_user)
        user = new_user

    # 设置用户的会话信息（假设你有SessionMiddleware来管理会话）
    request.session['user_id'] = user.id
    request.session['user_email'] = email
    request.session['is_active'] = user.is_active

    # 重定向到 dashboard
    return RedirectResponse(url="/dashboard")

# 登入後路由，支援搜索功能
@app.get("/dashboard")
@limiter.limit("10/minute")
async def dashboard(request: Request, page: int = Query(1, ge=1), search_query: str = None, db: Session = Depends(get_db)):
    is_active = request.session.get('is_active')
    
    if  is_active == False:
        return RedirectResponse(url="/?error=inactive", status_code=302)
    user_id = request.session.get('user_id')
    if not user_id:
        # raise HTTPException(status_code=401, detail="未登入")
        return RedirectResponse(url="/", status_code=303)
    
    page_size = 10  # 每頁10筆
    offset = (page - 1) * page_size

    # 依user_id取得用戶短網址
    query = db.query(URL).filter(URL.user_id == user_id)

    if search_query:
        search_query = f"%{search_query}%"
        query = query.filter(
            # 使用 or_ 来组合多个条件
            or_(
                URL.og_data.has(OGData.title.ilike(search_query)),  # OG Title 使用 has
                URL.tags.any(Tag.name.ilike(search_query)),  # Tags 使用 any
                URL.utm_data.any(  # UTM 數據的 any 查询
                    (UTMData.utm_source.ilike(search_query)) | 
                    (UTMData.utm_medium.ilike(search_query)) | 
                    (UTMData.utm_campaign.ilike(search_query)) | 
                    (UTMData.utm_term.ilike(search_query)) | 
                    (UTMData.utm_content.ilike(search_query))
                )
            )
        )
    
    # 查詢短網址資料，按 created_at 降序排序並分頁
    total_urls = query.count()

    urls = query.order_by(desc(URL.created_at)).offset(offset).limit(page_size).all()

    # 計算總頁數
    total_pages = (total_urls + page_size - 1) // page_size  # 向上取整

    # 查詢每個 URL 的點擊數
    url_clicks = {}
    for url in urls:
        click_count = db.query(Click).filter(Click.url_id == url.id).count()
        url_clicks[url.short_code] = click_count

    user_id = request.session.get('user_id')
    user_email = request.session.get('user_email')

    return templates.TemplateResponse("dashboard.html", {
        "request": request, 
        "urls": urls, 
        "url_clicks": url_clicks, 
        "current_page": page,
        "total_pages": total_pages,
        "user_email": user_email,
        "DOMAIN": DOMAIN
    })

# 解析網址OG（建立時）
@app.post("/parse_og_data")
@limiter.limit("10/minute")
async def parse_og_data(request: Request):
    data = await request.json()
    url = data['url']
    
    response = requests.get(url)
    response.encoding = 'utf-8'
    soup = BeautifulSoup(response.text, 'html.parser')
    
    og_title = soup.find('meta', property='og:title')
    og_description = soup.find('meta', property='og:description')
    og_image = soup.find('meta', property='og:image')

    return {
        "title": og_title['content'] if og_title else '',
        "description": og_description['content'] if og_description else '',
        "image": og_image['content'] if og_image else ''
    }


# 登入後建立短網址
@app.post("/create_short_url/")
@limiter.limit("5/minute")
async def create_short_url(
    request: Request,
    db: Session = Depends(get_db),  # 資料庫依賴
    original_url: str = Form(...),
    title: str = Form(None),
    description: str = Form(None),
    og_image_url: str = Form(None),
    image: UploadFile = File(None),
    direct_redirect: bool = Form(False),
    tags: str = Form(''),
    utm_source: str = Form(None),
    utm_medium: str = Form(None),
    utm_campaign: str = Form(None),
    utm_term: str = Form(None),
    utm_content: str = Form(None)
):
    try:
        user_id = request.session.get('user_id')  # 取得登入用戶的 user_id
        if not user_id:
            raise HTTPException(status_code=401, detail="未登入")

        tag_list = tags.split(',')  # 將標籤以逗號分隔處理成列表

        # 生成短碼
        short_code = ''.join(random.choices(string.ascii_letters + string.digits, k=8))

        # 確保 uploads 目錄存在
        upload_dir = "app/static/uploads"
        if not os.path.exists(upload_dir):
            os.makedirs(upload_dir)

        # 處理圖片上傳或 og:image
        image_url = handle_image_upload(image, og_image_url, upload_dir)

        # 先寫入 URL 表
        new_url = URL(
            original_url=original_url,
            short_code=short_code,
            user_id=user_id,
            direct_redirect=direct_redirect
        )
        db.add(new_url)
        db.flush()  # 在寫入 og_data 和其他關聯數據前，先刷新以獲得 new_url.id

        # 寫入 OG 資料表
        og_data = OGData(
            url_id=new_url.id,
            title=title,
            description=description,
            image=image_url  # 將解析到的圖片或上傳的圖片路徑保存
        )
        db.add(og_data)

        # 寫入 UTM 和標籤數據
        if any([utm_source, utm_medium, utm_campaign, utm_term, utm_content]):
            utm_data = UTMData(
                url_id=new_url.id,
                utm_source=utm_source,
                utm_medium=utm_medium,
                utm_campaign=utm_campaign,
                utm_term=utm_term,
                utm_content=utm_content
            )
            db.add(utm_data)

        # 處理標籤邏輯，將標籤與 URL 關聯
        for tag_name in tag_list:
            tag_name = tag_name.strip()  # 移除多餘空格
            if tag_name:
                # 查找是否已有此標籤，沒有則新增
                tag = db.query(Tag).filter(Tag.name == tag_name).first()
                if not tag:
                    tag = Tag(name=tag_name)
                    db.add(tag)
                    db.flush()  # 使用 flush() 來更新 tag.id 而不立即提交
                # 關聯此 URL 與 Tag
                new_url.tags.append(tag)

        # 提交所有變更
        db.commit()

        # 將 OG Data 和 URL 資訊寫入快取
        set_cached_url(short_code, original_url, new_url.id, og_title=title, og_description=description, og_image=image_url, direct_redirect=direct_redirect)

        return {"message": "短網址已建立", "short_url": f"https://{DOMAIN}/{new_url.short_code}"}

    except HTTPException as http_exc:
        # 捕捉 HTTPException，並返回具體錯誤
        logger.error(f"HTTPException occurred: {http_exc.detail}")
        raise http_exc
    except Exception as e:
        # 捕捉其他例外情況，記錄詳細錯誤，並回滾資料庫
        logger.error(f"Error occurred while creating short URL: {str(e)}", exc_info=True)
        db.rollback()  # 回滾所有未提交的變更
        return JSONResponse(
            status_code=500,
            content={"message": "建立短網址時發生錯誤，請重試", "error": str(e)}
        )

# 提供指定短碼的短網址數據
@app.get("/get_url_data/{short_code}")
async def get_url_data(short_code: str, db: Session = Depends(get_db)):
    url_data = db.query(URL).filter(URL.short_code == short_code).first()
    
    if not url_data:
        raise HTTPException(status_code=404, detail="查無此短網址")
    
    og_data = url_data.og_data
    utm_data = url_data.utm_data
    
    return {
        "original_url": url_data.original_url,
        "title": og_data.title if og_data else '',
        "description": og_data.description if og_data else '',
        "image": og_data.image if og_data else '',  # 返回 image 欄位
        "direct_redirect": url_data.direct_redirect,
        "tags": ','.join([tag.name for tag in url_data.tags]) if url_data.tags else '',
        "utm_source": utm_data.utm_source if utm_data else '',
        "utm_medium": utm_data.utm_medium if utm_data else '',
        "utm_campaign": utm_data.utm_campaign if utm_data else '',
        "utm_term": utm_data.utm_term if utm_data else '',
        "utm_content": utm_data.utm_content if utm_data else ''
    }

# 更新短網址
@app.post("/update_url")
async def update_url(
    short_code: str = Form(...),
    title: str = Form(None),
    description: str = Form(None),
    og_image_url: str = Form(None),  # 確保這個字段在表單裡有傳遞
    image: UploadFile = File(None),  # 用於上傳圖片的字段
    direct_redirect: bool = Form(False),  # 获取 direct_redirect 的值
    tags: str = Form(''),
    utm_source: str = Form(None),
    utm_medium: str = Form(None),
    utm_campaign: str = Form(None),
    utm_term: str = Form(None),
    utm_content: str = Form(None),
    db: Session = Depends(get_db)
):
    # 獲取對應的URL
    url_data = db.query(URL).filter(URL.short_code == short_code).first()
    if not url_data:
        raise HTTPException(status_code=404, detail="短網址未找到")
    
    # 獲取對應的OGData
    og_data = db.query(OGData).filter(OGData.url_id == url_data.id).first()
    if not og_data:
        raise HTTPException(status_code=404, detail="OG資料未找到")

    # 更新OGData標題和描述
    og_data.title = title
    og_data.description = description

    # 處理圖片上傳或 og:image
    upload_dir = "app/static/uploads"  # 保證圖片上傳到正確的靜態資源目錄

    # 確保 uploads 目錄存在
    if not os.path.exists(upload_dir):
        os.makedirs(upload_dir)

    # 圖片更新處理
    if image and image.filename:
        # 如果用户上傳了新圖片，處理上傳
        image_filename = f"{secrets.token_hex(8)}_{image.filename}"
        image_path = os.path.join(upload_dir, image_filename)
        with open(image_path, "wb") as buffer:
            buffer.write(image.file.read())
        og_data.image = f"/static/uploads/{image_filename}"  # 更新圖片欄位
    elif og_image_url and (og_image_url.startswith("https://") or og_image_url.startswith("https://")):
        # 如果 og_image_url 是有效的 URL，保留原始 og:image 的網址
        og_data.image = og_image_url  # 更新og:image欄位
    else:
        # 保持原本的圖片不變
        og_data.image = og_data.image  # 如果沒有上傳圖片或og_image_url，保持不變

    url_data.direct_redirect = direct_redirect
    # 提交數據庫變更
    db.commit()

    # return {"message": "短網址更新成功"}
    # 成功更新後，返回帶有 JavaScript 的 HTML
    success_html = """
    <html>
        <body>
            <script>
                showModal('短網址更新成功！', "成功");
                window.location.href = '/dashboard';
            </script>
        </body>
    </html>
    """
    return HTMLResponse(content=success_html)

# 分析點擊瀏覽器比例
@app.get("/analyze_clicks/{short_code}")
async def analyze_clicks(short_code: str, db: Session = Depends(get_db)):
    # 查找對應的 URL
    url = db.query(URL).filter(URL.short_code == short_code).first()
    if not url:
        raise HTTPException(status_code=404, detail="查無此短網址")

    # 查找該短網址的所有點擊數據
    clicks = db.query(Click).filter(Click.url_id == url.id).all()

    # 分析 user_agent 的統計數據
    user_agent_counter = Counter([click.user_agent for click in clicks])

    # 轉換為百分比和點擊數
    total_clicks = sum(user_agent_counter.values())
    user_agent_data = []
    
    for user_agent, count in user_agent_counter.items():
        browser_name = simplify_user_agent(user_agent)
        percentage = (count / total_clicks) * 100 if total_clicks > 0 else 0
        user_agent_data.append({
            "browser": browser_name,
            "clicks": count,
            "percentage": round(percentage, 2)
        })

    # 聚合同类浏览器的统计数据
    simplified_user_agent_data = {}
    for data in user_agent_data:
        browser = data["browser"]
        if browser not in simplified_user_agent_data:
            simplified_user_agent_data[browser] = {"clicks": 0, "percentage": 0}
        simplified_user_agent_data[browser]["clicks"] += data["clicks"]
        simplified_user_agent_data[browser]["percentage"] += data["percentage"]

    # 構造返回的數據格式
    response_data = {
        "total_clicks": total_clicks,
        "simplified_user_agent_data": simplified_user_agent_data
    }

    return response_data

# 未登入用戶首頁
@app.get("/")
async def read_index(request: Request, db: Session = Depends(get_db)):

    in_active = request.session.get('inactive')
    # 檢查使用者是否已登入
    user_id = request.session.get('user_id')
    
    if user_id and in_active == True:
        # 使用者已登入，重定向到 /dashboard
        return RedirectResponse(url="/dashboard")
    
    # 使用者未登入，顯示首頁
    return templates.TemplateResponse("index.html", {"request": request})

#不登入生成短網址
@app.post("/shorten/")
@limiter.limit("5/minute")
async def shorten_url(request: Request, url_request: URLRequest, db: Session = Depends(get_db)):
    url_str = str(url_request.url)
    
    # 使用大小寫字母和數字的組合生成 8 位長度的 short_code
    characters = string.ascii_letters + string.digits  # 包含 a-z, A-Z, 0-9
    short_code = ''.join(random.choices(characters, k=8))

    # 檢查短碼是否已存在，確保不重複
    existing_url = db.query(URL).filter(URL.short_code == short_code).first()
    while existing_url:
        short_code = ''.join(random.choices(characters, k=8))
        existing_url = db.query(URL).filter(URL.short_code == short_code).first()
    
    new_url = URL(original_url=url_str, short_code=short_code)
    db.add(new_url)
    db.commit()
    db.refresh(new_url)
    
    return {"short_url": f"https://{DOMAIN}/{new_url.short_code}", "short_code": short_code}

# 刪除短網址
@app.delete("/delete_url/{short_code}")
async def delete_url(short_code: str, db: Session = Depends(get_db)):
    url_data = db.query(URL).filter(URL.short_code == short_code).first()
    
    if not url_data:
        raise HTTPException(status_code=404, detail="查無此短網址")
    
    # 手動刪除關聯的 og_data
    og_data = db.query(OGData).filter(OGData.url_id == url_data.id).first()
    if og_data:
        db.delete(og_data)
    
    # 刪除 URL 資料
    db.delete(url_data)
    
    db.commit()
    return {"message": "短網址已刪除"}

#生成QRCode
@app.get("/generate_qrcode/{short_code}")
@limiter.limit("10/minute")
async def generate_qrcode(request: Request, short_code: str):
    url = f"https://{DOMAIN}/{short_code}"
    img = qrcode.make(url)
    buf = BytesIO()
    img.save(buf)
    buf.seek(0)
    return StreamingResponse(buf, media_type="image/png")

# 指派為管理員
@app.post("/admin/assignadmin")
async def assign_admin(request: AdminRequest, db: Session = Depends(get_db)):
    user = db.query(User).filter(User.id == request.user_id).first()
    if not user:
        return {"error": "User not found"}
    
    user.is_admin = True
    db.commit()
    
    return {"status": "User assigned as admin", "is_admin": user.is_admin}

# 解除指派管理員
@app.post("/admin/deassignadmin")
async def deassign_admin(request: AdminRequest, db: Session = Depends(get_db)):
    user = db.query(User).filter(User.id == request.user_id).first()
    if not user:
        return {"error": "User not found"}
    
    user.is_admin = False
    db.commit()
    
    return {"status": "User deassigned from admin", "is_admin": user.is_admin}

# 停用指定用戶
@app.post("/admin/deactivate")
async def deactivate_user(request: AdminRequest, db: Session = Depends(get_db)):
    # 查询用户是否存在
    user = db.query(User).filter(User.id == request.user_id).first()
    
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    
    # 将用户状态设为停用
    user.is_active = False
    db.commit()

    return {"status": "User deactivated", "user_id": user.id}

# 啟用指定用戶
@app.post("/admin/activate")
async def activate_user(request: AdminRequest, db: Session = Depends(get_db)):
    # 查询用户是否存在
    user = db.query(User).filter(User.id == request.user_id).first()
    
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    
    # 将用户状态设为停用
    user.is_active = True
    db.commit()

    return {"status": "User activated", "user_id": user.id}


# 模擬一個身份驗證系統來獲取當前登錄用戶
# 實際情況可以使用 JWT 或 OAuth2 來認證用戶
def get_current_user(token: str, db: Session = Depends(get_db)):
    # 假設 `token` 解碼後獲取到用戶的ID
    user = db.query(User).filter(User.token == token).first()  # 這是假設 token 存在 User 模型
    if not user:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid authentication credentials")
    return user

# 檢查用戶是否爲管理員
def admin_required(current_user: User = Depends(get_current_user)):
    if not current_user.is_admin:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Access denied")
    return current_user

# 縮網址圖片處理
def handle_image_upload(image: UploadFile, og_image_url: str, upload_dir: str):
    # 如果提供了 og:image 網址，優先使用該網址
    if og_image_url:
        return og_image_url

    # 如果有上傳圖片，檢查圖片大小
    if image:
        contents = image.file.read()
        file_size = len(contents)

        # 檢查文件大小是否超過 500KB
        if file_size > 500 * 1024:
            raise HTTPException(status_code=413, detail="文件太大，最大允許大小為 500KB")

        image_filename = f"{secrets.token_hex(8)}_{image.filename}"
        image_path = os.path.join(upload_dir, image_filename)
        with open(image_path, "wb") as f:
            f.write(contents)
        return f"/static/uploads/{image_filename}"

    return None

# 取得圖片的副檔名（通過Content-Type）
def get_image_extension(url):
    try:
        response = requests.head(url, timeout=5)
        response.raise_for_status()  # 确保请求成功
        content_type = response.headers.get('Content-Type')
        ext = mimetypes.guess_extension(content_type)
        return ext
    except (requests.RequestException, KeyError):
        return None

# 从 URL 提取圖片副檔名
def get_extension_from_url(url):
    parsed_url = urlparse(url)
    _, ext = os.path.splitext(parsed_url.path)
    return ext if ext else None

def simplify_user_agent(user_agent):
    if "Firefox" in user_agent:
        return "Firefox"
    elif "Edg" in user_agent:
        return "Edge"
    elif "Chrome" in user_agent and "Safari" in user_agent and "Edg" not in user_agent:
        if "Chrome/130" in user_agent:
            return "Chrome Canary"
        else:
            return "Chrome"
    elif "Safari" in user_agent and "Chrome" not in user_agent:
        if "Ddg" in user_agent:
            return "DuckDuckGo"
        else:
            return "Safari"
    elif "Brave" in user_agent:
        return "Brave"
    else:
        return "Other"

# 解析目標網址的OG資料
def parse_og_data(url):
    try:
        response = requests.get(url)
        
        # 尝试根据响应头推断编码，如果失败则使用 apparent_encoding
        response.encoding = response.apparent_encoding
        
        # 解析 HTML 内容
        soup = BeautifulSoup(response.text, 'html.parser')
        
        og_title = soup.find('meta', property='og:title')
        og_description = soup.find('meta', property='og:description')
        og_image = soup.find('meta', property='og:image')

        return {
            "title": og_title["content"] if og_title else None,
            "description": og_description["content"] if og_description else None,
            "image": og_image["content"] if og_image else None,
        }
    except Exception as e:
        print(f"Error parsing OG data: {e}")
        return {"title": None, "description": None, "image": None}

# 管理後台
@app.get("/admin/dashboard")
async def admin_dashboard(request: Request, db: Session = Depends(get_db)):
    user_id = request.session.get("user_id")
    
    if not user_id:
        return RedirectResponse(url="/login", status_code=303)
    
    user = db.query(User).filter(User.id == user_id).first()
    
    if not user or not user.is_admin:
        raise HTTPException(status_code=403, detail="無權訪問")

    return templates.TemplateResponse("admin_dashboard.html", {"request": request})

# 取得全部的用戶
@app.get("/api/admin/users", response_model=List[UserResponse])
def get_all_users(db: Session = Depends(get_db), page: int = 1, page_size: int = 10):
    offset = (page - 1) * page_size
    users = db.query(User).offset(offset).limit(page_size).all()
    return users

# 取得全部的短網址
@app.get("/api/admin/urls", response_model=List[ShortUrlResponse])
def get_all_urls(db: Session = Depends(get_db), page: int = 1, page_size: int = 10):
    offset = (page - 1) * page_size
    urls = db.query(URL).order_by(desc(URL.created_at)).offset(offset).limit(page_size).all()

    print("Query returned URLs:", [url.created_at for url in urls])  # 临时使用 print

    # 构建返回数据，包括聚合点击次数
    response_data = []
    for url in urls:
        click_count = db.query(func.sum(Click.click_count)).filter(Click.url_id == url.id).scalar() or 0
        response_data.append({
            "id": url.id,
            "short_code": url.short_code,
            "original_url": url.original_url,
            "click_count": click_count
        })

    return response_data

# 指定用戶的查詢
@app.post("/api/admin/users", response_model=List[UserResponse])
def search_users(request: UserQueryRequest, db: Session = Depends(get_db)):
    query_string = request.query_string

    # 使用 ilike 进行模糊查询
    users = db.query(User).filter(
        (User.email.ilike(f"%{query_string}%")) | (User.name.ilike(f"%{query_string}%"))
    ).all()

    if not users:
        raise HTTPException(status_code=404, detail="No users found")

    return users

# 指定短網址的查詢
@app.post("/api/admin/urls", response_model=List[ShortUrlResponse])
def search_short_urls(request: UrlQueryRequest, db: Session = Depends(get_db)):
    # 获取传入的查询字符串
    query = request.query_string

    # 执行模糊查询，使用 original_url 和 short_code 进行模糊匹配
    short_urls = db.query(URL).filter(
        or_(
            URL.original_url.ilike(f"%{query}%"),
            URL.short_code.ilike(f"%{query}%")
        )
    ).all()

    # 检查是否查询到了任何结果
    if not short_urls:
        raise HTTPException(status_code=404, detail="No matching short URLs found")

    # 聚合点击次数
    response_data = []
    for url in short_urls:
        click_count = db.query(func.sum(Click.click_count)).filter(Click.url_id == url.id).scalar() or 0
        response_data.append({
            "id": url.id,
            "short_code": url.short_code,
            "original_url": url.original_url,
            "click_count": click_count
        })

    return response_data

# 首頁用function start
@app.get("/api/top_clicks_last_week")
def get_top_clicks_last_week(limit: int = 9, db: Session = Depends(get_db)):
    # 从 Redis 检查是否已有缓存
    cached_data = get_top_clicks_last_week_cache()
    
    if cached_data:
        # 如果有缓存则直接返回
        return cached_data

    # 如果 Redis 中没有缓存，查询数据库
    one_week_ago = datetime.utcnow() - timedelta(days=7)
    
    results = (
        db.query(URL, OGData, func.count(Click.id).label('click_count'))
        .join(Click, URL.id == Click.url_id)
        .outerjoin(OGData, OGData.url_id == URL.id)
        .filter(Click.clicked_at >= one_week_ago)
        .group_by(URL.id, OGData.id, OGData.url_id, OGData.title, OGData.description, OGData.image)  # 将所有 OGData 字段加入 GROUP BY
        .order_by(func.count(Click.id).desc())  # 按點擊次數排序
        .limit(limit)
        .all()
    )
    
    top_urls = []
    
    for url, og_data, click_count in results:
        # 獲取初始的 title 和 og_image
        title = og_data.title if og_data and og_data.title else "No Title"
        image = og_data.image if og_data and og_data.image else "default_image.png"
        
        # 如果 title 為 "No Title" 或 image 為 "default_image.png"，即時抓取 OG Data
        if title == "No Title" or image == "default_image.png":
            online_og_data = fetch_og_data(url.original_url)  # 拉取線上的 OG Data
            title = online_og_data.get("title", title)  # 如果抓取成功就更新 title
            image = online_og_data.get("image", image)  # 如果抓取成功就更新 image
        
        top_urls.append({
            "short_code": url.short_code,
            "title": title,
            "og_image": image,
            "click_count": click_count
        })
    
    # 将结果写入 Redis 缓存，并设置 24 小时过期时间
    set_top_clicks_last_week_cache(top_urls)
    
    return top_urls


def fetch_og_data(url: str):
    try:
        # 發送請求並抓取內容
        response = requests.get(url, timeout=10)  # 加入 timeout 防止請求長時間卡住
        response.raise_for_status()  # 如果發生HTTP錯誤，會拋出異常
        soup = BeautifulSoup(response.content, "html.parser")
        
        # 提取標題
        title = soup.title.string if soup.title else "No Title"
        
        # 提取 og:image
        og_image = soup.find("meta", property="og:image")
        og_image_url = og_image["content"] if og_image else None

        return {
            "title": title,
            "image": og_image_url
        }

    except SSLError:
        # 捕捉 SSL 錯誤，並選擇忽略（pass）
        print(f"SSL error occurred while fetching OG data from {url}")
        pass  # 可以記錄日志或選擇忽略

    except RequestException as e:
        # 捕捉其他請求相關錯誤
        print(f"Error occurred while fetching OG data from {url}: {e}")
        pass  # 可以記錄日志或選擇忽略

    # 返回默認值以確保函數有返回
    return {
        "title": "No Title",
        "image": "default_image.png"
    }
# 首頁用function end

# 存取短網址
@app.get("/{short_code}")
async def redirect_with_delay(short_code: str, request: Request, db: Session = Depends(get_db)):
    # 從 Redis 快取獲取資料
    cached_url_data = get_cached_url(short_code)
    if cached_url_data:
        original_url = cached_url_data.get("original_url")
        url_id = cached_url_data.get("id")
        og_title = cached_url_data.get("og_title")
        og_description = cached_url_data.get("og_description")
        og_image = cached_url_data.get("og_image")
        direct_redirect = cached_url_data.get("direct_redirect", False)  # 默認為 False
    else:
        # 快取未命中，從資料庫查詢
        result = get_url_by_short_code(db, short_code)
        if not result:
            raise HTTPException(status_code=404, detail="Short URL not found")
        
        original_url = result.original_url
        url_id = result.id
        og_data = result.og_data
        direct_redirect = result.direct_redirect  # 從資料庫獲取 direct_redirect 標誌

        # 檢查 OG 資料
        if not og_data or not og_data.title:
            og_data_parsed = parse_og_data(original_url)
            og_title = og_data_parsed["title"]
            og_description = og_data_parsed["description"]
            og_image = og_data_parsed["image"]
        else:
            og_title = og_data.title
            og_description = og_data.description
            og_image = og_data.image

        # 將資料寫入 Redis 快取，包括 direct_redirect
        set_cached_url(short_code, original_url, url_id, og_title, og_description, og_image, direct_redirect)

    # 檢查是否設置了直接跳轉
    if direct_redirect:
        # 記錄點擊資料
        user_agent = request.headers.get('user-agent', 'unknown')
        ip_address = request.client.host
        record_click(db, url_id, user_agent, ip_address)
        return RedirectResponse(original_url)

    # 記錄點擊資料
    user_agent = request.headers.get('user-agent', 'unknown')
    ip_address = request.client.host
    record_click(db, url_id, user_agent, ip_address)

    # 渲染模板並傳递目標網址與 OG 資料
    return templates.TemplateResponse("jump_page.html", {
        "request": request,
        "original_url": original_url,
        "og_title": og_title,
        "og_description": og_description,
        "og_image": og_image,
        "DOMAIN": DOMAIN
    })