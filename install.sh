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
    local php_version=${1:-$DEFAULT_PHP_VERSION}
    local pkg_manager=$(detect_package_manager)
    echo -e "${BLUE}安装PHP ${php_version}...${NC}"

    # 检查用户权限
    if [ "$(id -u)" = "0" ]; then
        # 如果当前用户是root，不需要sudo
        USE_SUDO=""
    else
        USE_SUDO="sudo"
    fi

    # 提取主要版本号
    local php_major_version=$(echo $php_version | cut -d. -f1)
    local php_minor_version=$(echo $php_version | cut -d. -f2)
    local php_package_version="${php_major_version}.${php_minor_version}"

    case $pkg_manager in
        apt)
            # 对于Ubuntu/Debian，可能需要添加PPA来安装特定版本
            if [ "$php_major_version" -ge 5 ] && [ "$php_major_version" -le 8 ]; then
                # 检查是否已安装add-apt-repository命令
                if ! command -v add-apt-repository &> /dev/null; then
                    $USE_SUDO apt-get update
                    $USE_SUDO apt-get install -y software-properties-common
                fi

                # 添加PHP仓库
                $USE_SUDO add-apt-repository -y ppa:ondrej/php
                $USE_SUDO apt-get update

                # 安装特定版本的PHP
                $USE_SUDO apt-get install -y php${php_package_version} php${php_package_version}-cli php${php_package_version}-common php${php_package_version}-curl php${php_package_version}-xml php${php_package_version}-mbstring
            else
                echo -e "${RED}错误: 不支持的PHP版本 ${php_version}${NC}"
                echo -e "${YELLOW}请选择PHP 5.x-8.x的版本${NC}"
                exit 1
            fi
            ;;
        yum)
            # 对于CentOS/RHEL，可能需要添加EPEL和Remi仓库
            if [ "$php_major_version" -ge 5 ] && [ "$php_major_version" -le 8 ]; then
                # 安装EPEL仓库
                $USE_SUDO yum install -y epel-release

                # 安装Remi仓库
                $USE_SUDO yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm

                # 启用特定版本的PHP仓库
                $USE_SUDO yum-config-manager --enable remi-php${php_major_version}${php_minor_version}

                # 安装PHP
                $USE_SUDO yum install -y php php-cli php-common php-curl php-xml php-mbstring
            else
                echo -e "${RED}错误: 不支持的PHP版本 ${php_version}${NC}"
                echo -e "${YELLOW}请选择PHP 5.x-8.x的版本${NC}"
                exit 1
            fi
            ;;
        dnf)
            # 对于Fedora，可能需要添加仓库
            if [ "$php_major_version" -ge 5 ] && [ "$php_major_version" -le 8 ]; then
                # 安装DNF模块
                $USE_SUDO dnf install -y dnf-utils

                # 重置PHP模块
                $USE_SUDO dnf module reset php -y

                # 启用特定版本的PHP模块
                $USE_SUDO dnf module enable php:${php_major_version}.${php_minor_version} -y

                # 安装PHP
                $USE_SUDO dnf install -y php php-cli php-common php-curl php-xml php-mbstring
            else
                echo -e "${RED}错误: 不支持的PHP版本 ${php_version}${NC}"
                echo -e "${YELLOW}请选择PHP 5.x-8.x的版本${NC}"
                exit 1
            fi
            ;;
        apk)
            # 对于Alpine，版本选择较为有限
            if [ "$php_major_version" -ge 7 ] && [ "$php_major_version" -le 8 ]; then
                $USE_SUDO apk update

                # 安装特定版本的PHP
                if [ "$php_major_version" -eq 7 ]; then
                    $USE_SUDO apk add php7 php7-cli php7-common php7-curl php7-xml php7-mbstring
                elif [ "$php_major_version" -eq 8 ]; then
                    $USE_SUDO apk add php8 php8-cli php8-common php8-curl php8-xml php8-mbstring
                fi
            else
                echo -e "${RED}错误: Alpine仅支持PHP 7.x-8.x${NC}"
                echo -e "${YELLOW}请选择PHP 7.x-8.x的版本${NC}"
                exit 1
            fi
            ;;
        *)
            echo -e "${RED}错误: 不支持的包管理器，无法自动安装PHP${NC}"
            echo -e "${YELLOW}请手动安装PHP ${php_version}后再运行此脚本${NC}"
            exit 1
            ;;
    esac

    # 验证安装
    if command -v php &> /dev/null; then
        local installed_php_version=$(php -r "echo PHP_VERSION;")
        echo -e "${GREEN}成功安装PHP ${installed_php_version}${NC}"

        # 检查安装的版本是否与请求的版本匹配
        local installed_major=$(php -r "echo PHP_MAJOR_VERSION;")
        local installed_minor=$(php -r "echo PHP_MINOR_VERSION;")

        if [ "$installed_major" != "$php_major_version" ] || [ "$installed_minor" != "$php_minor_version" ]; then
            echo -e "${YELLOW}警告: 安装的PHP版本 (${installed_php_version}) 与请求的版本 (${php_version}) 不完全匹配${NC}"
            echo -e "${YELLOW}这可能是由于包管理器的限制或仓库中没有精确的版本${NC}"
        fi
    else
        echo -e "${RED}PHP安装失败${NC}"
        exit 1
    fi
}

# 检查PHP版本
check_php_version() {
    if command -v php &> /dev/null; then
        php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;'
    else
        echo "0.0"
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

    # 获取PHP版本
    local php_version=$(check_php_version)
    local php_major_version=$(echo $php_version | cut -d. -f1)
    local php_minor_version=$(echo $php_version | cut -d. -f2)

    echo -e "${BLUE}检测到PHP版本: ${php_version}${NC}"

    # 根据PHP版本选择合适的Composer版本
    if [ "$php_major_version" -lt 5 ] || ([ "$php_major_version" -eq 5 ] && [ "$php_minor_version" -lt 3 ]); then
        echo -e "${RED}错误: PHP版本过低，Composer需要PHP 5.3.2+${NC}"
        echo -e "${YELLOW}请升级PHP版本后再安装Composer${NC}"
        exit 1
    elif [ "$php_major_version" -lt 7 ] || ([ "$php_major_version" -eq 7 ] && [ "$php_minor_version" -lt 2 ]); then
        # PHP 5.3.2 - 7.1.x: 使用Composer 1.x
        echo -e "${YELLOW}PHP版本低于7.2，将安装Composer 1.x${NC}"
        curl -sS https://getcomposer.org/installer | php -- --1
    elif [ "$php_major_version" -lt 8 ] || ([ "$php_major_version" -eq 8 ] && [ "$php_minor_version" -lt 1 ]); then
        # PHP 7.2.5 - 8.0.x: 可以使用Composer 2.2.x (最后支持PHP 7.2的版本)
        echo -e "${YELLOW}PHP版本低于8.1，将安装Composer 2.2.x${NC}"
        curl -sS https://getcomposer.org/installer | php -- --2.2
    else
        # PHP 8.1+: 使用最新的Composer
        echo -e "${GREEN}PHP版本 8.1+，将安装最新版Composer${NC}"
        curl -sS https://getcomposer.org/installer | php
    fi

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

    # 设置PVM仓库URL
    local PVM_REPO_URL="https://github.com/dongasai/php-version-manager.git"

    # 通过HTTP克隆项目到PVM目录
    echo -e "${YELLOW}正在从 ${PVM_REPO_URL} 克隆项目...${NC}"
    git clone "$PVM_REPO_URL" "$PVM_DIR/repo"

    if [ $? -ne 0 ]; then
        echo -e "${RED}克隆仓库失败，请检查网络连接或仓库URL${NC}"
        exit 1
    fi

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

# 显示PHP版本选择菜单
show_php_version_menu() {
    echo -e "${BLUE}请选择要安装的PHP版本:${NC}"
    echo "1) PHP 5.6 (旧版，兼容性好)"
    echo "2) PHP 7.0 (旧版)"
    echo "3) PHP 7.1 (旧版)"
    echo "4) PHP 7.2 (稳定版)"
    echo "5) PHP 7.3 (稳定版)"
    echo "6) PHP 7.4 (推荐版本)"
    echo "7) PHP 8.0 (新特性)"
    echo "8) PHP 8.1 (新特性)"
    echo "9) PHP 8.2 (最新版)"
    echo "0) 跳过PHP安装"

    read -p "请输入选项 [6]: " php_choice

    # 默认选择PHP 7.4
    php_choice=${php_choice:-6}

    case $php_choice in
        1) echo "5.6" ;;
        2) echo "7.0" ;;
        3) echo "7.1" ;;
        4) echo "7.2" ;;
        5) echo "7.3" ;;
        6) echo "7.4" ;;
        7) echo "8.0" ;;
        8) echo "8.1" ;;
        9) echo "8.2" ;;
        0) echo "skip" ;;
        *) echo "7.4" ;; # 默认为PHP 7.4
    esac
}

# 主安装流程
main() {
    echo -e "${BLUE}开始安装PVM...${NC}"

    # 安装依赖
    install_dependencies

    # 检查PHP是否已安装
    if ! check_php_installed; then
        echo -e "${YELLOW}未检测到PHP，需要安装基础PHP版本${NC}"

        # 显示PHP版本选择菜单
        local selected_version=$(show_php_version_menu)

        if [ "$selected_version" != "skip" ]; then
            echo -e "${BLUE}将安装PHP ${selected_version}...${NC}"
            install_base_php "$selected_version"
        else
            echo -e "${YELLOW}跳过PHP安装，请注意这可能会影响后续步骤${NC}"
        fi
    else
        # 检查PHP版本是否满足最低要求
        local php_version=$(check_php_version)
        local php_major_version=$(echo $php_version | cut -d. -f1)
        local php_minor_version=$(echo $php_version | cut -d. -f2)

        if [ "$php_major_version" -lt 5 ] || ([ "$php_major_version" -eq 5 ] && [ "$php_minor_version" -lt 3 ]); then
            echo -e "${RED}警告: 当前PHP版本 ${php_version} 过低，可能无法正常使用PVM${NC}"
            echo -e "${YELLOW}建议安装PHP 7.2+以获得最佳体验${NC}"

            # 询问用户是否安装新版本
            read -p "是否安装新版本的PHP? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                # 显示PHP版本选择菜单
                local selected_version=$(show_php_version_menu)

                if [ "$selected_version" != "skip" ]; then
                    echo -e "${BLUE}将安装PHP ${selected_version}...${NC}"
                    install_base_php "$selected_version"
                else
                    echo -e "${YELLOW}继续使用当前PHP版本${NC}"
                fi
            fi
        fi
    fi

    # 检查Composer是否已安装
    if ! command -v composer &> /dev/null; then
        echo -e "${YELLOW}正在安装Composer...${NC}"
        install_composer
    else
        # 检查已安装的Composer版本是否与PHP版本兼容
        local composer_version=$(composer --version | cut -d' ' -f3)
        local php_version=$(check_php_version)
        local php_major_version=$(echo $php_version | cut -d. -f1)
        local php_minor_version=$(echo $php_version | cut -d. -f2)

        echo -e "${BLUE}检测到Composer版本: ${composer_version}${NC}"

        # 检查Composer版本与PHP版本的兼容性
        if [ "$php_major_version" -lt 7 ] && [[ "$composer_version" == 2.* ]]; then
            echo -e "${YELLOW}警告: Composer 2.x 在PHP 5.x上可能存在兼容性问题${NC}"
            echo -e "${YELLOW}建议使用Composer 1.x或升级PHP版本${NC}"

            # 询问用户是否重新安装Composer
            read -p "是否重新安装兼容的Composer版本? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                echo -e "${BLUE}重新安装Composer...${NC}"
                install_composer
            fi
        elif [ "$php_major_version" -eq 7 ] && [ "$php_minor_version" -lt 2 ] && [[ "$composer_version" == 2.3.* || "$composer_version" > 2.3 ]]; then
            echo -e "${YELLOW}警告: Composer ${composer_version} 需要PHP 7.2.5+${NC}"
            echo -e "${YELLOW}建议使用Composer 2.2.x或升级PHP版本${NC}"

            # 询问用户是否重新安装Composer
            read -p "是否重新安装兼容的Composer版本? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                echo -e "${BLUE}重新安装Composer...${NC}"
                install_composer
            fi
        fi
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
