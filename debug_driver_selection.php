#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

echo "Debugging driver selection...\n\n";

try {
    // 检测操作系统信息
    $osInfo = [
        'type' => 'linux',
        'distro' => 'ubuntu',
        'version' => '22.04'
    ];
    
    echo "Detected OS Info:\n";
    print_r($osInfo);
    
    // 扫描驱动目录
    $driversDir = 'src/Core/System/Drivers';
    $files = glob($driversDir . '/*.php');
    
    echo "\nAvailable driver files:\n";
    foreach ($files as $file) {
        echo "  " . basename($file) . "\n";
    }
    
    // 尝试创建Ubuntu驱动
    echo "\nTesting Ubuntu driver creation:\n";
    $ubuntuDriver = new \VersionManager\Core\System\Drivers\UbuntuDriver();
    echo "Ubuntu driver created successfully\n";
    echo "Ubuntu driver tags: " . implode(', ', $ubuntuDriver->getTags()) . "\n";
    
    // 测试工厂
    echo "\nTesting OsDriverFactory:\n";
    $factory = \VersionManager\Core\System\OsDriverFactory::getInstance();
    echo "Factory returned: " . get_class($factory) . "\n";
    echo "Factory driver tags: " . implode(', ', $factory->getTags()) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
