FROM python:3.11-slim

# 安装编译工具和必要的依赖
RUN apt-get update && apt-get install -y build-essential libssl-dev libffi-dev default-libmysqlclient-dev

# 设置工作目录
WORKDIR /app

# 复制 requirements.txt
COPY ./requirements.txt /app/requirements.txt

RUN pip install --upgrade pip 
# 安装 Python 依赖
RUN pip install --no-cache-dir --upgrade -r /app/requirements.txt

# 复制应用程序代码
COPY . /app

# 启动 FastAPI 应用
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]