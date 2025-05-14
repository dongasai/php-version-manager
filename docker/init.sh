#!/bin/bash

# 初始化脚本，用于在开发容器中设置基本环境

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 显示环境信息
echo -e "${BLUE}===== PHP Version Manager 开发环境初始化 =====${NC}"
echo -e "${BLUE}操作系统:${NC} $(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '"')"
echo -e "${BLUE}内核版本:${NC} $(uname -r)"
echo -e "${BLUE}架构:${NC} $(uname -m)"
echo -e "${BLUE}当前用户:${NC} $(whoami)"
echo -e "${BLUE}当前目录:${NC} $(pwd)"
echo ""

# 安装基本依赖
echo -e "${YELLOW}正在安装基本依赖...${NC}"
apt-get update
apt-get install -y \
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
    vim \
    unzip

# 安装Composer
echo -e "${YELLOW}正在安装Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    echo -e "${GREEN}Composer安装完成${NC}"
else
    echo -e "${GREEN}Composer已安装${NC}"
fi

# 进入应用目录
cd /app

# 安装项目依赖
echo -e "${YELLOW}正在安装项目依赖...${NC}"
composer install

# 设置环境变量
echo -e "${YELLOW}正在设置环境变量...${NC}"
echo 'export PATH="/app/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc

echo -e "${GREEN}开发环境初始化完成${NC}"
echo -e "${YELLOW}您现在可以开始开发PVM项目了${NC}"
