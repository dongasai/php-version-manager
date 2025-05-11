#!/bin/bash

# 在开发容器中运行命令的脚本

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 显示开发环境信息
echo -e "${BLUE}===== PHP Version Manager 开发环境 =====${NC}"
echo -e "${BLUE}PHP版本:${NC} $(php -v | head -n 1)"
echo -e "${BLUE}当前目录:${NC} $(pwd)"
echo ""

# 安装依赖
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}正在安装依赖...${NC}"
    composer install
    echo ""
fi

# 如果没有提供命令，则显示帮助信息
if [ $# -eq 0 ]; then
    echo -e "${BLUE}可用命令:${NC}"
    echo "  composer          - 运行Composer命令"
    echo "  test              - 运行测试"
    echo "  test-detector     - 测试版本检测功能"
    echo "  test-installer    - 测试版本安装功能"
    echo "  test-switcher     - 测试版本切换功能"
    echo "  test-remover      - 测试版本删除功能"
    echo "  test-all          - 运行所有测试"
    echo "  help              - 显示帮助信息"
    echo ""
    exit 0
fi

# 处理命令
case "$1" in
    composer)
        shift
        composer "$@"
        ;;
    test)
        shift
        if [ $# -eq 0 ]; then
            php tests/test_all.php
        else
            php "tests/test_$1.php"
        fi
        ;;
    test-detector)
        php tests/test_version_detector.php
        ;;
    test-installer)
        php tests/test_version_installer.php
        ;;
    test-switcher)
        php tests/test_version_switcher.php
        ;;
    test-remover)
        php tests/test_version_remover.php
        ;;
    test-all)
        php tests/test_all.php
        ;;
    help)
        echo -e "${BLUE}可用命令:${NC}"
        echo "  composer          - 运行Composer命令"
        echo "  test              - 运行测试"
        echo "  test-detector     - 测试版本检测功能"
        echo "  test-installer    - 测试版本安装功能"
        echo "  test-switcher     - 测试版本切换功能"
        echo "  test-remover      - 测试版本删除功能"
        echo "  test-all          - 运行所有测试"
        echo "  help              - 显示帮助信息"
        ;;
    *)
        # 如果不是特定命令，则作为PHP脚本运行
        php "$@"
        ;;
esac
