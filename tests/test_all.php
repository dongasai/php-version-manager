<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\VersionDetector;
use VersionManager\Core\VersionInstaller;
use VersionManager\Core\VersionSwitcher;
use VersionManager\Core\VersionRemover;

// 创建实例
$detector = new VersionDetector();
$installer = new VersionInstaller();
$switcher = new VersionSwitcher();
$remover = new VersionRemover();

echo "===== PHP Version Manager 综合测试 =====\n\n";

// 测试版本检测
echo "1. 测试版本检测功能\n";
echo "-------------------\n";

// 获取当前PHP版本
$currentVersion = $detector->getCurrentVersion();
echo "当前PHP版本: " . ($currentVersion ?: "未检测到") . "\n";

// 获取已安装的PHP版本
$installedVersions = $detector->getInstalledVersions();
echo "已安装的PHP版本:\n";
if (empty($installedVersions)) {
    echo "  没有通过PVM安装的PHP版本\n";
} else {
    foreach ($installedVersions as $version) {
        echo "  - $version\n";
    }
}

// 获取可用的PHP版本
$availableVersions = $detector->getAvailableVersions();
echo "可用的PHP版本:\n";
foreach ($availableVersions as $version) {
    $status = in_array($version, $installedVersions) ? '[已安装]' : '[可安装]';
    echo "  - $version $status\n";
}

echo "\n";

// 测试版本安装
echo "2. 测试版本安装功能\n";
echo "-------------------\n";

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
    } else {
        // 安装版本
        try {
            $installer->install($testVersion);
            echo "PHP版本 $testVersion 安装成功\n";
        } catch (Exception $e) {
            echo "安装失败: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n";

// 测试版本切换
echo "3. 测试版本切换功能\n";
echo "-------------------\n";

// 获取当前PHP版本
$currentVersion = $switcher->getCurrentVersion();
echo "当前PHP版本: " . ($currentVersion ?: "未设置") . "\n";

// 获取已安装的PHP版本
$installedVersions = $detector->getInstalledVersions();
if (!empty($installedVersions)) {
    // 选择一个不同于当前版本的版本进行切换
    $testVersion = null;
    foreach ($installedVersions as $version) {
        if ($version !== $currentVersion) {
            $testVersion = $version;
            break;
        }
    }
    
    if ($testVersion) {
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
        echo "没有可切换的PHP版本\n";
    }
} else {
    echo "没有已安装的PHP版本，无法测试切换功能\n";
}

echo "\n";

// 测试项目级别的版本切换
echo "4. 测试项目级别的版本切换\n";
echo "-----------------------\n";

// 创建一个测试项目目录
$testProjectDir = sys_get_temp_dir() . '/pvm_test_project';
if (!is_dir($testProjectDir)) {
    mkdir($testProjectDir, 0755, true);
}

// 获取已安装的PHP版本
$installedVersions = $detector->getInstalledVersions();
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

echo "\n";

// 测试版本删除
echo "5. 测试版本删除功能\n";
echo "-------------------\n";

// 获取已安装的PHP版本
$installedVersions = $detector->getInstalledVersions();
if (!empty($installedVersions)) {
    // 选择一个非当前版本的版本进行删除
    $currentVersion = $switcher->getCurrentVersion();
    $testVersion = null;
    foreach ($installedVersions as $version) {
        if ($version !== $currentVersion) {
            $testVersion = $version;
            break;
        }
    }
    
    if ($testVersion) {
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
        echo "没有可删除的PHP版本\n";
    }
} else {
    echo "没有已安装的PHP版本，无法测试删除功能\n";
}

echo "\n===== 测试完成 =====\n";
