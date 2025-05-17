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
DEFAULT_PHP_VERSION="8.3"

# 仓库源配置
GITEE_REPO_URL="https://gitee.com/Dongasai/php-version-manager.git"
GITHUB_REPO_URL="https://github.com/dongasai/php-version-manager.git"
DEFAULT_REPO_SOURCE="gitee"  # 默认使用gitee源

# 解析命令行参数
while [[ $# -gt 0 ]]; do
    case $1 in
        --dir=*)
            CUSTOM_DIR="${1#*=}"
            shift
            ;;
        --source=*)
            REPO_SOURCE="${1#*=}"
            shift
            ;;
        --help)
            echo "用法: ./install.sh [选项]"
            echo ""
            echo "选项:"
            echo "  --dir=PATH    指定安装目录，默认为 $HOME/.pvm"
            echo "  --source=SOURCE 指定仓库源，可选值: gitee, github，默认为 gitee"
            echo "  --help        显示此帮助信息"
            exit 0
            ;;
        *)
            echo "未知选项: $1"
            echo "使用 --help 查看帮助信息"
            exit 1
            ;;
    esac
done

# 设置仓库源
REPO_SOURCE="${REPO_SOURCE:-$DEFAULT_REPO_SOURCE}"

# 目录设置
PVM_DIR="${CUSTOM_DIR:-$HOME/.pvm}"
BIN_DIR="$PVM_DIR/bin"
VERSIONS_DIR="$PVM_DIR/versions"
SHIMS_DIR="$PVM_DIR/shims"

echo -e "${BLUE}安装目录: ${PVM_DIR}${NC}"

# 打印信息
echo -e "${BLUE}PHP Version Manager (PVM) 安装脚本${NC}"
echo -e "${BLUE}版本: ${PVM_VERSION}${NC}"
echo -e "${BLUE}仓库源: ${REPO_SOURCE}${NC}"
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
    # 确保php_version是有效的版本号格式
    if [[ ! "$php_version" =~ ^[0-9]+\.[0-9]+$ ]]; then
        echo -e "${RED}错误: 无效的PHP版本格式: ${php_version}${NC}"
        echo -e "${YELLOW}请选择有效的PHP版本，例如7.4或8.1${NC}"

        # 如果版本无效，使用默认版本
        php_version="$DEFAULT_PHP_VERSION"
        echo -e "${YELLOW}使用默认版本 ${php_version}${NC}"
    fi

    local php_major_version=$(echo $php_version | cut -d. -f1)
    local php_minor_version=$(echo $php_version | cut -d. -f2)
    local php_package_version="${php_major_version}.${php_minor_version}"

    # 创建版本目录
    local version_dir="$VERSIONS_DIR/$php_package_version"
    local bin_dir="$version_dir/bin"

    # 确保目录存在
    mkdir -p "$bin_dir"

    case $pkg_manager in
        apt)
            # 对于Ubuntu/Debian，使用系统默认源安装PHP
            if [ "$php_major_version" -ge 7 ] && [ "$php_major_version" -le 8 ]; then
                $USE_SUDO apt-get update

                # 尝试安装PHP，使用系统默认源
                echo -e "${YELLOW}尝试从系统默认源安装PHP ${php_package_version}...${NC}"

                # 检查系统源中是否有指定版本的PHP
                if apt-cache show php${php_package_version} &> /dev/null; then
                    # 安装特定版本的PHP
                    $USE_SUDO apt-get install -y php${php_package_version} php${php_package_version}-cli php${php_package_version}-common php${php_package_version}-curl php${php_package_version}-xml php${php_package_version}-mbstring
                else
                    # 如果找不到特定版本，尝试安装默认版本
                    echo -e "${YELLOW}系统源中未找到PHP ${php_package_version}，尝试安装默认PHP版本...${NC}"
                    $USE_SUDO apt-get install -y php php-cli php-common php-curl php-xml php-mbstring
                fi
            else
                echo -e "${RED}错误: 不支持的PHP版本 ${php_version}${NC}"
                echo -e "${YELLOW}请选择PHP 7.x-8.x的版本${NC}"
                exit 1
            fi
            ;;
        yum)
            # 对于CentOS/RHEL，使用系统默认源安装PHP
            if [ "$php_major_version" -ge 7 ] && [ "$php_major_version" -le 8 ]; then
                $USE_SUDO yum check-update

                # 尝试安装PHP，使用系统默认源
                echo -e "${YELLOW}尝试从系统默认源安装PHP...${NC}"

                # 检查系统源中是否有PHP
                if yum list available php &> /dev/null; then
                    # 安装PHP
                    $USE_SUDO yum install -y php php-cli php-common php-curl php-xml php-mbstring
                else
                    echo -e "${YELLOW}系统源中未找到PHP，请考虑手动安装或使用其他方法${NC}"
                    echo -e "${YELLOW}跳过PHP安装...${NC}"
                fi
            else
                echo -e "${RED}错误: 不支持的PHP版本 ${php_version}${NC}"
                echo -e "${YELLOW}请选择PHP 7.x-8.x的版本${NC}"
                exit 1
            fi
            ;;
        dnf)
            # 对于Fedora，使用系统默认源安装PHP
            if [ "$php_major_version" -ge 7 ] && [ "$php_major_version" -le 8 ]; then
                $USE_SUDO dnf check-update

                # 尝试安装PHP，使用系统默认源
                echo -e "${YELLOW}尝试从系统默认源安装PHP...${NC}"

                # 检查系统源中是否有PHP
                if dnf list available php &> /dev/null; then
                    # 安装PHP
                    $USE_SUDO dnf install -y php php-cli php-common php-curl php-xml php-mbstring
                else
                    echo -e "${YELLOW}系统源中未找到PHP，请考虑手动安装或使用其他方法${NC}"
                    echo -e "${YELLOW}跳过PHP安装...${NC}"
                fi
            else
                echo -e "${RED}错误: 不支持的PHP版本 ${php_version}${NC}"
                echo -e "${YELLOW}请选择PHP 7.x-8.x的版本${NC}"
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
        local installed_version_full="${installed_major}.${installed_minor}"

        if [ "$installed_major" != "$php_major_version" ] || [ "$installed_minor" != "$php_minor_version" ]; then
            echo -e "${YELLOW}警告: 安装的PHP版本 (${installed_php_version}) 与请求的版本 (${php_version}) 不完全匹配${NC}"
            echo -e "${YELLOW}这是因为使用了系统默认源，而该源中可能没有精确的版本${NC}"
            echo -e "${YELLOW}如果需要特定版本，您可能需要手动安装${NC}"

            # 更新版本目录为实际安装的版本
            version_dir="$VERSIONS_DIR/$installed_version_full"
            bin_dir="$version_dir/bin"
            mkdir -p "$bin_dir"
        fi

        # 创建PHP可执行文件的符号链接到版本目录
        local php_path=$(which php)
        echo -e "${BLUE}创建PHP符号链接: ${php_path} -> ${bin_dir}/php${NC}"
        ln -sf "$php_path" "$bin_dir/php"

        # 创建符号链接到shims目录
        echo -e "${BLUE}创建PHP符号链接到shims目录${NC}"
        ln -sf "$bin_dir/php" "$SHIMS_DIR/php"

        # 设置为当前版本
        echo "$installed_version_full" > "$PVM_DIR/config/current"
        echo -e "${GREEN}已将PHP ${installed_version_full}设置为当前版本${NC}"
    else
        echo -e "${RED}PHP安装失败${NC}"
        echo -e "${YELLOW}这可能是因为系统默认源中没有PHP或者版本不匹配${NC}"
        echo -e "${YELLOW}请考虑手动安装PHP后再运行此脚本${NC}"
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
    local php_version_full="${php_major_version}.${php_minor_version}"

    echo -e "${BLUE}检测到PHP版本: ${php_version}${NC}"

    # 创建版本目录
    local version_dir="$VERSIONS_DIR/$php_version_full"
    local bin_dir="$version_dir/bin"

    # 确保目录存在
    mkdir -p "$bin_dir"

    # 创建临时目录用于下载Composer
    local temp_dir=$(mktemp -d)
    echo -e "${BLUE}使用临时目录下载Composer: ${temp_dir}${NC}"
    cd "$temp_dir"

    # 根据PHP版本选择合适的Composer版本
    if [ "$php_major_version" -lt 7 ]; then
        echo -e "${RED}错误: PHP版本过低，Composer需要PHP 7.0+${NC}"
        echo -e "${YELLOW}请升级PHP版本后再安装Composer${NC}"
        exit 1
    elif [ "$php_major_version" -eq 7 ] && [ "$php_minor_version" -lt 2 ]; then
        # PHP 7.0 - 7.1.x: 使用Composer 1.x
        echo -e "${YELLOW}PHP版本低于7.2，将安装Composer 1.x${NC}"
        curl -v https://getcomposer.org/installer -o composer-setup.php
        php composer-setup.php --1
    elif [ "$php_major_version" -lt 8 ] || ([ "$php_major_version" -eq 8 ] && [ "$php_minor_version" -lt 1 ]); then
        # PHP 7.2.5 - 8.0.x: 可以使用Composer 2.2.x (最后支持PHP 7.2的版本)
        echo -e "${YELLOW}PHP版本低于8.1，将安装Composer 2.2.x${NC}"
        curl -v https://getcomposer.org/installer -o composer-setup.php
        php composer-setup.php --2.2
    else
        # PHP 8.1+: 使用最新的Composer
        echo -e "${GREEN}PHP版本 8.1+，将安装最新版Composer${NC}"
        curl -v https://getcomposer.org/installer -o composer-setup.php
        php composer-setup.php
    fi

    # 检查下载和安装结果
    if [ $? -ne 0 ]; then
        echo -e "${RED}Composer安装程序执行失败${NC}"
        ls -la
        return 1
    fi

    # 列出当前目录内容
    echo -e "${BLUE}临时目录内容:${NC}"
    ls -la

    # 移动到版本对应的bin目录
    if [ -f "composer.phar" ]; then
        echo -e "${BLUE}找到composer.phar文件，移动到版本目录...${NC}"
        mv composer.phar "$bin_dir/composer"
        chmod +x "$bin_dir/composer"
        echo -e "${YELLOW}Composer已安装到版本目录: $bin_dir/composer${NC}"

        # 创建符号链接到shims目录
        ln -sf "$bin_dir/composer" "$SHIMS_DIR/composer"

        # 验证安装
        if [ -f "$bin_dir/composer" ]; then
            local composer_version=$("$bin_dir/composer" --version | cut -d' ' -f3 2>/dev/null || echo "未知")
            echo -e "${GREEN}成功安装Composer ${composer_version}${NC}"
        else
            echo -e "${RED}Composer安装失败${NC}"
        fi
    else
        echo -e "${YELLOW}未找到composer.phar文件，尝试查找其他可能的文件名...${NC}"
        # 尝试查找其他可能的文件名
        local composer_file=$(find . -name "composer*.phar" | head -1)

        if [ -n "$composer_file" ]; then
            echo -e "${BLUE}找到Composer文件: $composer_file，移动到版本目录...${NC}"
            mv "$composer_file" "$bin_dir/composer"
            chmod +x "$bin_dir/composer"
            echo -e "${YELLOW}Composer已安装到版本目录: $bin_dir/composer${NC}"

            # 创建符号链接到shims目录
            ln -sf "$bin_dir/composer" "$SHIMS_DIR/composer"

            # 验证安装
            if [ -f "$bin_dir/composer" ]; then
                local composer_version=$("$bin_dir/composer" --version | cut -d' ' -f3 2>/dev/null || echo "未知")
                echo -e "${GREEN}成功安装Composer ${composer_version}${NC}"
            else
                echo -e "${RED}Composer安装失败${NC}"
            fi
        else
            echo -e "${RED}Composer下载失败，未找到composer相关文件${NC}"
            # 尝试手动下载composer.phar
            echo -e "${YELLOW}尝试手动下载composer.phar...${NC}"
            curl -v https://getcomposer.org/download/latest-stable/composer.phar -o composer.phar

            if [ -f "composer.phar" ]; then
                echo -e "${BLUE}手动下载成功，移动到版本目录...${NC}"
                mv composer.phar "$bin_dir/composer"
                chmod +x "$bin_dir/composer"
                echo -e "${YELLOW}Composer已安装到版本目录: $bin_dir/composer${NC}"

                # 创建符号链接到shims目录
                ln -sf "$bin_dir/composer" "$SHIMS_DIR/composer"

                # 验证安装
                if [ -f "$bin_dir/composer" ]; then
                    local composer_version=$("$bin_dir/composer" --version | cut -d' ' -f3 2>/dev/null || echo "未知")
                    echo -e "${GREEN}成功安装Composer ${composer_version}${NC}"
                else
                    echo -e "${RED}Composer安装失败${NC}"
                fi
            else
                echo -e "${RED}手动下载也失败，无法安装Composer${NC}"
            fi
        fi
    fi

    # 清理临时目录
    cd - > /dev/null
    rm -rf "$temp_dir"
}

# 创建PVM目录结构
create_pvm_dirs() {
    echo -e "${BLUE}创建PVM目录结构...${NC}"

    # 检查并清理现有的bin和shims目录
    if [ -d "$BIN_DIR" ]; then
        echo -e "${YELLOW}清理现有bin目录...${NC}"
        rm -rf "$BIN_DIR"
    fi

    if [ -d "$SHIMS_DIR" ]; then
        echo -e "${YELLOW}清理现有shims目录...${NC}"
        rm -rf "$SHIMS_DIR"
    fi

    # 创建必要的目录
    mkdir -p "$BIN_DIR"
    mkdir -p "$VERSIONS_DIR"
    mkdir -p "$SHIMS_DIR"

    # 创建配置目录（如果不存在）
    if [ ! -d "$PVM_DIR/config" ]; then
        mkdir -p "$PVM_DIR/config"
    fi

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

    # 根据选择的源设置PVM仓库URL
    local PVM_REPO_URL=""
    case $REPO_SOURCE in
        gitee)
            PVM_REPO_URL="$GITEE_REPO_URL"
            ;;
        github)
            PVM_REPO_URL="$GITHUB_REPO_URL"
            ;;
        *)
            echo -e "${YELLOW}未知的仓库源: ${REPO_SOURCE}，使用默认源 gitee${NC}"
            PVM_REPO_URL="$GITEE_REPO_URL"
            ;;
    esac

    # 检查仓库目录是否已存在，如果存在则先删除
    if [ -d "$PVM_DIR/repo" ]; then
        echo -e "${YELLOW}检测到仓库目录已存在，正在清理...${NC}"
        rm -rf "$PVM_DIR/repo"
    fi

    # 通过HTTP克隆项目到PVM目录
    echo -e "${YELLOW}正在从 ${PVM_REPO_URL} (${REPO_SOURCE}) 克隆项目...${NC}"
    git clone "$PVM_REPO_URL" "$PVM_DIR/repo"

    # 如果克隆失败，尝试使用备用源
    if [ $? -ne 0 ]; then
        echo -e "${YELLOW}从 ${REPO_SOURCE} 克隆失败，尝试使用备用源...${NC}"

        # 切换源
        if [ "$REPO_SOURCE" = "gitee" ]; then
            REPO_SOURCE="github"
            PVM_REPO_URL="$GITHUB_REPO_URL"
        else
            REPO_SOURCE="gitee"
            PVM_REPO_URL="$GITEE_REPO_URL"
        fi

        echo -e "${YELLOW}正在从备用源 ${PVM_REPO_URL} (${REPO_SOURCE}) 克隆项目...${NC}"
        git clone "$PVM_REPO_URL" "$PVM_DIR/repo"

        if [ $? -ne 0 ]; then
            echo -e "${RED}克隆仓库失败，请检查网络连接或仓库URL${NC}"
            exit 1
        fi
    fi

    # 进入仓库目录并安装依赖
    cd "$PVM_DIR/repo"

    # 获取PHP版本
    local php_version=$(check_php_version)
    local php_major_version=$(echo $php_version | cut -d. -f1)
    local php_minor_version=$(echo $php_version | cut -d. -f2)
    local php_version_full="${php_major_version}.${php_minor_version}"

    # 版本目录中的composer
    local version_composer="$VERSIONS_DIR/$php_version_full/bin/composer"

    # 优先使用版本目录中的composer
    if [ -f "$version_composer" ]; then
        echo -e "${YELLOW}使用PHP ${php_version_full}版本目录中的Composer安装依赖...${NC}"
        "$version_composer" install --no-dev

        if [ $? -ne 0 ]; then
            echo -e "${YELLOW}使用版本目录中的Composer安装依赖失败，尝试不使用autoloader...${NC}"
            "$version_composer" install --no-dev --no-autoloader
        fi
    else
        # 尝试使用系统全局composer
        echo -e "${YELLOW}未找到版本目录中的Composer，尝试使用系统全局Composer安装依赖...${NC}"
        if command -v composer &> /dev/null; then
            composer install --no-dev

            if [ $? -ne 0 ]; then
                echo -e "${YELLOW}使用系统全局Composer安装依赖失败，尝试不使用autoloader...${NC}"
                composer install --no-dev --no-autoloader
            fi
        else
            echo -e "${RED}未找到Composer，跳过依赖安装${NC}"
            echo -e "${YELLOW}PVM可能无法正常工作，建议手动安装Composer后重新运行此脚本${NC}"
            echo -e "${YELLOW}或者手动进入 $PVM_DIR/repo 目录运行 composer install${NC}"
        fi
    fi

    # 即使依赖安装失败，也继续安装PVM
    echo -e "${BLUE}继续安装PVM...${NC}"

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

    # 检查配置文件中是否已有PVM配置
    if grep -q "PHP Version Manager" "$shell_config"; then
        echo -e "${YELLOW}检测到Shell配置文件中已有PVM配置，将更新配置...${NC}"

        # 使用sed删除旧的配置
        sed -i '/# PHP Version Manager/,/source.*pvm.sh/d' "$shell_config"
    fi

    # 添加PVM配置到Shell配置文件
    cat >> "$shell_config" << EOF

# PHP Version Manager
export PVM_DIR="$PVM_DIR"
export PATH="\$PVM_DIR/shims:\$PVM_DIR/bin:\$PATH"
source "\$PVM_DIR/repo/shell/pvm.sh"
EOF

    echo -e "${GREEN}Shell集成配置完成${NC}"
    echo -e "${YELLOW}请运行 'source ${shell_config}' 或重新打开终端以使配置生效${NC}"
}

# 显示PHP版本选择菜单
show_php_version_menu() {
    echo -e "${BLUE}请选择要安装的PHP版本:${NC}"
    echo "1) PHP 7.0 (旧版)"
    echo "2) PHP 7.1 (旧版)"
    echo "3) PHP 7.2 (稳定版)"
    echo "4) PHP 7.3 (稳定版)"
    echo "5) PHP 7.4 (推荐版本)"
    echo "6) PHP 8.0 (新特性)"
    echo "7) PHP 8.1 (新特性)"
    echo "8) PHP 8.2 (最新版)"
    echo "9) PHP 8.3 (最新版)"
    echo "0) 跳过PHP安装"

    read -p "请输入选项 [5]: " php_choice

    # 默认选择PHP 7.4
    php_choice=${php_choice:-5}

    local version=""
    case $php_choice in
        1) version="7.0" ;;
        2) version="7.1" ;;
        3) version="7.2" ;;
        4) version="7.3" ;;
        5) version="7.4" ;;
        6) version="8.0" ;;
        7) version="8.1" ;;
        8) version="8.2" ;;
        9) version="8.3" ;;
        0) version="skip" ;;
        *) version="7.4" ;; # 默认为PHP 7.4
    esac

    # 返回版本号，不输出到标准输出
    echo "$version" >&1
}

# 检查目标安装目录
check_install_directory() {
    echo -e "${BLUE}检查安装目录...${NC}"

    # 检查PVM目录是否已存在
    if [ -d "$PVM_DIR" ]; then
        echo -e "${YELLOW}检测到目录 $PVM_DIR 已存在${NC}"

        # 检查是否已安装PVM
        if [ -d "$PVM_DIR/repo" ] && [ -f "$BIN_DIR/pvm" ]; then
            echo -e "${YELLOW}检测到PVM已安装在此目录${NC}"

            # 询问用户是否继续
            read -p "是否重新安装PVM? 这将覆盖现有安装 (y/n) " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                echo -e "${BLUE}安装已取消${NC}"
                exit 0
            fi

            echo -e "${YELLOW}将重新安装PVM...${NC}"

            # 备份现有配置
            if [ -d "$PVM_DIR/config" ]; then
                echo -e "${BLUE}备份现有配置...${NC}"
                backup_dir="$PVM_DIR/config_backup_$(date +%Y%m%d%H%M%S)"
                cp -r "$PVM_DIR/config" "$backup_dir"
                echo -e "${GREEN}配置已备份到 $backup_dir${NC}"
            fi
        else
            # 目录存在但不是完整的PVM安装
            echo -e "${YELLOW}目录存在但不是完整的PVM安装${NC}"

            # 询问用户是否继续
            read -p "是否在此目录安装PVM? (y/n) " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                echo -e "${BLUE}安装已取消${NC}"
                exit 0
            fi
        fi
    else
        echo -e "${GREEN}安装目录不存在，将创建新目录${NC}"
    fi
}

# 主安装流程
main() {
    echo -e "${BLUE}开始安装PVM...${NC}"

    # 检查目标安装目录
    check_install_directory

    # 安装依赖
    install_dependencies

    # 检查PHP是否已安装
    if ! check_php_installed; then
        echo -e "${YELLOW}未检测到PHP，需要安装基础PHP版本${NC}"

        # 在开发容器中，直接使用默认版本
        local selected_version="$DEFAULT_PHP_VERSION"
        echo -e "${BLUE}在开发容器中使用默认PHP版本 ${selected_version}...${NC}"
        echo -e "${YELLOW}注意：仅使用系统默认源，不会添加第三方源${NC}"
        install_base_php "$selected_version"
    else
        # 检查PHP版本是否满足最低要求
        local php_version=$(check_php_version)
        local php_major_version=$(echo $php_version | cut -d. -f1)
        local php_minor_version=$(echo $php_version | cut -d. -f2)

        if [ "$php_major_version" -lt 7 ]; then
            echo -e "${RED}警告: 当前PHP版本 ${php_version} 过低，可能无法正常使用PVM${NC}"
            echo -e "${YELLOW}建议安装PHP 7.2+以获得最佳体验${NC}"

            # 询问用户是否安装新版本
            read -p "是否安装新版本的PHP? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                # 在开发容器中，直接使用默认版本
                local selected_version="$DEFAULT_PHP_VERSION"
                echo -e "${BLUE}在开发容器中使用默认PHP版本 ${selected_version}...${NC}"
                echo -e "${YELLOW}注意：仅使用系统默认源，不会添加第三方源${NC}"
                install_base_php "$selected_version"
            fi
        fi
    fi

    # 检查Composer是否已安装
    local composer_installed=false

    if ! command -v composer &> /dev/null; then
        echo -e "${YELLOW}正在安装Composer...${NC}"
        install_composer

        # 检查Composer是否成功安装到版本目录
        local php_version=$(check_php_version)
        local php_major_version=$(echo $php_version | cut -d. -f1)
        local php_minor_version=$(echo $php_version | cut -d. -f2)
        local php_version_full="${php_major_version}.${php_minor_version}"
        local version_composer="$VERSIONS_DIR/$php_version_full/bin/composer"

        if [ -f "$version_composer" ]; then
            echo -e "${GREEN}Composer已成功安装到版本目录${NC}"
            composer_installed=true
        else
            echo -e "${YELLOW}Composer安装可能失败，但将继续安装PVM${NC}"
        fi
    else
        # 检查已安装的Composer版本是否与PHP版本兼容
        local composer_version=$(composer --version | cut -d' ' -f3)
        local php_version=$(check_php_version)
        local php_major_version=$(echo $php_version | cut -d. -f1)
        local php_minor_version=$(echo $php_version | cut -d. -f2)
        local php_version_full="${php_major_version}.${php_minor_version}"

        echo -e "${BLUE}检测到Composer版本: ${composer_version}${NC}"
        composer_installed=true

        # 检查Composer版本与PHP版本的兼容性
        if [ "$php_major_version" -eq 7 ] && [ "$php_minor_version" -lt 2 ] && [[ "$composer_version" == 2.3.* || "$composer_version" > 2.3 ]]; then
            echo -e "${YELLOW}警告: Composer ${composer_version} 需要PHP 7.2.5+${NC}"
            echo -e "${YELLOW}建议使用Composer 2.2.x或升级PHP版本${NC}"

            # 询问用户是否重新安装Composer
            read -p "是否重新安装兼容的Composer版本? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                echo -e "${BLUE}重新安装Composer...${NC}"
                install_composer

                # 检查Composer是否成功安装到版本目录
                local version_composer="$VERSIONS_DIR/$php_version_full/bin/composer"

                if [ -f "$version_composer" ]; then
                    echo -e "${GREEN}Composer已成功安装到版本目录${NC}"
                    composer_installed=true
                else
                    echo -e "${YELLOW}Composer安装可能失败，但将继续安装PVM${NC}"
                    composer_installed=false
                fi
            fi
        fi

        # 如果系统已安装Composer，但没有安装到版本目录，则安装到版本目录
        local version_composer="$VERSIONS_DIR/$php_version_full/bin/composer"
        if [ "$composer_installed" = true ] && [ ! -f "$version_composer" ]; then
            echo -e "${YELLOW}系统已安装Composer，但未安装到版本目录，正在安装到版本目录...${NC}"

            # 创建版本目录
            mkdir -p "$VERSIONS_DIR/$php_version_full/bin"

            # 复制系统Composer到版本目录
            local system_composer=$(which composer)
            cp "$system_composer" "$version_composer"
            chmod +x "$version_composer"

            # 创建符号链接到shims目录
            ln -sf "$version_composer" "$SHIMS_DIR/composer"

            echo -e "${GREEN}Composer已复制到版本目录: $version_composer${NC}"
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
