FROM fedora:36

# 替换为国内镜像源
RUN sed -i 's|metalink=|#metalink=|g' /etc/yum.repos.d/fedora*.repo && \
    sed -i 's|#baseurl=http://download.example/pub/fedora/linux|baseurl=https://mirrors.aliyun.com/fedora|g' /etc/yum.repos.d/fedora*.repo

# 安装基本工具和依赖
RUN dnf -y update && dnf -y install \
    curl \
    wget \
    git \
    gcc \
    gcc-c++ \
    make \
    openssl-devel \
    libcurl-devel \
    libxml2-devel \
    libpng-devel \
    libjpeg-devel \
    libzip-devel \
    sudo \
    && dnf clean all

# 创建工作目录
WORKDIR /app

# 添加一个非root用户
RUN useradd -m -s /bin/bash testuser && \
    echo "testuser ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/testuser

# 切换到非root用户
USER testuser

CMD ["/bin/bash"]
