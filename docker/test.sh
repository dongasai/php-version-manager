#!/bin/bash

# 测试脚本，用于在不同的容器中测试PVM

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 测试环境
echo -e "${BLUE}===== 测试环境信息 =====${NC}"
echo -e "${BLUE}操作系统:${NC} $(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '"')"
echo -e "${BLUE}内核版本:${NC} $(uname -r)"
echo -e "${BLUE}架构:${NC} $(uname -m)"
echo -e "${BLUE}当前用户:${NC} $(whoami)"
echo -e "${BLUE}当前目录:${NC} $(pwd)"
echo ""

# 测试安装脚本
echo -e "${BLUE}===== 测试安装脚本 =====${NC}"
chmod +x install.sh
./install.sh
echo ""

# 测试版本检测
echo -e "${BLUE}===== 测试版本检测 =====${NC}"
php tests/test_version_detector.php
echo ""

# 测试版本安装
echo -e "${BLUE}===== 测试版本安装 =====${NC}"
php tests/test_version_installer.php
echo ""

# 测试完成
echo -e "${GREEN}===== 测试完成 =====${NC}"
