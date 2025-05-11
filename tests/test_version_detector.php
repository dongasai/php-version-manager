<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\VersionDetector;

// 创建版本检测器实例
$detector = new VersionDetector();

echo "===== PHP版本检测测试 =====\n\n";

// 测试获取当前PHP版本
$currentVersion = $detector->getCurrentVersion();
echo "当前PHP版本: " . ($currentVersion ?: "未检测到") . "\n\n";

// 测试获取已安装的PHP版本
echo "已安装的PHP版本:\n";
$installedVersions = $detector->getInstalledVersions();
if (empty($installedVersions)) {
    echo "  没有通过PVM安装的PHP版本\n";
} else {
    foreach ($installedVersions as $version) {
        echo "  - $version\n";
    }
}
echo "\n";

// 测试获取可用的PHP版本
echo "可用的PHP版本:\n";
$availableVersions = $detector->getAvailableVersions();
foreach ($availableVersions as $version) {
    $status = in_array($version, $installedVersions) ? '[已安装]' : '[可安装]';
    echo "  - $version $status\n";
}
echo "\n";

// 测试版本兼容性检查
$testVersions = ['7.4.33', '8.1.27', '8.3.5'];
echo "版本兼容性检查:\n";
foreach ($testVersions as $version) {
    $isCompatible = $detector->isVersionCompatible($version);
    echo "  PHP $version: " . ($isCompatible ? '兼容' : '不兼容') . "\n";
}
echo "\n";

// 测试依赖检查
echo "依赖检查:\n";
foreach ($testVersions as $version) {
    echo "  PHP $version 依赖:\n";
    $missingDependencies = $detector->checkDependencies($version);
    if (empty($missingDependencies)) {
        echo "    所有依赖已满足\n";
    } else {
        echo "    缺少以下依赖:\n";
        foreach ($missingDependencies as $dependency) {
            echo "    - $dependency\n";
        }
    }
}
echo "\n";

echo "===== 测试完成 =====\n";
