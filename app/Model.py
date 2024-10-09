from sqlalchemy import Table, Column, String, Integer, DateTime, ForeignKey, create_engine, select, Boolean
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import relationship, sessionmaker
from datetime import datetime
import os

# 創建 Base 類
Base = declarative_base()

# 設置資料庫引擎
DATABASE_URL = os.getenv("DATABASE_URL", "postgresql://user:password@localhost/dbname")
engine = create_engine(DATABASE_URL)

SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

# 使用者資料表
class User(Base):
    __tablename__ = "users"
    
    id = Column(Integer, primary_key=True, index=True)
    email = Column(String, unique=True, index=True, nullable=False)
    name = Column(String, nullable=False)
    is_active = Column(Boolean, default=True)
    is_admin = Column(Boolean, default=False)
    
    # 與 URL 的一對多關係
    urls = relationship("URL", back_populates="user")

# 中間表定義
url_tag_association = Table(
    'url_tag_association',
    Base.metadata,
    Column('url_id', Integer, ForeignKey('urls.id')),
    Column('tag_id', Integer, ForeignKey('tags.id'))
)

# 標籤資料表
class Tag(Base):
    __tablename__ = "tags"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String, nullable=False)

    # 與 URL 的多對多關係
    urls = relationship("URL", secondary=url_tag_association, back_populates="tags")

# URL 模型
class URL(Base):
    __tablename__ = 'urls'
    
    id = Column(Integer, primary_key=True, index=True)
    original_url = Column(String, nullable=False)
    short_code = Column(String, unique=True, index=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    direct_redirect = Column(Boolean, default=False)
    
    clicks = relationship("Click", back_populates="url", cascade="all, delete")
    # 使用者外鍵
    user_id = Column(Integer, ForeignKey('users.id'))
    
    # 與 User 和 Tag 的關係
    user = relationship("User", back_populates="urls")
    tags = relationship("Tag", secondary=url_tag_association, back_populates="urls")
    utm_data = relationship("UTMData", back_populates="url", cascade="all, delete")
    # 與 OGData 的一對一關係
    og_data = relationship("OGData", back_populates="url", uselist=False, cascade="all, delete")  # 一對一關係

class OGData(Base):
    __tablename__ = "og_data"
    
    id = Column(Integer, primary_key=True, index=True)
    url_id = Column(Integer, ForeignKey('urls.id'), nullable=False)  # 這裡是關鍵，必須關聯到 URL 表的主鍵
    title = Column(String, nullable=True)
    description = Column(String, nullable=True)
    image = Column(String, nullable=True)
    
    # 與 URL 的關聯
    url = relationship("URL", back_populates="og_data")

# Click 模型
class Click(Base):
    __tablename__ = 'clicks'
    
    id = Column(Integer, primary_key=True, index=True)
    url_id = Column(Integer, ForeignKey('urls.id'), nullable=False)
    user_agent = Column(String, nullable=True)
    ip_address = Column(String(45), nullable=True)
    clicked_at = Column(DateTime, default=datetime.utcnow)
    click_count = Column(Integer, default=1)

    url = relationship("URL", back_populates="clicks")

# 定义 UTM 数据表
class UTMData(Base):
    __tablename__ = 'utm_data'

    id = Column(Integer, primary_key=True, index=True)
    url_id = Column(Integer, ForeignKey('urls.id'), nullable=False)  # 关联到 URL 表
    utm_source = Column(String, nullable=True)
    utm_medium = Column(String, nullable=True)
    utm_campaign = Column(String, nullable=True)
    utm_term = Column(String, nullable=True)
    utm_content = Column(String, nullable=True)

    # 与 URL 表的关系
    url = relationship("URL", back_populates="utm_data")

# 获取数据库会话
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# 获取 URL 通过短码
def get_url_by_short_code(db: SessionLocal, short_code: str):
    query = select(URL).where(URL.short_code == short_code)
    result = db.execute(query).scalar_one_or_none()
    return result

# 记录点击信息
def record_click(db: SessionLocal, url_id: int, user_agent: str, ip_address: str):
    click_data = Click(
        url_id=url_id,
        user_agent=user_agent,
        ip_address=ip_address,
        clicked_at=datetime.utcnow(),
        click_count=1
    )
    db.add(click_data)
    db.commit()
    return click_data

# 創建所有表格
Base.metadata.create_all(bind=engine)