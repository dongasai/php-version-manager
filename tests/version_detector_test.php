<?php

// 自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\VersionDetector;

// 创建版本检测器实例
$detector = new VersionDetector();

// 测试获取当前PHP版本
echo "当前PHP版本: " . $detector->getCurrentVersion() . PHP_EOL;

// 测试获取已安装的PHP版本
echo "已安装的PHP版本: " . PHP_EOL;
$installedVersions = $detector->getInstalledVersions();
if (empty($installedVersions)) {
    echo "  没有通过PVM安装的PHP版本" . PHP_EOL;
} else {
    foreach ($installedVersions as $version) {
        echo "  - $version" . PHP_EOL;
    }
}

// 测试获取可用的PHP版本
echo "可用的PHP版本: " . PHP_EOL;
$availableVersions = $detector->getAvailableVersions();
foreach ($availableVersions as $version) {
    echo "  - $version" . PHP_EOL;
}

// 测试版本兼容性检查
$testVersion = '8.1.27';
echo "PHP $testVersion 与当前系统兼容性: " . 
    ($detector->isVersionCompatible($testVersion) ? '兼容' : '不兼容') . PHP_EOL;

// 测试依赖检查
echo "PHP $testVersion 依赖检查: " . PHP_EOL;
$missingDependencies = $detector->checkDependencies($testVersion);
if (empty($missingDependencies)) {
    echo "  所有依赖已满足" . PHP_EOL;
} else {
    echo "  缺少以下依赖: " . PHP_EOL;
    foreach ($missingDependencies as $dependency) {
        echo "  - $dependency" . PHP_EOL;
    }
}
