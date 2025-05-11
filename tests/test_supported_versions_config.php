<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\SupportedVersions;

// 创建支持的版本管理器实例
$supportedVersions = new SupportedVersions();

echo "===== PHP版本支持配置测试 =====\n\n";

// 获取系统信息
$arch = $supportedVersions->getSystemArchitecture();
list($distro, $version) = $supportedVersions->getSystemDistribution();

echo "系统信息:\n";
echo "  架构: {$arch}\n";
echo "  发行版: {$distro}\n";
echo "  版本: {$version}\n\n";

// 获取当前系统支持的PHP版本
$supportedVersionsList = $supportedVersions->getSupportedVersionsForCurrentSystem();

echo "当前系统支持的PHP版本:\n";
foreach ($supportedVersionsList as $phpVersion => $supportLevel) {
    $levelDesc = $supportedVersions->getSupportLevelDescription($supportLevel);
    $recommended = $supportedVersions->isRecommended($phpVersion) ? '(推荐)' : '';
    
    echo "  PHP {$phpVersion}.x: {$levelDesc} {$recommended}\n";
    
    // 显示已知问题
    $knownIssues = $supportedVersions->getKnownIssues($phpVersion);
    if (!empty($knownIssues)) {
        echo "    已知问题:\n";
        foreach ($knownIssues as $issue) {
            echo "    - {$issue}\n";
        }
    }
}

echo "\n";

// 测试特定版本的支持级别
$testVersions = ['7.1.33', '7.2.34', '7.3.33', '7.4.33', '8.0.30', '8.1.27', '8.2.17', '8.3.5'];

echo "特定版本的支持级别:\n";
foreach ($testVersions as $version) {
    $supportLevel = $supportedVersions->getSupportLevel($version);
    $levelDesc = $supportedVersions->getSupportLevelDescription($supportLevel);
    $recommended = $supportedVersions->isRecommended($version) ? '(推荐)' : '';
    
    echo "  PHP {$version}: {$levelDesc} {$recommended}\n";
}

echo "\n===== 测试完成 =====\n";
