<?php

// 自动加载
require_once __DIR__ . '/../../vendor/autoload.php';

// 添加测试命名空间自动加载
spl_autoload_register(function ($class) {
    // 检查是否是测试命名空间
    if (strpos($class, 'Tests\\') === 0) {
        $file = __DIR__ . '/../../' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});

use Tests\Console\Command\VersionCommandTest;
use Tests\Console\Command\InstallCommandTest;
use Tests\Console\Command\ExtensionCommandTest;

// 设置颜色
$GREEN = "\033[32m";
$RED = "\033[31m";
$YELLOW = "\033[33m";
$BLUE = "\033[34m";
$NC = "\033[0m"; // No Color

echo "{$BLUE}===== PVM 命令测试 ====={$NC}\n\n";

// 测试环境信息
echo "{$BLUE}测试环境信息:{$NC}\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "操作系统: " . php_uname('s') . ' ' . php_uname('r') . "\n";
echo "当前目录: " . getcwd() . "\n\n";

// 运行测试
$testClasses = [
    'VersionCommand' => VersionCommandTest::class,
    'InstallCommand' => InstallCommandTest::class,
    'ExtensionCommand' => ExtensionCommandTest::class,
];

$results = [];
$totalTests = 0;
$passedTests = 0;

foreach ($testClasses as $name => $class) {
    echo "{$BLUE}测试 {$name}...{$NC}\n";
    
    $test = new $class();
    $methods = get_class_methods($test);
    
    // 过滤出测试方法
    $testMethods = array_filter($methods, function($method) {
        return strpos($method, 'test') === 0;
    });
    
    foreach ($testMethods as $method) {
        $totalTests++;
        echo "  运行 {$method}... ";
        
        try {
            $test->setUp();
            $test->$method();
            $test->tearDown();
            
            echo "{$GREEN}通过{$NC}\n";
            $passedTests++;
            $results[$name][$method] = true;
        } catch (\Exception $e) {
            echo "{$RED}失败: {$e->getMessage()}{$NC}\n";
            $results[$name][$method] = false;
        }
    }
    
    echo "\n";
}

// 显示测试结果摘要
echo "{$BLUE}测试结果摘要:{$NC}\n";
echo "总测试数: {$totalTests}\n";
echo "通过测试数: {$passedTests}\n";
echo "失败测试数: " . ($totalTests - $passedTests) . "\n\n";

// 显示详细测试结果
echo "{$BLUE}详细测试结果:{$NC}\n";
foreach ($results as $className => $classResults) {
    $classPassedTests = count(array_filter($classResults));
    $classTotalTests = count($classResults);
    
    echo "{$className}: {$classPassedTests}/{$classTotalTests} ";
    
    if ($classPassedTests === $classTotalTests) {
        echo "{$GREEN}全部通过{$NC}\n";
    } else {
        echo "{$RED}部分失败{$NC}\n";
        
        // 显示失败的测试
        foreach ($classResults as $method => $passed) {
            if (!$passed) {
                echo "  - {$method}: {$RED}失败{$NC}\n";
            }
        }
    }
}

echo "\n{$BLUE}===== 测试完成 ====={$NC}\n";
