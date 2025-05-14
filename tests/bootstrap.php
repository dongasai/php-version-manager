<?php

// 自动加载
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

$loaded = false;
foreach ($autoloadPaths as $file) {
    if (file_exists($file)) {
        require $file;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    echo 'You need to set up the project dependencies using Composer:' . PHP_EOL . PHP_EOL;
    echo '    composer install' . PHP_EOL . PHP_EOL;
    exit(1);
}

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

// 设置测试环境
$pvmTestHome = getenv('PVM_HOME') ?: '/tmp/pvm_test';

// 确保测试目录存在
if (!is_dir($pvmTestHome)) {
    mkdir($pvmTestHome, 0755, true);
}

// 创建版本目录
$versionsDir = $pvmTestHome . '/versions';
if (!is_dir($versionsDir)) {
    mkdir($versionsDir, 0755, true);
}

// 创建配置目录
$configDir = $pvmTestHome . '/config';
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

// 设置环境变量
putenv("HOME=$pvmTestHome");

// 清理函数
register_shutdown_function(function() use ($pvmTestHome) {
    // 测试完成后清理测试目录
    // 注释掉以保留测试数据进行调试
    // system("rm -rf $pvmTestHome");
});
