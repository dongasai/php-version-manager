<?php

// 自动加载
require_once __DIR__ . '/../vendor/autoload.php';

// 添加测试命名空间自动加载
spl_autoload_register(function ($class) {
    // 检查是否是测试命名空间
    if (strpos($class, 'Tests\\') === 0) {
        $file = __DIR__ . '/../' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});

use Tests\Console\Command\TestVersionCommand;

// 创建测试实例
$test = new TestVersionCommand();
// 手动调用setUp方法
$setupMethod = new ReflectionMethod(TestVersionCommand::class, 'setUp');
$setupMethod->setAccessible(true);
$setupMethod->invoke($test);

// 运行测试方法
echo "===== PHP Version Manager 测试 =====\n\n";

echo "测试环境信息:\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "操作系统: " . php_uname('s') . ' ' . php_uname('r') . "\n";
echo "当前目录: " . getcwd() . "\n\n";

// 运行测试方法
try {
    echo "1. 测试版本检测功能\n";
    echo "-------------------\n";
    $test->testVersionDetection();
    echo "版本检测测试通过!\n\n";
} catch (Exception $e) {
    echo "版本检测测试失败: " . $e->getMessage() . "\n\n";
}

echo "2. 测试版本安装功能 (已跳过)\n";
echo "-------------------\n";
echo "跳过版本安装测试，因为从源码安装PHP需要很长时间\n\n";

echo "3. 测试版本切换功能 (已跳过)\n";
echo "-------------------\n";
echo "跳过版本切换测试，因为它依赖于已安装的PHP版本\n\n";

try {
    echo "4. 测试PHP命令执行\n";
    echo "-------------------\n";
    $test->testExecutePhpCommand();
    echo "PHP命令执行测试通过!\n\n";
} catch (Exception $e) {
    echo "PHP命令执行测试失败: " . $e->getMessage() . "\n\n";
}

echo "===== 测试完成 =====\n";
