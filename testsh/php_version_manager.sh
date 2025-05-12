#!/bin/bash
# PHP版本管理脚本
# 用于安装、切换和卸载不同版本的PHP

# 设置颜色
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 检查PVM是否已安装
check_pvm() {
    if ! command -v pvm &> /dev/null; then
        echo -e "${RED}错误: PVM未安装，请先安装PVM${NC}"
        exit 1
    fi
}

# 显示帮助信息
show_help() {
    echo -e "${BLUE}PHP版本管理脚本${NC}"
    echo "用法: $0 [命令] [参数]"
    echo ""
    echo "命令:"
    echo "  install <版本>       安装指定版本的PHP"
    echo "  install-all          安装所有支持的PHP版本"
    echo "  use <版本>           切换到指定版本的PHP"
    echo "  remove <版本>        卸载指定版本的PHP"
    echo "  list                 列出已安装的PHP版本"
    echo "  supported            列出支持的PHP版本"
    echo "  help                 显示帮助信息"
    echo ""
    echo "示例:"
    echo "  $0 install 7.4.33    安装PHP 7.4.33"
    echo "  $0 use 7.4.33        切换到PHP 7.4.33"
    echo "  $0 remove 7.4.33     卸载PHP 7.4.33"
}

# 安装指定版本的PHP
install_php() {
    local version=$1
    local options=$2
    
    if [ -z "$version" ]; then
        echo -e "${RED}错误: 请指定要安装的PHP版本${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}正在安装PHP $version...${NC}"
    
    if [ -z "$options" ]; then
        pvm install $version
    else
        pvm install $version $options
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}PHP $version 安装成功${NC}"
    else
        echo -e "${RED}PHP $version 安装失败${NC}"
        exit 1
    fi
}

# 安装所有支持的PHP版本
install_all_php() {
    echo -e "${YELLOW}正在获取支持的PHP版本列表...${NC}"
    
    # 获取支持的PHP版本列表
    local versions=$(pvm supported | grep -E '^[0-9]+\.[0-9]+\.[0-9]+' | awk '{print $1}')
    
    if [ -z "$versions" ]; then
        echo -e "${RED}错误: 无法获取支持的PHP版本列表${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}将安装以下PHP版本:${NC}"
    echo "$versions"
    echo ""
    
    # 确认安装
    read -p "是否继续安装? (y/n): " confirm
    if [ "$confirm" != "y" ]; then
        echo -e "${YELLOW}已取消安装${NC}"
        exit 0
    fi
    
    # 安装每个版本
    for version in $versions; do
        install_php $version
    done
    
    echo -e "${GREEN}所有PHP版本安装完成${NC}"
}

# 切换到指定版本的PHP
use_php() {
    local version=$1
    
    if [ -z "$version" ]; then
        echo -e "${RED}错误: 请指定要切换的PHP版本${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}正在切换到PHP $version...${NC}"
    
    pvm use $version
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}已切换到PHP $version${NC}"
    else
        echo -e "${RED}切换到PHP $version 失败${NC}"
        exit 1
    fi
}

# 卸载指定版本的PHP
remove_php() {
    local version=$1
    
    if [ -z "$version" ]; then
        echo -e "${RED}错误: 请指定要卸载的PHP版本${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}正在卸载PHP $version...${NC}"
    
    pvm remove $version
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}PHP $version 卸载成功${NC}"
    else
        echo -e "${RED}PHP $version 卸载失败${NC}"
        exit 1
    fi
}

# 列出已安装的PHP版本
list_php() {
    echo -e "${YELLOW}已安装的PHP版本:${NC}"
    pvm list
}

# 列出支持的PHP版本
supported_php() {
    echo -e "${YELLOW}支持的PHP版本:${NC}"
    pvm supported
}

# 主函数
main() {
    # 检查PVM是否已安装
    check_pvm
    
    # 解析命令行参数
    local command=$1
    local param=$2
    local options=$3
    
    case $command in
        install)
            install_php $param "$options"
            ;;
        install-all)
            install_all_php
            ;;
        use)
            use_php $param
            ;;
        remove)
            remove_php $param
            ;;
        list)
            list_php
            ;;
        supported)
            supported_php
            ;;
        help|*)
            show_help
            ;;
    esac
}

# 执行主函数
main "$@"
