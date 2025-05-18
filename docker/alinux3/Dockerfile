FROM alibaba-cloud-linux-3-registry.cn-hangzhou.cr.aliyuncs.com/alinux3/alinux3

# 安装基本工具和依赖
RUN yum -y update && yum -y install \
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
    && yum clean all

# 创建工作目录
WORKDIR /app

# 添加一个非root用户
RUN useradd -m -s /bin/bash testuser && \
    echo "testuser ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/testuser

# 切换到非root用户
USER testuser

CMD ["/bin/bash"]
