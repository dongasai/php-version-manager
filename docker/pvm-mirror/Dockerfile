FROM ubuntu:22.04

# 设置非交互式安装
ENV DEBIAN_FRONTEND=noninteractive

# 使用阿里云镜像源
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基本工具和PHP
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    unzip \
    build-essential \
    libssl-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libonig-dev \
    libsqlite3-dev \
    libreadline-dev \
    php8.1 \
    php8.1-cli \
    php8.1-common \
    php8.1-curl \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-zip \
    php8.1-sqlite3 \
    php8.1-readline \
    sudo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 创建pvm-mirror用户
RUN useradd -m -s /bin/bash pvm-mirror && \
    echo "pvm-mirror ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/pvm-mirror

# 设置工作目录
WORKDIR /app

# 复制项目文件
COPY . /app/

# 设置权限
RUN chown -R pvm-mirror:pvm-mirror /app && \
    chmod +x /app/bin/pvm-mirror

# 创建数据和日志目录
RUN mkdir -p /app/data /app/logs && \
    chown -R pvm-mirror:pvm-mirror /app/data /app/logs

# 切换到pvm-mirror用户
USER pvm-mirror

# 暴露端口
EXPOSE 34403

# 健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:34403/ || exit 1

# 启动命令
CMD ["/app/bin/pvm-mirror", "server", "start", "--foreground"]
