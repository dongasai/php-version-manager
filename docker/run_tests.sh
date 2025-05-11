#!/bin/bash

# 测试脚本，用于在Docker环境中运行所有测试

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

# 运行版本检测测试
echo -e "${BLUE}===== 运行版本检测测试 =====${NC}"
php tests/test_version_detector.php
echo ""

# 运行版本安装测试
echo -e "${BLUE}===== 运行版本安装测试 =====${NC}"
php tests/test_version_installer.php
echo ""

# 运行版本切换测试
echo -e "${BLUE}===== 运行版本切换测试 =====${NC}"
php tests/test_version_switcher.php
echo ""

# 运行版本删除测试
echo -e "${BLUE}===== 运行版本删除测试 =====${NC}"
php tests/test_version_remover.php
echo ""

# 运行综合测试
echo -e "${BLUE}===== 运行综合测试 =====${NC}"
php tests/test_all.php
echo ""

# 测试完成
echo -e "${GREEN}===== 所有测试完成 =====${NC}"
