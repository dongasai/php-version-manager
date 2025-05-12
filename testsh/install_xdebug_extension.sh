#!/bin/bash
# Xdebug扩展安装脚本
# 用于为多个PHP版本安装Xdebug扩展

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

echo -e "${BLUE}Xdebug扩展安装脚本${NC}"
echo -e "${YELLOW}此脚本将为多个PHP版本安装Xdebug扩展${NC}"
echo ""

# 获取已安装的PHP版本
INSTALLED_VERSIONS=$(pvm list | grep -E '^[0-9]+\.[0-9]+\.[0-9]+' | awk '{print $1}')

if [ -z "$INSTALLED_VERSIONS" ]; then
    echo -e "${RED}错误: 未找到已安装的PHP版本${NC}"
    exit 1
fi

echo -e "${YELLOW}已安装的PHP版本:${NC}"
echo "$INSTALLED_VERSIONS"
echo ""

# 确认安装
read -p "是否为所有已安装的PHP版本安装Xdebug扩展? (y/n): " confirm
if [ "$confirm" != "y" ]; then
    echo -e "${YELLOW}已取消安装${NC}"
    exit 0
fi

# 为每个PHP版本安装Xdebug扩展
for version in $INSTALLED_VERSIONS; do
    echo -e "${YELLOW}正在为PHP $version 安装Xdebug扩展...${NC}"
    
    # 检查PHP版本是否支持Xdebug
    major_version=$(echo $version | cut -d. -f1)
    minor_version=$(echo $version | cut -d. -f2)
    
    # PHP 5.x需要使用Xdebug 2.x
    if [ "$major_version" -eq 5 ]; then
        echo -e "${YELLOW}PHP $version 需要使用Xdebug 2.x${NC}"
        pvm ext install xdebug 2.5.5 $version
    else
        pvm ext install xdebug $version
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}PHP $version Xdebug扩展安装成功${NC}"
        
        # 配置Xdebug
        PHP_INI=$(pvm config get php.ini $version)
        
        if [ -n "$PHP_INI" ]; then
            echo -e "${YELLOW}正在配置Xdebug...${NC}"
            
            # 检查PHP版本以确定正确的Xdebug配置
            if [ "$major_version" -ge 8 ] || [ "$major_version" -eq 7 -a "$minor_version" -ge 2 ]; then
                # PHP 7.2+和PHP 8.x使用新的Xdebug 3配置
                cat >> $PHP_INI << EOF

; Xdebug配置
zend_extension=xdebug
xdebug.mode = debug,develop
xdebug.client_host = 127.0.0.1
xdebug.client_port = 9003
xdebug.idekey = PHPSTORM
xdebug.start_with_request = yes
xdebug.log = /tmp/xdebug.log
xdebug.log_level = 7
EOF
            else
                # PHP 5.x和PHP 7.0-7.1使用旧的Xdebug 2配置
                cat >> $PHP_INI << EOF

; Xdebug配置
zend_extension=xdebug
xdebug.remote_enable = 1
xdebug.remote_host = 127.0.0.1
xdebug.remote_port = 9000
xdebug.remote_handler = dbgp
xdebug.remote_mode = req
xdebug.remote_autostart = 1
xdebug.idekey = PHPSTORM
xdebug.remote_log = /tmp/xdebug.log
EOF
            fi
            
            echo -e "${GREEN}Xdebug配置完成${NC}"
        else
            echo -e "${RED}无法获取PHP配置文件路径，跳过Xdebug配置${NC}"
        fi
    else
        echo -e "${RED}PHP $version Xdebug扩展安装失败${NC}"
    fi
    
    echo ""
done

# 创建Xdebug测试文件
echo -e "${YELLOW}正在创建Xdebug测试文件...${NC}"

cat > xdebug_test.php << 'EOF'
<?php
// Xdebug扩展测试文件

echo "PHP版本: " . PHP_VERSION . "\n";

// 检查Xdebug扩展是否已加载
if (!extension_loaded('xdebug')) {
    echo "错误: Xdebug扩展未加载\n";
    exit(1);
}

echo "Xdebug版本: " . phpversion('xdebug') . "\n\n";

// 获取Xdebug配置
echo "Xdebug配置:\n";
$xdebug_config = [];

// 检查PHP版本以确定正确的Xdebug配置
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    // PHP 7.2+和PHP 8.x使用新的Xdebug 3配置
    $config_vars = [
        'xdebug.mode',
        'xdebug.client_host',
        'xdebug.client_port',
        'xdebug.idekey',
        'xdebug.start_with_request',
        'xdebug.log',
        'xdebug.log_level'
    ];
} else {
    // PHP 5.x和PHP 7.0-7.1使用旧的Xdebug 2配置
    $config_vars = [
        'xdebug.remote_enable',
        'xdebug.remote_host',
        'xdebug.remote_port',
        'xdebug.remote_handler',
        'xdebug.remote_mode',
        'xdebug.remote_autostart',
        'xdebug.idekey',
        'xdebug.remote_log'
    ];
}

foreach ($config_vars as $var) {
    if (ini_get($var) !== false) {
        $xdebug_config[$var] = ini_get($var);
    }
}

foreach ($xdebug_config as $key => $value) {
    echo "  $key = $value\n";
}

echo "\n";

// 测试Xdebug功能
echo "Xdebug功能测试:\n";

// 测试var_dump()增强
echo "var_dump()增强:\n";
$test_array = ['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4]];
var_dump($test_array);
echo "\n";

// 测试堆栈跟踪
echo "堆栈跟踪:\n";
function test_function_3() {
    echo xdebug_get_function_stack() ? "堆栈跟踪功能正常\n" : "堆栈跟踪功能不可用\n";
}

function test_function_2() {
    test_function_3();
}

function test_function_1() {
    test_function_2();
}

test_function_1();
echo "\n";

// 测试代码覆盖率
if (function_exists('xdebug_start_code_coverage')) {
    echo "代码覆盖率:\n";
    xdebug_start_code_coverage();
    
    function test_coverage() {
        $a = 1;
        $b = 2;
        $c = $a + $b;
        return $c;
    }
    
    test_coverage();
    
    $coverage = xdebug_get_code_coverage();
    echo "代码覆盖率功能正常\n";
    xdebug_stop_code_coverage();
} else {
    echo "代码覆盖率: 不可用\n";
}
echo "\n";

// 测试性能分析
if (function_exists('xdebug_start_trace')) {
    echo "性能分析:\n";
    $trace_file = tempnam(sys_get_temp_dir(), 'xdebug');
    xdebug_start_trace($trace_file);
    
    function test_profiling() {
        $sum = 0;
        for ($i = 0; $i < 1000; $i++) {
            $sum += $i;
        }
        return $sum;
    }
    
    test_profiling();
    
    xdebug_stop_trace();
    echo "性能分析功能正常，跟踪文件: $trace_file\n";
} else {
    echo "性能分析: 不可用\n";
}

echo "\nXdebug扩展测试成功!\n";
EOF

echo -e "${GREEN}Xdebug测试文件已创建: $(pwd)/xdebug_test.php${NC}"
echo -e "${YELLOW}您可以运行 'php xdebug_test.php' 来测试Xdebug扩展${NC}"

echo -e "${GREEN}Xdebug扩展安装完成${NC}"
