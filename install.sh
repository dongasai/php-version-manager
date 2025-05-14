#!/bin/bash

# PHP Version Manager (PVM) 安装脚本
# 该脚本用于安装PVM，并在需要时安装基础PHP版本

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 版本信息
PVM_VERSION="1.0.0"
DEFAULT_PHP_VERSION="7.4"

# 目录设置
PVM_DIR="$HOME/.pvm"
BIN_DIR="$PVM_DIR/bin"
VERSIONS_DIR="$PVM_DIR/versions"
SHIMS_DIR="$PVM_DIR/shims"

# 打印信息
echo -e "${BLUE}PHP Version Manager (PVM) 安装脚本${NC}"
echo -e "${BLUE}版本: ${PVM_VERSION}${NC}"
echo ""

# 检查系统类型
if [[ "$OSTYPE" != "linux-gnu"* ]]; then
    echo -e "${RED}错误: PVM 目前只支持 Linux 系统${NC}"
    exit 1
fi

# 检测包管理器
detect_package_manager() {
    if command -v apt-get &> /dev/null; then
        echo "apt"
    elif command -v yum &> /dev/null; then
        echo "yum"
    elif command -v dnf &> /dev/null; then
        echo "dnf"
    elif command -v apk &> /dev/null; then
        echo "apk"
    else
        echo "unknown"
    fi
}

# 安装依赖
install_dependencies() {
    local pkg_manager=$(detect_package_manager)
    echo -e "${BLUE}使用 ${pkg_manager} 安装依赖...${NC}"

    # 检查用户权限
    if [ "$(id -u)" = "0" ]; then
        # 如果当前用户是root，不需要sudo
        USE_SUDO=""
    else
        USE_SUDO="sudo"
    fi

    case $pkg_manager in
        apt)
            $USE_SUDO apt-get update
            $USE_SUDO apt-get install -y curl wget build-essential libssl-dev libcurl4-openssl-dev libxml2-dev
            ;;
        yum|dnf)
            $USE_SUDO $pkg_manager update -y
            $USE_SUDO $pkg_manager install -y curl wget gcc make openssl-devel libcurl-devel libxml2-devel
            ;;
        apk)
            $USE_SUDO apk update
            $USE_SUDO apk add curl wget gcc make openssl-dev curl-dev libxml2-dev
            ;;
        *)
            echo -e "${RED}错误: 不支持的包管理器${NC}"
            echo -e "${YELLOW}请手动安装以下依赖: curl, wget, gcc, make, openssl-dev, libcurl-dev, libxml2-dev${NC}"
            ;;
    esac
}

# 检查PHP是否已安装
check_php_installed() {
    if command -v php &> /dev/null; then
        local php_version=$(php -r "echo PHP_VERSION;")
        echo -e "${GREEN}检测到PHP ${php_version}${NC}"
        return 0
    else
        echo -e "${YELLOW}未检测到PHP${NC}"
        return 1
    fi
}

# 安装基础PHP版本
install_base_php() {
    local pkg_manager=$(detect_package_manager)
    echo -e "${BLUE}安装PHP ${DEFAULT_PHP_VERSION}...${NC}"

    # 检查用户权限
    if [ "$(id -u)" = "0" ]; then
        # 如果当前用户是root，不需要sudo
        USE_SUDO=""
    else
        USE_SUDO="sudo"
    fi

    case $pkg_manager in
        apt)
            $USE_SUDO apt-get update
            $USE_SUDO apt-get install -y php php-cli php-common php-curl php-xml php-mbstring
            ;;
        yum)
            $USE_SUDO yum install -y php php-cli php-common php-curl php-xml php-mbstring
            ;;
        dnf)
            $USE_SUDO dnf install -y php php-cli php-common php-curl php-xml php-mbstring
            ;;
        apk)
            $USE_SUDO apk update
            $USE_SUDO apk add php php-cli php-common php-curl php-xml php-mbstring
            ;;
        *)
            echo -e "${RED}错误: 不支持的包管理器，无法自动安装PHP${NC}"
            echo -e "${YELLOW}请手动安装PHP ${DEFAULT_PHP_VERSION}后再运行此脚本${NC}"
            exit 1
            ;;
    esac

    # 验证安装
    if command -v php &> /dev/null; then
        local php_version=$(php -r "echo PHP_VERSION;")
        echo -e "${GREEN}成功安装PHP ${php_version}${NC}"
    else
        echo -e "${RED}PHP安装失败${NC}"
        exit 1
    fi
}

# 安装Composer
install_composer() {
    echo -e "${BLUE}安装Composer...${NC}"

    # 检查用户权限
    if [ "$(id -u)" = "0" ]; then
        # 如果当前用户是root，不需要sudo
        USE_SUDO=""
    else
        USE_SUDO="sudo"
    fi

    # 下载Composer安装脚本
    curl -sS https://getcomposer.org/installer | php

    # 移动到全局目录
    $USE_SUDO mv composer.phar /usr/local/bin/composer

    # 验证安装
    if command -v composer &> /dev/null; then
        local composer_version=$(composer --version | cut -d' ' -f3)
        echo -e "${GREEN}成功安装Composer ${composer_version}${NC}"
    else
        echo -e "${RED}Composer安装失败${NC}"
        exit 1
    fi
}

# 创建PVM目录结构
create_pvm_dirs() {
    echo -e "${BLUE}创建PVM目录结构...${NC}"

    mkdir -p "$BIN_DIR"
    mkdir -p "$VERSIONS_DIR"
    mkdir -p "$SHIMS_DIR"

    echo -e "${GREEN}目录结构创建完成${NC}"
}

# 克隆PVM仓库
clone_pvm_repo() {
    echo -e "${BLUE}克隆PVM仓库...${NC}"

    # 检查用户权限
    if [ "$(id -u)" = "0" ]; then
        # 如果当前用户是root，不需要sudo
        USE_SUDO=""
    else
        USE_SUDO="sudo"
    fi

    # 如果git未安装，则安装git
    if ! command -v git &> /dev/null; then
        local pkg_manager=$(detect_package_manager)
        case $pkg_manager in
            apt)
                $USE_SUDO apt-get install -y git
                ;;
            yum|dnf)
                $USE_SUDO $pkg_manager install -y git
                ;;
            apk)
                $USE_SUDO apk add git
                ;;
            *)
                echo -e "${RED}错误: 不支持的包管理器，无法自动安装git${NC}"
                echo -e "${YELLOW}请手动安装git后再运行此脚本${NC}"
                exit 1
                ;;
        esac
    fi

    # 复制当前目录到PVM目录
    cp -r "$(pwd)" "$PVM_DIR/repo"

    # 进入仓库目录并安装依赖
    cd "$PVM_DIR/repo"
    composer install

    # 创建符号链接
    ln -sf "$PVM_DIR/repo/bin/pvm" "$BIN_DIR/pvm"

    echo -e "${GREEN}PVM仓库克隆完成${NC}"
}

# 配置Shell集成
configure_shell() {
    echo -e "${BLUE}配置Shell集成...${NC}"

    # 检测Shell类型
    local shell_type=$(basename "$SHELL")
    local shell_config=""

    case $shell_type in
        bash)
            shell_config="$HOME/.bashrc"
            ;;
        zsh)
            shell_config="$HOME/.zshrc"
            ;;
        *)
            echo -e "${YELLOW}未知的Shell类型: ${shell_type}${NC}"
            echo -e "${YELLOW}请手动将PVM配置添加到您的Shell配置文件中${NC}"
            return
            ;;
    esac

    # 添加PVM配置到Shell配置文件
    cat >> "$shell_config" << EOF

# PHP Version Manager
export PVM_DIR="\$HOME/.pvm"
export PATH="\$PVM_DIR/shims:\$PVM_DIR/bin:\$PATH"
source "\$PVM_DIR/repo/shell/pvm.sh"
EOF

    echo -e "${GREEN}Shell集成配置完成${NC}"
    echo -e "${YELLOW}请运行 'source ${shell_config}' 或重新打开终端以使配置生效${NC}"
}

# 主安装流程
main() {
    echo -e "${BLUE}开始安装PVM...${NC}"

    # 安装依赖
    install_dependencies

    # 检查PHP是否已安装
    if ! check_php_installed; then
        echo -e "${YELLOW}正在安装基础PHP版本...${NC}"
        install_base_php
    fi

    # 检查Composer是否已安装
    if ! command -v composer &> /dev/null; then
        echo -e "${YELLOW}正在安装Composer...${NC}"
        install_composer
    fi

    # 创建PVM目录结构
    create_pvm_dirs

    # 克隆PVM仓库
    clone_pvm_repo

    # 配置Shell集成
    configure_shell

    echo -e "${GREEN}PVM安装完成!${NC}"
    echo -e "${BLUE}使用 'pvm help' 查看可用命令${NC}"
}

# 执行主安装流程
main
