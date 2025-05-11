FROM php:7.1-cli

# 使用阿里云镜像
RUN sed -i 's/deb.debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基本工具和依赖
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    zip \
    unzip \
    libzip-dev \
    libxml2-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    sudo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安装PHP扩展
RUN docker-php-ext-install \
    zip \
    xml \
    dom \
    mbstring \
    curl \
    pdo \
    pdo_mysql

# 安装Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    # 配置Composer使用阿里云镜像
    composer config -g repo.packagist composer https://packagist.pages.dev/

# 创建非root用户
RUN useradd -m -s /bin/bash developer && \
    echo "developer ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/developer

# 创建工作目录
WORKDIR /app

# 设置权限
RUN chown -R developer:developer /app

# 切换到非root用户
USER developer

# 设置环境变量
ENV PATH="/app/vendor/bin:${PATH}"

# 默认命令
CMD ["/bin/bash"]
