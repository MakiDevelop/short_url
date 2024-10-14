import redis
import json
import os

# 初始化 Redis 连接
redis_host = os.getenv("REDIS_HOST", "localhost")
redis_port = os.getenv("REDIS_PORT", 6379)
r = redis.StrictRedis(host=redis_host, port=redis_port, db=0, decode_responses=True)

# 从 Redis 获取缓存
def get_cached_url(short_code):
    cached_data = r.get(short_code)
    if cached_data:
        return json.loads(cached_data)
    return None

# 将数据写入 Redis 缓存
def set_cached_url(short_code, original_url, url_id, og_title, og_description, og_image, direct_redirect):
    cached_data = {
        "original_url": original_url,
        "id": url_id,
        "og_title": og_title,
        "og_description": og_description,
        "og_image": og_image,
        "direct_redirect": direct_redirect
    }
    r.set(short_code, json.dumps(cached_data), ex=86400)  # 設置 1 天的過期時間

# 获取上周热门短网址的缓存
def get_top_clicks_last_week_cache():
    cached_data = r.get("top_clicks_last_week")
    if cached_data:
        return json.loads(cached_data)
    return None

# 将上周热门短网址结果写入 Redis，并设定 24 小时过期时间
def set_top_clicks_last_week_cache(top_urls):
    r.set("top_clicks_last_week", json.dumps(top_urls), ex=86400)  # 設置 1 天的過期時間