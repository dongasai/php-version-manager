#!/bin/bash

# 更新兼容性测试报告的脚本

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 获取测试环境信息
OS_NAME=$(cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2 | tr -d '"')
OS_VERSION=$(cat /etc/os-release | grep VERSION_ID | cut -d= -f2 | tr -d '"')
ARCH=$(uname -m)

# 获取测试结果
if [ -f "/tmp/test_results.txt" ]; then
    TEST_RESULTS=$(cat /tmp/test_results.txt)
    if [ "$TEST_RESULTS" == "PASSED" ]; then
        RESULT="通过"
    else
        RESULT="失败"
    fi
else
    RESULT="未知"
fi

# 更新报告
echo -e "${BLUE}更新兼容性测试报告...${NC}"

# 根据操作系统和架构更新报告
case "$OS_NAME" in
    *Ubuntu*)
        if [ "$ARCH" == "x86_64" ]; then
            # 更新Ubuntu x86_64的测试结果
            sed -i "s/Ubuntu | 22.04 | x86_64 | 待测试 |/Ubuntu | 22.04 | x86_64 | $RESULT |/g" docs/COMPATIBILITY_REPORT.md
            sed -i "s/- 版本检测功能: 待测试/- 版本检测功能: $RESULT/g" docs/COMPATIBILITY_REPORT.md
            sed -i "s/- 版本安装功能: 待测试/- 版本安装功能: $RESULT/g" docs/COMPATIBILITY_REPORT.md
            sed -i "s/- 版本切换功能: 待测试/- 版本切换功能: $RESULT/g" docs/COMPATIBILITY_REPORT.md
            sed -i "s/- 版本删除功能: 待测试/- 版本删除功能: $RESULT/g" docs/COMPATIBILITY_REPORT.md
            sed -i "s/- CLI命令行工具: 待测试/- CLI命令行工具: $RESULT/g" docs/COMPATIBILITY_REPORT.md
        elif [ "$ARCH" == "aarch64" ]; then
            # 更新Ubuntu ARM64的测试结果
            sed -i "s/Ubuntu | 22.04 | ARM64 | 待测试 |/Ubuntu | 22.04 | ARM64 | $RESULT |/g" docs/COMPATIBILITY_REPORT.md
            sed -i "s/### Ubuntu 22.04 (ARM64)\n\n- 版本检测功能: 待测试\n- 版本安装功能: 待测试\n- 版本切换功能: 待测试\n- 版本删除功能: 待测试\n- CLI命令行工具: 待测试/### Ubuntu 22.04 (ARM64)\n\n- 版本检测功能: $RESULT\n- 版本安装功能: $RESULT\n- 版本切换功能: $RESULT\n- 版本删除功能: $RESULT\n- CLI命令行工具: $RESULT/g" docs/COMPATIBILITY_REPORT.md
        fi
        ;;
    *Debian*)
        # 更新Debian的测试结果
        sed -i "s/Debian | 11 | x86_64 | 待测试 |/Debian | 11 | x86_64 | $RESULT |/g" docs/COMPATIBILITY_REPORT.md
        sed -i "s/### Debian 11 (x86_64)\n\n- 版本检测功能: 待测试\n- 版本安装功能: 待测试\n- 版本切换功能: 待测试\n- 版本删除功能: 待测试\n- CLI命令行工具: 待测试/### Debian 11 (x86_64)\n\n- 版本检测功能: $RESULT\n- 版本安装功能: $RESULT\n- 版本切换功能: $RESULT\n- 版本删除功能: $RESULT\n- CLI命令行工具: $RESULT/g" docs/COMPATIBILITY_REPORT.md
        ;;
    *CentOS*)
        # 更新CentOS的测试结果
        sed -i "s/CentOS | 7 | x86_64 | 待测试 |/CentOS | 7 | x86_64 | $RESULT |/g" docs/COMPATIBILITY_REPORT.md
        sed -i "s/### CentOS 7 (x86_64)\n\n- 版本检测功能: 待测试\n- 版本安装功能: 待测试\n- 版本切换功能: 待测试\n- 版本删除功能: 待测试\n- CLI命令行工具: 待测试/### CentOS 7 (x86_64)\n\n- 版本检测功能: $RESULT\n- 版本安装功能: $RESULT\n- 版本切换功能: $RESULT\n- 版本删除功能: $RESULT\n- CLI命令行工具: $RESULT/g" docs/COMPATIBILITY_REPORT.md
        ;;
    *Fedora*)
        # 更新Fedora的测试结果
        sed -i "s/Fedora | 36 | x86_64 | 待测试 |/Fedora | 36 | x86_64 | $RESULT |/g" docs/COMPATIBILITY_REPORT.md
        sed -i "s/### Fedora 36 (x86_64)\n\n- 版本检测功能: 待测试\n- 版本安装功能: 待测试\n- 版本切换功能: 待测试\n- 版本删除功能: 待测试\n- CLI命令行工具: 待测试/### Fedora 36 (x86_64)\n\n- 版本检测功能: $RESULT\n- 版本安装功能: $RESULT\n- 版本切换功能: $RESULT\n- 版本删除功能: $RESULT\n- CLI命令行工具: $RESULT/g" docs/COMPATIBILITY_REPORT.md
        ;;
    *Alpine*)
        # 更新Alpine的测试结果
        sed -i "s/Alpine | 3.16 | x86_64 | 待测试 |/Alpine | 3.16 | x86_64 | $RESULT |/g" docs/COMPATIBILITY_REPORT.md
        sed -i "s/### Alpine 3.16 (x86_64)\n\n- 版本检测功能: 待测试\n- 版本安装功能: 待测试\n- 版本切换功能: 待测试\n- 版本删除功能: 待测试\n- CLI命令行工具: 待测试/### Alpine 3.16 (x86_64)\n\n- 版本检测功能: $RESULT\n- 版本安装功能: $RESULT\n- 版本切换功能: $RESULT\n- 版本删除功能: $RESULT\n- CLI命令行工具: $RESULT/g" docs/COMPATIBILITY_REPORT.md
        ;;
    *)
        echo -e "${RED}未知操作系统: $OS_NAME${NC}"
        ;;
esac

echo -e "${GREEN}兼容性测试报告已更新${NC}"
