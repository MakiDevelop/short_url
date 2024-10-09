from pydantic import BaseModel, HttpUrl

class URLRequest(BaseModel):
    url: HttpUrl


class UserQueryRequest(BaseModel):
    query_string: str

class UrlQueryRequest(BaseModel):
    query_string: str

class AdminRequest(BaseModel):
    user_id: int

class UserResponse(BaseModel):
    id: int
    email: str
    is_active: bool
    is_admin: bool

    class Config:
        orm_mode = True

class ShortUrlResponse(BaseModel):
    id: int
    short_code: str
    original_url: str
    click_count: int  # 确保这个字段存在

    class Config:
        orm_mode = True