<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\VersionSwitcher;
use VersionManager\Core\VersionDetector;
use VersionManager\Core\VersionInstaller;

// 创建版本切换器、检测器和安装器实例
$switcher = new VersionSwitcher();
$detector = new VersionDetector();
$installer = new VersionInstaller();

echo "===== PHP版本切换测试 =====\n\n";

// 获取当前PHP版本
$currentVersion = $switcher->getCurrentVersion();
echo "当前PHP版本: " . ($currentVersion ?: "未设置") . "\n\n";

// 获取全局PHP版本
$globalVersion = $switcher->getGlobalVersion();
echo "全局PHP版本: " . ($globalVersion ?: "未设置") . "\n\n";

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

// 选择一个版本进行切换
if (!empty($installedVersions)) {
    $testVersion = $installedVersions[0];
    echo "测试切换到PHP版本: $testVersion\n";
    
    try {
        $switcher->switchVersion($testVersion);
        echo "已切换到PHP版本 $testVersion\n";
        
        // 验证切换是否成功
        $newCurrentVersion = $switcher->getCurrentVersion();
        echo "当前PHP版本: $newCurrentVersion\n";
        
        if ($newCurrentVersion === $testVersion) {
            echo "版本切换成功!\n";
        } else {
            echo "版本切换失败!\n";
        }
    } catch (Exception $e) {
        echo "切换失败: " . $e->getMessage() . "\n";
    }
} else {
    echo "没有已安装的PHP版本，无法测试切换功能\n";
    
    // 安装一个测试版本
    $testVersion = '7.4.33';
    echo "尝试安装PHP版本 $testVersion 用于测试...\n";
    
    try {
        $installer->install($testVersion);
        echo "PHP版本 $testVersion 安装成功\n";
        
        // 切换到测试版本
        $switcher->switchVersion($testVersion);
        echo "已切换到PHP版本 $testVersion\n";
        
        // 验证切换是否成功
        $newCurrentVersion = $switcher->getCurrentVersion();
        echo "当前PHP版本: $newCurrentVersion\n";
        
        if ($newCurrentVersion === $testVersion) {
            echo "版本切换成功!\n";
        } else {
            echo "版本切换失败!\n";
        }
    } catch (Exception $e) {
        echo "安装或切换失败: " . $e->getMessage() . "\n";
    }
}

// 测试项目级别的版本切换
echo "\n测试项目级别的版本切换:\n";

// 创建一个测试项目目录
$testProjectDir = sys_get_temp_dir() . '/pvm_test_project';
if (!is_dir($testProjectDir)) {
    mkdir($testProjectDir, 0755, true);
}

// 选择一个版本设置为项目版本
if (!empty($installedVersions)) {
    $testVersion = $installedVersions[0];
    echo "设置项目 $testProjectDir 的PHP版本为 $testVersion\n";
    
    try {
        $switcher->setProjectVersion($testVersion, $testProjectDir);
        echo "已设置项目PHP版本\n";
        
        // 验证设置是否成功
        $projectVersion = $switcher->getProjectVersion($testProjectDir);
        echo "项目PHP版本: $projectVersion\n";
        
        if ($projectVersion === $testVersion) {
            echo "项目版本设置成功!\n";
        } else {
            echo "项目版本设置失败!\n";
        }
    } catch (Exception $e) {
        echo "设置失败: " . $e->getMessage() . "\n";
    }
} else {
    echo "没有已安装的PHP版本，无法测试项目级别的版本切换\n";
}

// 清理测试项目目录
if (is_dir($testProjectDir)) {
    rmdir($testProjectDir);
}

echo "\n===== 测试完成 =====\n";
