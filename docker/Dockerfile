FROM ubuntu:24.04

# 设置非交互式安装
ENV DEBIAN_FRONTEND=noninteractive

# 使用阿里云镜像
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基本工具和依赖(只安装基本依赖,其他的依赖需要通过pvm来处理)
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    sudo


# 注意：我们不预先安装PHP,只安装基本依赖curl /wget 来下载 install.sh ，其他的而是留给PVM工具来安装和管理

# 创建非root用户
RUN useradd -m -s /bin/bash developer && \
    mkdir -p /etc/sudoers.d && \
    echo "developer ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/developer

# 创建工作目录
WORKDIR /app

# 设置权限
RUN chown -R developer:developer /app

# 切换到非root用户
USER developer


# 默认命令
CMD ["/bin/bash"]
