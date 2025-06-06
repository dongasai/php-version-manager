FROM quay.io/centos/centos:stream8

# 替换为阿里云镜像源
RUN cd /etc/yum.repos.d/ && \
    sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=https://mirrors.aliyun.com|g' /etc/yum.repos.d/CentOS-*

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

# 尝试安装 bats 包，如果失败则使用 Git 安装
RUN dnf -y install epel-release && \
    dnf -y install bats || \
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
