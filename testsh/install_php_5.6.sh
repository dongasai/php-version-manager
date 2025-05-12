#!/bin/bash
# PHP 5.6安装脚本
# 用于安装PHP 5.6版本及其常用扩展

# 设置颜色
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 检查PVM是否已安装
if ! command -v pvm &> /dev/null; then
    echo -e "${RED}错误: PVM未安装，请先安装PVM${NC}"
    exit 1
fi

# 设置PHP版本
PHP_VERSION="5.6.40"

echo -e "${BLUE}PHP ${PHP_VERSION} 安装脚本${NC}"
echo -e "${YELLOW}此脚本将安装PHP ${PHP_VERSION}及其常用扩展${NC}"
echo ""

# 确认安装
read -p "是否继续安装? (y/n): " confirm
if [ "$confirm" != "y" ]; then
    echo -e "${YELLOW}已取消安装${NC}"
    exit 0
fi

# 安装PHP 5.6
echo -e "${YELLOW}正在安装PHP ${PHP_VERSION}...${NC}"
pvm install ${PHP_VERSION} --from-source

if [ $? -ne 0 ]; then
    echo -e "${RED}PHP ${PHP_VERSION} 安装失败${NC}"
    exit 1
fi

echo -e "${GREEN}PHP ${PHP_VERSION} 安装成功${NC}"

# 切换到PHP 5.6
echo -e "${YELLOW}正在切换到PHP ${PHP_VERSION}...${NC}"
pvm use ${PHP_VERSION}

if [ $? -ne 0 ]; then
    echo -e "${RED}切换到PHP ${PHP_VERSION} 失败${NC}"
    exit 1
fi

echo -e "${GREEN}已切换到PHP ${PHP_VERSION}${NC}"

# 安装常用扩展
echo -e "${YELLOW}正在安装常用扩展...${NC}"

# PHP 5.6常用扩展列表
EXTENSIONS=(
    "mysqli"
    "pdo_mysql"
    "mbstring"
    "gd"
    "curl"
    "json"
    "xml"
    "zip"
    "opcache"
)

for ext in "${EXTENSIONS[@]}"; do
    echo -e "${YELLOW}正在安装扩展: ${ext}${NC}"
    pvm ext install ${ext} ${PHP_VERSION}
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}扩展 ${ext} 安装成功${NC}"
    else
        echo -e "${RED}扩展 ${ext} 安装失败，继续安装其他扩展${NC}"
    fi
done

# 配置PHP
echo -e "${YELLOW}正在配置PHP...${NC}"

# 获取PHP配置文件路径
PHP_INI=$(pvm config get php.ini ${PHP_VERSION})

if [ -z "$PHP_INI" ]; then
    echo -e "${RED}错误: 无法获取PHP配置文件路径${NC}"
    exit 1
fi

echo -e "${YELLOW}PHP配置文件路径: ${PHP_INI}${NC}"

# 备份原始配置文件
cp ${PHP_INI} ${PHP_INI}.bak

# 修改配置文件
echo -e "${YELLOW}正在修改PHP配置...${NC}"

# PHP 5.6配置
cat > ${PHP_INI} << EOF
; PHP 5.6配置

; 基本设置
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /tmp/php_errors.log
memory_limit = 128M
max_execution_time = 60
max_input_time = 60
post_max_size = 8M
upload_max_filesize = 2M
date.timezone = UTC

; 扩展设置
extension_dir = "$(pvm config get extension_dir ${PHP_VERSION})"

; 常用扩展
extension=mysqli.so
extension=pdo_mysql.so
extension=mbstring.so
extension=gd.so
extension=curl.so
extension=json.so
extension=xml.so
extension=zip.so

; OPcache配置
zend_extension=opcache.so
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 1
opcache.revalidate_freq = 0
EOF

echo -e "${GREEN}PHP配置完成${NC}"
echo -e "${YELLOW}原始配置文件已备份为: ${PHP_INI}.bak${NC}"

# 创建测试文件
echo -e "${YELLOW}正在创建测试文件...${NC}"

cat > php56_test.php << 'EOF'
<?php
// PHP 5.6测试文件

echo "PHP版本: " . PHP_VERSION . "\n";
echo "操作系统: " . PHP_OS . "\n";
echo "当前时间: " . date('Y-m-d H:i:s') . "\n\n";

echo "已加载的扩展:\n";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $extension) {
    echo "- $extension\n";
}

echo "\nOPcache状态: " . (extension_loaded('opcache') ? '已启用' : '未启用') . "\n";

echo "\nPHP 5.6安装完成!\n";
EOF

echo -e "${GREEN}测试文件已创建: $(pwd)/php56_test.php${NC}"
echo -e "${YELLOW}您可以运行 'php php56_test.php' 来测试PHP 5.6环境${NC}"

echo -e "${GREEN}PHP ${PHP_VERSION} 及其常用扩展安装完成${NC}"
