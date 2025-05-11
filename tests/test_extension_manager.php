<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\ExtensionManager;

// 测试扩展管理器

echo "===== 测试扩展管理器 =====\n\n";

// 创建扩展管理器实例
$manager = new ExtensionManager();

// 获取已安装的扩展列表
$installedExtensions = $manager->getInstalledExtensions();
echo "已安装的扩展列表:\n";
foreach ($installedExtensions as $name => $info) {
    $status = isset($info['enabled']) && $info['enabled'] ? '已启用' : '已禁用';
    $type = isset($info['type']) ? $info['type'] : '';
    $version = isset($info['version']) ? $info['version'] : '';

    echo "  * {$name}";
    if (!empty($version)) {
        echo " ({$version})";
    }
    echo " [{$status}]";
    if (!empty($type)) {
        echo " [{$type}]";
    }
    echo "\n";
}

echo "\n";

// 获取可用的扩展列表
$availableExtensions = $manager->getAvailableExtensions();
echo "可用的扩展列表:\n";
if (empty($availableExtensions)) {
    echo "  没有可用的扩展\n";
} else {
    foreach ($availableExtensions as $name => $info) {
        $description = isset($info['description']) ? $info['description'] : '';
        $version = isset($info['version']) ? $info['version'] : '';

        echo "  * {$name}";
        if (!empty($version)) {
            echo " ({$version})";
        }
        if (!empty($description)) {
            echo " - {$description}";
        }
        echo "\n";
    }
}

echo "\n";

// 测试获取特定扩展的信息
$extensions = ['gd', 'mysqli', 'redis'];
echo "特定扩展的信息:\n";
foreach ($extensions as $extension) {
    $info = $manager->getExtensionInfo($extension);
    $installed = $manager->isExtensionInstalled($extension) ? '已安装' : '未安装';

    echo "  * {$extension}: {$installed}\n";
    if ($info) {
        echo "    - 描述: " . (isset($info['description']) ? $info['description'] : '') . "\n";
        echo "    - 类型: " . (isset($info['type']) ? $info['type'] : '') . "\n";
        echo "    - 版本: " . (isset($info['version']) ? $info['version'] : '') . "\n";
    }
}

echo "\n===== 测试完成 =====\n";