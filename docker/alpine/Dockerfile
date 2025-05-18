FROM alpine:3.16

# 替换为阿里云镜像源
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

# 安装基本工具和依赖
RUN apk update && apk add --no-cache \
    curl \
    wget \
    git \
    gcc \
    g++ \
    make \
    openssl-dev \
    curl-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    sudo \
    bash \
    bats

# 创建工作目录
WORKDIR /app

# 添加一个非root用户
RUN adduser -D -s /bin/bash testuser && \
    echo "testuser ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/testuser

# 切换到非root用户
USER testuser

CMD ["/bin/bash"]
