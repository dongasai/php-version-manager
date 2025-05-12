#!/bin/bash
# PHP扩展管理脚本
# 用于安装、启用、禁用和卸载PHP扩展

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
    echo -e "${BLUE}PHP扩展管理脚本${NC}"
    echo "用法: $0 [命令] [参数]"
    echo ""
    echo "命令:"
    echo "  install <扩展名> [版本]    安装指定的PHP扩展"
    echo "  install-common [版本]      安装常用PHP扩展"
    echo "  enable <扩展名> [版本]     启用指定的PHP扩展"
    echo "  disable <扩展名> [版本]    禁用指定的PHP扩展"
    echo "  remove <扩展名> [版本]     卸载指定的PHP扩展"
    echo "  list [版本]                列出已安装的PHP扩展"
    echo "  help                       显示帮助信息"
    echo ""
    echo "参数:"
    echo "  扩展名                     PHP扩展名称，如 redis, xdebug 等"
    echo "  版本                       PHP版本，如 7.4.33，默认为当前使用的版本"
    echo ""
    echo "示例:"
    echo "  $0 install redis           安装Redis扩展"
    echo "  $0 install redis 7.4.33    为PHP 7.4.33安装Redis扩展"
    echo "  $0 install-common          安装常用PHP扩展"
    echo "  $0 enable xdebug           启用Xdebug扩展"
    echo "  $0 disable xdebug          禁用Xdebug扩展"
    echo "  $0 remove redis            卸载Redis扩展"
    echo "  $0 list                    列出已安装的PHP扩展"
}

# 获取当前PHP版本
get_current_php_version() {
    local version=$(php -v | grep -oE '^PHP [0-9]+\.[0-9]+\.[0-9]+' | cut -d' ' -f2)
    echo $version
}

# 安装指定的PHP扩展
install_extension() {
    local extension=$1
    local version=$2
    
    if [ -z "$extension" ]; then
        echo -e "${RED}错误: 请指定要安装的PHP扩展${NC}"
        exit 1
    fi
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 安装扩展${NC}"
    fi
    
    echo -e "${YELLOW}正在为PHP $version 安装扩展 $extension...${NC}"
    
    pvm ext install $extension $version
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}扩展 $extension 安装成功${NC}"
    else
        echo -e "${RED}扩展 $extension 安装失败${NC}"
        exit 1
    fi
}

# 安装常用PHP扩展
install_common_extensions() {
    local version=$1
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 安装常用扩展${NC}"
    fi
    
    echo -e "${YELLOW}正在为PHP $version 安装常用扩展...${NC}"
    
    # 常用扩展列表
    local extensions=("redis" "xdebug" "mongodb" "imagick" "opcache" "zip" "curl" "json" "mbstring" "xml")
    
    for ext in "${extensions[@]}"; do
        echo -e "${YELLOW}正在安装扩展 $ext...${NC}"
        pvm ext install $ext $version
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}扩展 $ext 安装成功${NC}"
        else
            echo -e "${RED}扩展 $ext 安装失败，继续安装其他扩展${NC}"
        fi
    done
    
    echo -e "${GREEN}常用扩展安装完成${NC}"
}

# 启用指定的PHP扩展
enable_extension() {
    local extension=$1
    local version=$2
    
    if [ -z "$extension" ]; then
        echo -e "${RED}错误: 请指定要启用的PHP扩展${NC}"
        exit 1
    fi
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 启用扩展${NC}"
    fi
    
    echo -e "${YELLOW}正在为PHP $version 启用扩展 $extension...${NC}"
    
    pvm ext enable $extension $version
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}扩展 $extension 启用成功${NC}"
    else
        echo -e "${RED}扩展 $extension 启用失败${NC}"
        exit 1
    fi
}

# 禁用指定的PHP扩展
disable_extension() {
    local extension=$1
    local version=$2
    
    if [ -z "$extension" ]; then
        echo -e "${RED}错误: 请指定要禁用的PHP扩展${NC}"
        exit 1
    fi
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 禁用扩展${NC}"
    fi
    
    echo -e "${YELLOW}正在为PHP $version 禁用扩展 $extension...${NC}"
    
    pvm ext disable $extension $version
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}扩展 $extension 禁用成功${NC}"
    else
        echo -e "${RED}扩展 $extension 禁用失败${NC}"
        exit 1
    fi
}

# 卸载指定的PHP扩展
remove_extension() {
    local extension=$1
    local version=$2
    
    if [ -z "$extension" ]; then
        echo -e "${RED}错误: 请指定要卸载的PHP扩展${NC}"
        exit 1
    fi
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 卸载扩展${NC}"
    fi
    
    echo -e "${YELLOW}正在为PHP $version 卸载扩展 $extension...${NC}"
    
    pvm ext remove $extension $version
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}扩展 $extension 卸载成功${NC}"
    else
        echo -e "${RED}扩展 $extension 卸载失败${NC}"
        exit 1
    fi
}

# 列出已安装的PHP扩展
list_extensions() {
    local version=$1
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将列出当前版本 $version 的扩展${NC}"
    fi
    
    echo -e "${YELLOW}PHP $version 已安装的扩展:${NC}"
    pvm ext list $version
}

# 主函数
main() {
    # 检查PVM是否已安装
    check_pvm
    
    # 解析命令行参数
    local command=$1
    local param=$2
    local version=$3
    
    case $command in
        install)
            install_extension $param $version
            ;;
        install-common)
            install_common_extensions $param
            ;;
        enable)
            enable_extension $param $version
            ;;
        disable)
            disable_extension $param $version
            ;;
        remove)
            remove_extension $param $version
            ;;
        list)
            list_extensions $param
            ;;
        help|*)
            show_help
            ;;
    esac
}

# 执行主函数
main "$@"
