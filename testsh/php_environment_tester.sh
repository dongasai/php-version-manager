#!/bin/bash
# PHP环境测试脚本
# 用于测试PHP环境和扩展功能

# 设置颜色
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 检查PHP是否已安装
check_php() {
    if ! command -v php &> /dev/null; then
        echo -e "${RED}错误: PHP未安装${NC}"
        exit 1
    fi
}

# 显示帮助信息
show_help() {
    echo -e "${BLUE}PHP环境测试脚本${NC}"
    echo "用法: $0 [命令] [参数]"
    echo ""
    echo "命令:"
    echo "  info                  显示PHP环境信息"
    echo "  test-extension <扩展名>  测试指定扩展的功能"
    echo "  test-all              测试所有已安装扩展的功能"
    echo "  benchmark             执行PHP性能基准测试"
    echo "  create-phpinfo        创建phpinfo页面"
    echo "  help                  显示帮助信息"
    echo ""
    echo "示例:"
    echo "  $0 info               显示PHP环境信息"
    echo "  $0 test-extension redis  测试Redis扩展"
    echo "  $0 test-all           测试所有已安装扩展"
    echo "  $0 benchmark          执行性能基准测试"
    echo "  $0 create-phpinfo     创建phpinfo页面"
}

# 显示PHP环境信息
show_php_info() {
    echo -e "${YELLOW}PHP版本信息:${NC}"
    php -v
    echo ""
    
    echo -e "${YELLOW}PHP配置信息:${NC}"
    php -i | grep "Configure Command"
    echo ""
    
    echo -e "${YELLOW}PHP扩展列表:${NC}"
    php -m
    echo ""
    
    echo -e "${YELLOW}PHP配置文件路径:${NC}"
    php -i | grep "Loaded Configuration File"
    echo ""
    
    echo -e "${YELLOW}PHP扩展目录:${NC}"
    php -i | grep "extension_dir"
    echo ""
    
    echo -e "${YELLOW}PHP内存限制:${NC}"
    php -i | grep "memory_limit"
    echo ""
    
    echo -e "${YELLOW}PHP最大执行时间:${NC}"
    php -i | grep "max_execution_time"
    echo ""
    
    echo -e "${YELLOW}PHP错误报告级别:${NC}"
    php -i | grep "error_reporting"
    echo ""
}

# 测试指定扩展的功能
test_extension() {
    local extension=$1
    
    if [ -z "$extension" ]; then
        echo -e "${RED}错误: 请指定要测试的PHP扩展${NC}"
        exit 1
    fi
    
    echo -e "${YELLOW}正在测试扩展 $extension...${NC}"
    
    # 检查扩展是否已安装
    if ! php -m | grep -i "$extension" &> /dev/null; then
        echo -e "${RED}错误: 扩展 $extension 未安装${NC}"
        exit 1
    fi
    
    # 根据扩展名创建测试代码
    local test_code=""
    
    case $extension in
        redis)
            test_code='
            $redis = new Redis();
            try {
                $redis->connect("127.0.0.1", 6379);
                $redis->set("test_key", "Hello from PHP Redis!");
                $value = $redis->get("test_key");
                echo "Redis测试成功: " . $value . "\n";
            } catch (Exception $e) {
                echo "Redis测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        mysqli)
            test_code='
            try {
                $mysqli = new mysqli("localhost", "root", "", "mysql");
                if ($mysqli->connect_error) {
                    throw new Exception($mysqli->connect_error);
                }
                $result = $mysqli->query("SELECT VERSION() as version");
                $row = $result->fetch_assoc();
                echo "MySQL测试成功: " . $row["version"] . "\n";
                $mysqli->close();
            } catch (Exception $e) {
                echo "MySQL测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        pdo_mysql)
            test_code='
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=mysql", "root", "");
                $stmt = $pdo->query("SELECT VERSION() as version");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "PDO MySQL测试成功: " . $row["version"] . "\n";
            } catch (PDOException $e) {
                echo "PDO MySQL测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        curl)
            test_code='
            try {
                $ch = curl_init("https://www.php.net");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                if ($response === false) {
                    throw new Exception(curl_error($ch));
                }
                curl_close($ch);
                echo "CURL测试成功: 成功获取PHP官网内容\n";
            } catch (Exception $e) {
                echo "CURL测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        gd)
            test_code='
            try {
                if (function_exists("gd_info")) {
                    $info = gd_info();
                    echo "GD测试成功: " . $info["GD Version"] . "\n";
                } else {
                    throw new Exception("GD函数不可用");
                }
            } catch (Exception $e) {
                echo "GD测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        imagick)
            test_code='
            try {
                if (class_exists("Imagick")) {
                    $imagick = new Imagick();
                    echo "ImageMagick测试成功: " . Imagick::getVersion()["versionString"] . "\n";
                } else {
                    throw new Exception("Imagick类不可用");
                }
            } catch (Exception $e) {
                echo "ImageMagick测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        xdebug)
            test_code='
            try {
                if (function_exists("xdebug_info")) {
                    echo "Xdebug测试成功: " . phpversion("xdebug") . "\n";
                } else {
                    throw new Exception("Xdebug函数不可用");
                }
            } catch (Exception $e) {
                echo "Xdebug测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        opcache)
            test_code='
            try {
                if (function_exists("opcache_get_status")) {
                    $status = opcache_get_status();
                    echo "OPcache测试成功: " . ($status ? "已启用" : "已禁用") . "\n";
                } else {
                    throw new Exception("OPcache函数不可用");
                }
            } catch (Exception $e) {
                echo "OPcache测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        mongodb)
            test_code='
            try {
                if (class_exists("MongoDB\Driver\Manager")) {
                    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
                    echo "MongoDB测试成功: 连接成功\n";
                } else {
                    throw new Exception("MongoDB类不可用");
                }
            } catch (Exception $e) {
                echo "MongoDB测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
        *)
            test_code='
            try {
                if (extension_loaded("'$extension'")) {
                    echo "'$extension'扩展测试成功: 已加载\n";
                } else {
                    throw new Exception("'$extension'扩展未加载");
                }
            } catch (Exception $e) {
                echo "'$extension'扩展测试失败: " . $e->getMessage() . "\n";
            }'
            ;;
    esac
    
    # 执行测试代码
    php -r "$test_code"
}

# 测试所有已安装扩展的功能
test_all_extensions() {
    echo -e "${YELLOW}正在测试所有已安装的扩展...${NC}"
    
    # 获取已安装的扩展列表
    local extensions=$(php -m | grep -v -E "^\[" | sort)
    
    for ext in $extensions; do
        echo -e "${YELLOW}测试扩展: $ext${NC}"
        test_extension $ext
        echo ""
    done
    
    echo -e "${GREEN}所有扩展测试完成${NC}"
}

# 执行PHP性能基准测试
run_benchmark() {
    echo -e "${YELLOW}正在执行PHP性能基准测试...${NC}"
    
    # 创建基准测试脚本
    local benchmark_script=$(mktemp)
    
    cat > $benchmark_script << 'EOF'
<?php
// 基准测试函数
function benchmark($name, $iterations, $func) {
    echo "运行 $name 测试 ($iterations 次迭代)...\n";
    
    $start = microtime(true);
    $func($iterations);
    $end = microtime(true);
    
    $duration = $end - $start;
    $opsPerSec = $iterations / $duration;
    
    echo sprintf("完成: %.2f 秒, %.2f 操作/秒\n\n", $duration, $opsPerSec);
    
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

// 4. 文件操作测试
$results[] = benchmark("文件操作", 1000, function($iterations) {
    $filename = tempnam(sys_get_temp_dir(), "benchmark");
    for ($i = 0; $i < $iterations; $i++) {
        file_put_contents($filename, "Line $i\n", FILE_APPEND);
        $content = file_get_contents($filename);
    }
    unlink($filename);
});

// 5. 正则表达式测试
$results[] = benchmark("正则表达式", 10000, function($iterations) {
    $text = "The quick brown fox jumps over the lazy dog. PHP is the best programming language!";
    for ($i = 0; $i < $iterations; $i++) {
        preg_match_all('/\b\w{3,}\b/', $text, $matches);
        preg_replace('/\b(\w+)\b/', '[$1]', $text);
    }
});

// 6. JSON操作测试
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

// 7. 哈希计算测试
$results[] = benchmark("哈希计算", 10000, function($iterations) {
    $data = "PHP Benchmark Data " . rand(1000, 9999);
    for ($i = 0; $i < $iterations; $i++) {
        $md5 = md5($data . $i);
        $sha1 = sha1($data . $i);
        $hash = hash('sha256', $data . $i);
    }
});

// 显示结果摘要
echo "\n性能基准测试结果摘要:\n";
echo str_repeat('-', 60) . "\n";
echo sprintf("%-20s %-15s %-15s\n", "测试项目", "耗时(秒)", "操作/秒");
echo str_repeat('-', 60) . "\n";

foreach ($results as $result) {
    echo sprintf("%-20s %-15.2f %-15.2f\n", 
        $result['name'], 
        $result['duration'], 
        $result['opsPerSec']
    );
}

echo str_repeat('-', 60) . "\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "操作系统: " . PHP_OS . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
EOF
    
    # 执行基准测试
    php $benchmark_script
    
    # 清理临时文件
    rm $benchmark_script
}

# 创建phpinfo页面
create_phpinfo() {
    local phpinfo_file="phpinfo.php"
    
    echo -e "${YELLOW}正在创建phpinfo页面...${NC}"
    
    # 创建phpinfo.php文件
    cat > $phpinfo_file << 'EOF'
<?php
// 显示PHP信息
phpinfo();
EOF
    
    echo -e "${GREEN}phpinfo页面已创建: $phpinfo_file${NC}"
    echo -e "${YELLOW}您可以通过Web服务器访问此文件查看详细的PHP信息${NC}"
    echo -e "${YELLOW}注意: 在生产环境中，请确保此文件不可公开访问，以防泄露敏感信息${NC}"
}

# 主函数
main() {
    # 检查PHP是否已安装
    check_php
    
    # 解析命令行参数
    local command=$1
    local param=$2
    
    case $command in
        info)
            show_php_info
            ;;
        test-extension)
            test_extension $param
            ;;
        test-all)
            test_all_extensions
            ;;
        benchmark)
            run_benchmark
            ;;
        create-phpinfo)
            create_phpinfo
            ;;
        help|*)
            show_help
            ;;
    esac
}

# 执行主函数
main "$@"
