#!/bin/bash
# 运行所有测试脚本
# 此脚本将运行所有测试脚本，并生成测试报告

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

echo -e "${BLUE}运行所有测试脚本${NC}"
echo -e "${YELLOW}此脚本将运行所有测试脚本，并生成测试报告${NC}"
echo ""

# 创建测试报告目录
REPORT_DIR="test_reports"
mkdir -p $REPORT_DIR

# 获取当前时间
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
REPORT_FILE="$REPORT_DIR/test_report_$TIMESTAMP.txt"

# 创建测试报告文件
echo "PHP版本管理器测试报告" > $REPORT_FILE
echo "生成时间: $(date)" >> $REPORT_FILE
echo "----------------------------------------" >> $REPORT_FILE
echo "" >> $REPORT_FILE

# 获取系统信息
echo "系统信息:" >> $REPORT_FILE
echo "操作系统: $(uname -a)" >> $REPORT_FILE
echo "处理器: $(grep "model name" /proc/cpuinfo | head -1 | cut -d: -f2 | sed 's/^[ \t]*//')" >> $REPORT_FILE
echo "内存: $(free -h | grep Mem | awk '{print $2}')" >> $REPORT_FILE
echo "磁盘空间: $(df -h / | tail -1 | awk '{print $4}') 可用" >> $REPORT_FILE
echo "" >> $REPORT_FILE

# 获取PVM信息
echo "PVM信息:" >> $REPORT_FILE
echo "版本: $(pvm version)" >> $REPORT_FILE
echo "" >> $REPORT_FILE

# 获取已安装的PHP版本
echo "已安装的PHP版本:" >> $REPORT_FILE
pvm list >> $REPORT_FILE
echo "" >> $REPORT_FILE

# 运行PHP版本测试
echo -e "${YELLOW}正在运行PHP版本测试...${NC}"
echo "PHP版本测试:" >> $REPORT_FILE

# 获取已安装的PHP版本
INSTALLED_VERSIONS=$(pvm list | grep -E '^[0-9]+\.[0-9]+\.[0-9]+' | awk '{print $1}')

for version in $INSTALLED_VERSIONS; do
    echo -e "${YELLOW}测试PHP $version...${NC}"
    echo "测试PHP $version:" >> $REPORT_FILE
    
    # 切换到指定版本
    pvm use $version > /dev/null 2>&1
    
    # 获取PHP信息
    php_info=$(php -v | head -1)
    echo "  $php_info" >> $REPORT_FILE
    
    # 获取已安装的扩展
    echo "  已安装的扩展:" >> $REPORT_FILE
    php -m | sort >> $REPORT_FILE
    
    # 测试PHP功能
    echo "  功能测试:" >> $REPORT_FILE
    
    # 创建临时测试文件
    TEST_FILE=$(mktemp)
    
    cat > $TEST_FILE << 'EOF'
<?php
// 基本功能测试
$tests = [
    'JSON' => function() {
        return function_exists('json_encode') && function_exists('json_decode');
    },
    'cURL' => function() {
        return function_exists('curl_init') && function_exists('curl_exec');
    },
    'MySQLi' => function() {
        return class_exists('mysqli');
    },
    'PDO MySQL' => function() {
        return class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers());
    },
    'GD' => function() {
        return function_exists('imagecreate');
    },
    'Mbstring' => function() {
        return function_exists('mb_strlen');
    },
    'XML' => function() {
        return class_exists('SimpleXMLElement');
    },
    'Zip' => function() {
        return class_exists('ZipArchive');
    },
    'OPcache' => function() {
        return function_exists('opcache_get_status');
    },
    'Redis' => function() {
        return class_exists('Redis');
    },
    'Xdebug' => function() {
        return function_exists('xdebug_info') || function_exists('xdebug_get_code_coverage');
    },
    'MongoDB' => function() {
        return class_exists('MongoDB\Driver\Manager');
    }
];

foreach ($tests as $name => $test) {
    $result = $test() ? 'Pass' : 'Fail';
    echo "$name: $result\n";
}
EOF
    
    # 运行测试
    php $TEST_FILE >> $REPORT_FILE
    
    # 删除临时测试文件
    rm $TEST_FILE
    
    echo "" >> $REPORT_FILE
done

# 运行扩展测试
echo -e "${YELLOW}正在运行扩展测试...${NC}"
echo "扩展测试:" >> $REPORT_FILE

# 测试Redis扩展
if [ -f "redis_test.php" ]; then
    echo -e "${YELLOW}测试Redis扩展...${NC}"
    echo "Redis扩展测试:" >> $REPORT_FILE
    
    for version in $INSTALLED_VERSIONS; do
        echo -e "${YELLOW}使用PHP $version 测试Redis扩展...${NC}"
        echo "使用PHP $version 测试Redis扩展:" >> $REPORT_FILE
        
        # 切换到指定版本
        pvm use $version > /dev/null 2>&1
        
        # 检查Redis扩展是否已安装
        if php -m | grep -q "redis"; then
            # 运行Redis测试
            php redis_test.php >> $REPORT_FILE 2>&1
            
            if [ $? -eq 0 ]; then
                echo "  测试结果: 成功" >> $REPORT_FILE
            else
                echo "  测试结果: 失败" >> $REPORT_FILE
            fi
        else
            echo "  Redis扩展未安装" >> $REPORT_FILE
        fi
        
        echo "" >> $REPORT_FILE
    done
fi

# 测试Xdebug扩展
if [ -f "xdebug_test.php" ]; then
    echo -e "${YELLOW}测试Xdebug扩展...${NC}"
    echo "Xdebug扩展测试:" >> $REPORT_FILE
    
    for version in $INSTALLED_VERSIONS; do
        echo -e "${YELLOW}使用PHP $version 测试Xdebug扩展...${NC}"
        echo "使用PHP $version 测试Xdebug扩展:" >> $REPORT_FILE
        
        # 切换到指定版本
        pvm use $version > /dev/null 2>&1
        
        # 检查Xdebug扩展是否已安装
        if php -m | grep -q "xdebug"; then
            # 运行Xdebug测试
            php xdebug_test.php >> $REPORT_FILE 2>&1
            
            if [ $? -eq 0 ]; then
                echo "  测试结果: 成功" >> $REPORT_FILE
            else
                echo "  测试结果: 失败" >> $REPORT_FILE
            fi
        else
            echo "  Xdebug扩展未安装" >> $REPORT_FILE
        fi
        
        echo "" >> $REPORT_FILE
    done
fi

# 运行性能基准测试
echo -e "${YELLOW}正在运行性能基准测试...${NC}"
echo "性能基准测试:" >> $REPORT_FILE

# 创建临时基准测试文件
BENCHMARK_FILE=$(mktemp)

cat > $BENCHMARK_FILE << 'EOF'
<?php
// 基准测试函数
function benchmark($name, $iterations, $func) {
    echo "$name: ";
    
    $start = microtime(true);
    $func($iterations);
    $end = microtime(true);
    
    $duration = $end - $start;
    $opsPerSec = $iterations / $duration;
    
    echo sprintf("%.2f 秒, %.2f 操作/秒\n", $duration, $opsPerSec);
    
    return [
        'name' => $name,
        'duration' => $duration,
        'opsPerSec' => $opsPerSec
    ];
}

// 测试项目
$results = [];

// 1. 数学运算测试
$results[] = benchmark("数学运算", 1000000, function($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        $x = sin($i) * cos($i) * tan($i) * sqrt($i);
    }
});

// 2. 字符串操作测试
$results[] = benchmark("字符串操作", 100000, function($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        $str = "Hello, World! " . $i;
        $str = strtoupper($str);
        $str = str_replace("WORLD", "PHP", $str);
        $len = strlen($str);
        $pos = strpos($str, "PHP");
    }
});

// 3. 数组操作测试
$results[] = benchmark("数组操作", 10000, function($iterations) {
    for ($i = 0; $i < $iterations; $i++) {
        $arr = range(0, 1000);
        shuffle($arr);
        sort($arr);
        array_reverse($arr);
        array_map(function($x) { return $x * 2; }, $arr);
        array_filter($arr, function($x) { return $x % 2 == 0; });
    }
});

// 4. JSON操作测试
$results[] = benchmark("JSON操作", 10000, function($iterations) {
    $data = [
        'name' => 'PHP Benchmark',
        'version' => '1.0',
        'items' => range(1, 100),
        'config' => [
            'enabled' => true,
            'debug' => false,
            'options' => ['a', 'b', 'c']
        ]
    ];
    
    for ($i = 0; $i < $iterations; $i++) {
        $json = json_encode($data);
        $decoded = json_decode($json, true);
    }
});

// 5. 哈希计算测试
$results[] = benchmark("哈希计算", 10000, function($iterations) {
    $data = "PHP Benchmark Data " . rand(1000, 9999);
    for ($i = 0; $i < $iterations; $i++) {
        $md5 = md5($data . $i);
        $sha1 = sha1($data . $i);
        $hash = hash('sha256', $data . $i);
    }
});

echo "\nPHP版本: " . PHP_VERSION . "\n";
EOF

for version in $INSTALLED_VERSIONS; do
    echo -e "${YELLOW}使用PHP $version 运行性能基准测试...${NC}"
    echo "使用PHP $version 运行性能基准测试:" >> $REPORT_FILE
    
    # 切换到指定版本
    pvm use $version > /dev/null 2>&1
    
    # 运行基准测试
    php $BENCHMARK_FILE >> $REPORT_FILE
    
    echo "" >> $REPORT_FILE
done

# 删除临时基准测试文件
rm $BENCHMARK_FILE

# 生成测试报告摘要
echo -e "${YELLOW}正在生成测试报告摘要...${NC}"
echo "测试报告摘要:" >> $REPORT_FILE
echo "----------------------------------------" >> $REPORT_FILE
echo "已测试的PHP版本: $(echo $INSTALLED_VERSIONS | wc -w)" >> $REPORT_FILE
echo "测试完成时间: $(date)" >> $REPORT_FILE
echo "测试报告文件: $REPORT_FILE" >> $REPORT_FILE

echo -e "${GREEN}测试完成!${NC}"
echo -e "${GREEN}测试报告已保存到: $REPORT_FILE${NC}"
