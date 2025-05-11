<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\VersionRemover;
use VersionManager\Core\VersionDetector;
use VersionManager\Core\VersionInstaller;
use VersionManager\Core\VersionSwitcher;

// 创建版本删除器、检测器、安装器和切换器实例
$remover = new VersionRemover();
$detector = new VersionDetector();
$installer = new VersionInstaller();
$switcher = new VersionSwitcher();

echo "===== PHP版本删除测试 =====\n\n";

// 获取当前PHP版本
$currentVersion = $switcher->getCurrentVersion();
echo "当前PHP版本: " . ($currentVersion ?: "未设置") . "\n\n";

// 获取已安装的PHP版本
$installedVersions = $detector->getInstalledVersions();
echo "已安装的PHP版本:\n";
if (empty($installedVersions)) {
    echo "  没有通过PVM安装的PHP版本\n";
} else {
    foreach ($installedVersions as $version) {
        $status = ($version === $currentVersion) ? '[当前]' : '';
        echo "  - $version $status\n";
    }
}
echo "\n";

// 选择一个版本进行删除
if (!empty($installedVersions)) {
    // 选择一个非当前版本的版本进行删除
    $testVersion = null;
    foreach ($installedVersions as $version) {
        if ($version !== $currentVersion) {
            $testVersion = $version;
            break;
        }
    }
    
    // 如果没有非当前版本的版本，则安装一个测试版本
    if ($testVersion === null) {
        $testVersion = '7.4.33';
        echo "没有可删除的PHP版本，尝试安装PHP版本 $testVersion 用于测试...\n";
        
        try {
            $installer->install($testVersion);
            echo "PHP版本 $testVersion 安装成功\n";
        } catch (Exception $e) {
            echo "安装失败: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    echo "测试删除PHP版本: $testVersion\n";
    
    try {
        $remover->remove($testVersion);
        echo "PHP版本 $testVersion 删除成功\n";
        
        // 验证删除是否成功
        $newInstalledVersions = $detector->getInstalledVersions();
        if (!in_array($testVersion, $newInstalledVersions)) {
            echo "版本删除成功!\n";
        } else {
            echo "版本删除失败!\n";
        }
    } catch (Exception $e) {
        echo "删除失败: " . $e->getMessage() . "\n";
    }
} else {
    echo "没有已安装的PHP版本，无法测试删除功能\n";
    
    // 安装两个测试版本
    $testVersions = ['7.4.33', '8.1.27'];
    echo "尝试安装PHP版本用于测试...\n";
    
    foreach ($testVersions as $version) {
        try {
            $installer->install($version);
            echo "PHP版本 $version 安装成功\n";
        } catch (Exception $e) {
            echo "安装失败: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    // 切换到第一个版本
    try {
        $switcher->switchVersion($testVersions[0]);
        echo "已切换到PHP版本 {$testVersions[0]}\n";
    } catch (Exception $e) {
        echo "切换失败: " . $e->getMessage() . "\n";
    }
    
    // 删除第二个版本
    try {
        $remover->remove($testVersions[1]);
        echo "PHP版本 {$testVersions[1]} 删除成功\n";
        
        // 验证删除是否成功
        $newInstalledVersions = $detector->getInstalledVersions();
        if (!in_array($testVersions[1], $newInstalledVersions)) {
            echo "版本删除成功!\n";
        } else {
            echo "版本删除失败!\n";
        }
    } catch (Exception $e) {
        echo "删除失败: " . $e->getMessage() . "\n";
    }
}

echo "\n===== 测试完成 =====\n";
