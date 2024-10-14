from sqlalchemy.orm import Session
from models import URL, Clicks
from sqlalchemy import func
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import smtplib
from apscheduler.schedulers.background import BackgroundScheduler
import atexit

# 獲取Top 100點擊數的短網址
def get_top_100_urls(db: Session):
    return db.query(
        URL.short_code, 
        URL.original_url, 
        func.count(Clicks.id).label('click_count')
    ).join(Clicks, Clicks.url_id == URL.id, isouter=True)\
    .group_by(URL.id)\
    .order_by(func.count(Clicks.id).desc())\
    .limit(100).all()

# 生成HTML報告
def generate_report(top_urls):
    html_content = """
    <html>
    <body>
        <h2>Top 100 Clicked URLs Report</h2>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Short URL</th>
                <th>Original URL</th>
                <th>Click Count</th>
            </tr>
    """
    
    for url in top_urls:
        html_content += f"<tr><td>{url.short_code}</td><td>{url.original_url}</td><td>{url.click_count}</td></tr>"
    
    html_content += """
        </table>
    </body>
    </html>
    """
    return html_content

# 發送報告的Email
def send_email_report(html_content, recipient_email):
    msg = MIMEMultipart('alternative')
    msg['Subject'] = "Weekly Top 100 Clicked URLs Report"
    msg['From'] = "your-email@example.com"
    msg['To'] = recipient_email

    part = MIMEText(html_content, 'html')
    msg.attach(part)

    # 發送郵件
    with smtplib.SMTP('smtp.example.com', 587) as server:
        server.starttls()
        server.login("your-email@example.com", "your-password")
        server.sendmail(msg['From'], [msg['To']], msg.as_string())

# 定期生成並發送報告
def send_weekly_report(db_session):
    # 獲取top 100的短網址並生成報告
    top_urls = get_top_100_urls(db_session)
    html_report = generate_report(top_urls)
    send_email_report(html_report, 'admin@example.com')

# 啟動定期任務的Scheduler
def start_scheduler(db_session):
    scheduler = BackgroundScheduler()
    scheduler.add_job(send_weekly_report, 'cron', day_of_week='mon', hour=10, args=[db_session])
    
    scheduler.start()
    
    # 確保應用程式關閉時關閉定時器
    atexit.register(lambda: scheduler.shutdown())