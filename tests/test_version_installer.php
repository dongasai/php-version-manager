<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\VersionInstaller;
use VersionManager\Core\VersionDetector;

// 创建版本安装器和检测器实例
$installer = new VersionInstaller();
$detector = new VersionDetector();

echo "===== PHP版本安装测试 =====\n\n";

// 获取可用的PHP版本
$availableVersions = $detector->getAvailableVersions();
echo "可用的PHP版本:\n";
foreach ($availableVersions as $version) {
    $status = $installer->isVersionInstalled($version) ? '[已安装]' : '[可安装]';
    echo "  - $version $status\n";
}
echo "\n";

// 选择一个版本进行安装
$testVersion = '7.4.33';
echo "测试安装PHP版本: $testVersion\n";

// 检查版本是否已安装
if ($installer->isVersionInstalled($testVersion)) {
    echo "PHP版本 $testVersion 已安装\n";
} else {
    // 检查版本兼容性
    $isCompatible = $detector->isVersionCompatible($testVersion);
    if (!$isCompatible) {
        echo "PHP版本 $testVersion 与当前系统不兼容\n";
        exit(1);
    }
    
    // 检查依赖
    $missingDependencies = $detector->checkDependencies($testVersion);
    if (!empty($missingDependencies)) {
        echo "缺少以下依赖:\n";
        foreach ($missingDependencies as $dependency) {
            echo "  - $dependency\n";
        }
        echo "\n";
    }
    
    // 安装版本
    echo "开始安装PHP版本 $testVersion...\n";
    try {
        $installer->install($testVersion, ['from_source' => true]);
        echo "PHP版本 $testVersion 安装成功\n";
    } catch (Exception $e) {
        echo "安装失败: " . $e->getMessage() . "\n";
    }
}

echo "\n===== 测试完成 =====\n";
