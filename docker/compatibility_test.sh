#!/bin/bash

# 跨平台兼容性测试脚本
# 用于在不同的Linux发行版和架构上测试PVM的功能

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 测试结果
TESTS_TOTAL=0
TESTS_PASSED=0
TESTS_FAILED=0

# 测试环境信息
echo -e "${BLUE}===== 测试环境信息 =====${NC}"
echo -e "${BLUE}操作系统:${NC} $(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '"')"
echo -e "${BLUE}内核版本:${NC} $(uname -r)"
echo -e "${BLUE}架构:${NC} $(uname -m)"
echo -e "${BLUE}PHP版本:${NC} $(php -v | head -n 1)"
echo -e "${BLUE}当前用户:${NC} $(whoami)"
echo -e "${BLUE}当前目录:${NC} $(pwd)"
echo ""

# 安装依赖
echo -e "${BLUE}===== 安装依赖 =====${NC}"
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}正在安装依赖...${NC}"
    composer install
    if [ $? -ne 0 ]; then
        echo -e "${RED}依赖安装失败${NC}"
        exit 1
    fi
    echo -e "${GREEN}依赖安装成功${NC}"
else
    echo -e "${GREEN}依赖已安装${NC}"
fi
echo ""

# 测试函数
run_test() {
    local test_name=$1
    local test_command=$2

    echo -e "${BLUE}测试: ${test_name}${NC}"
    echo -e "${YELLOW}执行: ${test_command}${NC}"

    TESTS_TOTAL=$((TESTS_TOTAL + 1))

    # 执行测试命令
    eval $test_command
    local result=$?

    if [ $result -eq 0 ]; then
        echo -e "${GREEN}测试通过${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}测试失败${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi

    echo ""
}

# 测试版本检测功能
echo -e "${BLUE}===== 测试版本检测功能 =====${NC}"
run_test "获取当前PHP版本" "php tests/test_version_detector.php"

# 测试版本安装功能
echo -e "${BLUE}===== 测试版本安装功能 =====${NC}"
# 创建必要的目录
mkdir -p ~/.pvm/versions ~/.pvm/shims

# 测试安装PHP 7.4.33版本
run_test "安装PHP 7.4.33" "php -f tests/test_version_installer.php"

# 测试版本切换功能
echo -e "${BLUE}===== 测试版本切换功能 =====${NC}"
run_test "切换PHP版本" "php -f tests/test_version_switcher.php"

# 测试版本删除功能
echo -e "${BLUE}===== 测试版本删除功能 =====${NC}"
run_test "删除PHP版本" "php -f tests/test_version_remover.php"

# 测试CLI命令行工具
echo -e "${BLUE}===== 测试CLI命令行工具 =====${NC}"
run_test "帮助命令" "php bin/pvm help"
run_test "版本命令" "php bin/pvm version"
run_test "列表命令" "php bin/pvm list"

# 测试结果
echo -e "${BLUE}===== 测试结果 =====${NC}"
echo -e "总测试数: ${TESTS_TOTAL}"
echo -e "通过: ${GREEN}${TESTS_PASSED}${NC}"
echo -e "失败: ${RED}${TESTS_FAILED}${NC}"

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}所有测试通过!${NC}"
    # 写入测试结果
    echo "PASSED" > /tmp/test_results.txt
    # 更新兼容性测试报告
    if [ -f "docker/update_report.sh" ]; then
        chmod +x docker/update_report.sh
        ./docker/update_report.sh
    fi
    exit 0
else
    echo -e "${RED}有测试失败!${NC}"
    # 写入测试结果
    echo "FAILED" > /tmp/test_results.txt
    # 更新兼容性测试报告
    if [ -f "docker/update_report.sh" ]; then
        chmod +x docker/update_report.sh
        ./docker/update_report.sh
    fi
    exit 1
fi
