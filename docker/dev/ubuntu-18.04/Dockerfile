FROM ubuntu:18.04

# 设置非交互式安装
ENV DEBIAN_FRONTEND=noninteractive

# 使用阿里云镜像
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基本工具和依赖
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    build-essential \
    libssl-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libonig-dev \
    sudo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 尝试安装 bats 包，如果失败则使用 Git 安装
RUN apt-get update && apt-get install -y bats || \
    (cd /tmp && \
    git clone https://github.com/bats-core/bats-core.git && \
    cd bats-core && \
    ./install.sh /usr/local && \
    rm -rf /tmp/bats-core)

# 创建工作目录
WORKDIR /app

# 添加一个非root用户
RUN useradd -m -s /bin/bash testuser && \
    echo "testuser ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/testuser

# 切换到非root用户
USER testuser

CMD ["/bin/bash"]
