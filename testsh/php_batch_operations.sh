#!/bin/bash
# PHP批量操作脚本
# 用于批量安装PHP版本和扩展

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
    echo -e "${BLUE}PHP批量操作脚本${NC}"
    echo "用法: $0 [命令] [参数]"
    echo ""
    echo "命令:"
    echo "  install-versions <版本列表>    批量安装指定的PHP版本"
    echo "  install-extensions <扩展列表> [版本]  批量安装指定的PHP扩展"
    echo "  setup-dev-env [版本]           设置开发环境"
    echo "  setup-prod-env [版本]          设置生产环境"
    echo "  cleanup                       清理临时文件和缓存"
    echo "  help                          显示帮助信息"
    echo ""
    echo "参数:"
    echo "  版本列表                       以逗号分隔的PHP版本列表，如 7.4.33,8.0.30,8.1.27"
    echo "  扩展列表                       以逗号分隔的PHP扩展列表，如 redis,xdebug,mongodb"
    echo "  版本                          PHP版本，如 7.4.33，默认为当前使用的版本"
    echo ""
    echo "示例:"
    echo "  $0 install-versions 7.4.33,8.0.30,8.1.27"
    echo "  $0 install-extensions redis,xdebug,mongodb"
    echo "  $0 install-extensions redis,xdebug,mongodb 7.4.33"
    echo "  $0 setup-dev-env 7.4.33"
    echo "  $0 setup-prod-env 7.4.33"
    echo "  $0 cleanup"
}

# 获取当前PHP版本
get_current_php_version() {
    local version=$(php -v | grep -oE '^PHP [0-9]+\.[0-9]+\.[0-9]+' | cut -d' ' -f2)
    echo $version
}

# 批量安装PHP版本
install_versions() {
    local versions=$1
    
    if [ -z "$versions" ]; then
        echo -e "${RED}错误: 请指定要安装的PHP版本列表${NC}"
        exit 1
    fi
    
    # 将版本列表拆分为数组
    IFS=',' read -ra VERSION_ARRAY <<< "$versions"
    
    echo -e "${YELLOW}将安装以下PHP版本:${NC}"
    for version in "${VERSION_ARRAY[@]}"; do
        echo "  - $version"
    done
    echo ""
    
    # 确认安装
    read -p "是否继续安装? (y/n): " confirm
    if [ "$confirm" != "y" ]; then
        echo -e "${YELLOW}已取消安装${NC}"
        exit 0
    fi
    
    # 安装每个版本
    for version in "${VERSION_ARRAY[@]}"; do
        echo -e "${YELLOW}正在安装PHP $version...${NC}"
        
        pvm install $version
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}PHP $version 安装成功${NC}"
        else
            echo -e "${RED}PHP $version 安装失败${NC}"
        fi
        
        echo ""
    done
    
    echo -e "${GREEN}所有PHP版本安装完成${NC}"
}

# 批量安装PHP扩展
install_extensions() {
    local extensions=$1
    local version=$2
    
    if [ -z "$extensions" ]; then
        echo -e "${RED}错误: 请指定要安装的PHP扩展列表${NC}"
        exit 1
    fi
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 安装扩展${NC}"
    fi
    
    # 将扩展列表拆分为数组
    IFS=',' read -ra EXTENSION_ARRAY <<< "$extensions"
    
    echo -e "${YELLOW}将为PHP $version 安装以下扩展:${NC}"
    for extension in "${EXTENSION_ARRAY[@]}"; do
        echo "  - $extension"
    done
    echo ""
    
    # 确认安装
    read -p "是否继续安装? (y/n): " confirm
    if [ "$confirm" != "y" ]; then
        echo -e "${YELLOW}已取消安装${NC}"
        exit 0
    fi
    
    # 安装每个扩展
    for extension in "${EXTENSION_ARRAY[@]}"; do
        echo -e "${YELLOW}正在为PHP $version 安装扩展 $extension...${NC}"
        
        pvm ext install $extension $version
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}扩展 $extension 安装成功${NC}"
        else
            echo -e "${RED}扩展 $extension 安装失败${NC}"
        fi
        
        echo ""
    done
    
    echo -e "${GREEN}所有扩展安装完成${NC}"
}

# 设置开发环境
setup_dev_env() {
    local version=$1
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 设置开发环境${NC}"
    fi
    
    echo -e "${YELLOW}正在为PHP $version 设置开发环境...${NC}"
    
    # 安装开发环境所需的扩展
    local dev_extensions="xdebug,redis,mongodb,imagick,opcache,zip,curl,json,mbstring,xml"
    install_extensions $dev_extensions $version
    
    # 配置PHP
    echo -e "${YELLOW}正在配置PHP...${NC}"
    
    # 获取PHP配置文件路径
    local php_ini=$(php -i | grep "Loaded Configuration File" | awk '{print $5}')
    
    if [ -z "$php_ini" ]; then
        echo -e "${RED}错误: 无法获取PHP配置文件路径${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}PHP配置文件路径: $php_ini${NC}"
    
    # 备份原始配置文件
    cp $php_ini ${php_ini}.bak
    
    # 修改配置文件
    echo -e "${YELLOW}正在修改PHP配置...${NC}"
    
    # 开发环境配置
    cat > $php_ini << EOF
; PHP开发环境配置

; 基本设置
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /tmp/php_errors.log
memory_limit = 256M
max_execution_time = 120
max_input_time = 120
post_max_size = 50M
upload_max_filesize = 50M
date.timezone = UTC

; Xdebug配置
[xdebug]
xdebug.mode = debug,develop
xdebug.client_host = 127.0.0.1
xdebug.client_port = 9003
xdebug.idekey = PHPSTORM
xdebug.start_with_request = yes
xdebug.log = /tmp/xdebug.log
xdebug.log_level = 7

; OPcache配置
[opcache]
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 1
opcache.revalidate_freq = 0
EOF
    
    echo -e "${GREEN}PHP开发环境配置完成${NC}"
    echo -e "${YELLOW}原始配置文件已备份为: ${php_ini}.bak${NC}"
    
    # 创建phpinfo文件
    echo -e "${YELLOW}正在创建phpinfo文件...${NC}"
    
    cat > phpinfo.php << 'EOF'
<?php
phpinfo();
EOF
    
    echo -e "${GREEN}phpinfo文件已创建: $(pwd)/phpinfo.php${NC}"
    
    # 创建测试文件
    echo -e "${YELLOW}正在创建测试文件...${NC}"
    
    cat > test.php << 'EOF'
<?php
// PHP开发环境测试文件

echo "PHP版本: " . PHP_VERSION . "\n";
echo "操作系统: " . PHP_OS . "\n";
echo "Web服务器: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "当前时间: " . date('Y-m-d H:i:s') . "\n\n";

echo "已加载的扩展:\n";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $extension) {
    echo "- $extension\n";
}

echo "\nXdebug状态: " . (extension_loaded('xdebug') ? '已启用' : '未启用') . "\n";
echo "OPcache状态: " . (extension_loaded('opcache') ? '已启用' : '未启用') . "\n";

echo "\n开发环境设置完成!\n";
EOF
    
    echo -e "${GREEN}测试文件已创建: $(pwd)/test.php${NC}"
    echo -e "${YELLOW}您可以运行 'php test.php' 来测试开发环境${NC}"
    
    echo -e "${GREEN}PHP $version 开发环境设置完成${NC}"
}

# 设置生产环境
setup_prod_env() {
    local version=$1
    
    if [ -z "$version" ]; then
        version=$(get_current_php_version)
        echo -e "${YELLOW}未指定PHP版本，将为当前版本 $version 设置生产环境${NC}"
    fi
    
    echo -e "${YELLOW}正在为PHP $version 设置生产环境...${NC}"
    
    # 安装生产环境所需的扩展
    local prod_extensions="redis,opcache,zip,curl,json,mbstring,xml"
    install_extensions $prod_extensions $version
    
    # 配置PHP
    echo -e "${YELLOW}正在配置PHP...${NC}"
    
    # 获取PHP配置文件路径
    local php_ini=$(php -i | grep "Loaded Configuration File" | awk '{print $5}')
    
    if [ -z "$php_ini" ]; then
        echo -e "${RED}错误: 无法获取PHP配置文件路径${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}PHP配置文件路径: $php_ini${NC}"
    
    # 备份原始配置文件
    cp $php_ini ${php_ini}.bak
    
    # 修改配置文件
    echo -e "${YELLOW}正在修改PHP配置...${NC}"
    
    # 生产环境配置
    cat > $php_ini << EOF
; PHP生产环境配置

; 基本设置
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/log/php_errors.log
memory_limit = 128M
max_execution_time = 30
max_input_time = 60
post_max_size = 8M
upload_max_filesize = 2M
date.timezone = UTC
expose_php = Off

; OPcache配置
[opcache]
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.revalidate_freq = 0
opcache.save_comments = 0
opcache.fast_shutdown = 1
EOF
    
    echo -e "${GREEN}PHP生产环境配置完成${NC}"
    echo -e "${YELLOW}原始配置文件已备份为: ${php_ini}.bak${NC}"
    
    echo -e "${GREEN}PHP $version 生产环境设置完成${NC}"
}

# 清理临时文件和缓存
cleanup() {
    echo -e "${YELLOW}正在清理临时文件和缓存...${NC}"
    
    # 清理PVM缓存
    echo -e "${YELLOW}正在清理PVM缓存...${NC}"
    pvm cache clear
    
    # 清理PHP缓存
    echo -e "${YELLOW}正在清理PHP缓存...${NC}"
    if php -m | grep -q "opcache"; then
        php -r 'if(function_exists("opcache_reset")) opcache_reset();'
        echo -e "${GREEN}OPcache已清理${NC}"
    fi
    
    # 清理临时文件
    echo -e "${YELLOW}正在清理临时文件...${NC}"
    rm -f phpinfo.php test.php
    
    echo -e "${GREEN}清理完成${NC}"
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
        install-versions)
            install_versions $param
            ;;
        install-extensions)
            install_extensions $param $version
            ;;
        setup-dev-env)
            setup_dev_env $param
            ;;
        setup-prod-env)
            setup_prod_env $param
            ;;
        cleanup)
            cleanup
            ;;
        help|*)
            show_help
            ;;
    esac
}

# 执行主函数
main "$@"
