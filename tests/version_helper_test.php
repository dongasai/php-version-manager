<?php

/**
 * 简单的版本助手类测试
 */

require_once __DIR__ . '/../src/Core/Version/Util/VersionHelper.php';

use VersionManager\Core\Version\Util\VersionHelper;

echo "测试版本助手类功能...\n";

// 测试版本格式验证
echo "测试版本格式验证:\n";
$validVersions = ['7.4.0', '8.1.15', '8.2.10'];
$invalidVersions = ['7.4', '8.1.x', 'invalid'];

foreach ($validVersions as $version) {
    $result = VersionHelper::isValidVersionFormat($version);
    echo "  {$version}: " . ($result ? '✓' : '✗') . "\n";
}

foreach ($invalidVersions as $version) {
    $result = VersionHelper::isValidVersionFormat($version);
    echo "  {$version}: " . ($result ? '✗' : '✓') . " (应该无效)\n";
}

// 测试版本支持检查
echo "\n测试版本支持检查:\n";
$testVersions = ['5.6.0', '7.0.0', '7.1.0', '8.2.0'];

foreach ($testVersions as $version) {
    $result = VersionHelper::isSupportedVersion($version);
    echo "  {$version}: " . ($result ? '支持' : '不支持') . "\n";
}

// 测试版本解析
echo "\n测试版本解析:\n";
$version = '8.1.15';
$parsed = VersionHelper::parseVersion($version);
echo "  版本 {$version}:\n";
echo "    主版本: {$parsed['major']}\n";
echo "    次版本: {$parsed['minor']}\n";
echo "    补丁版本: {$parsed['patch']}\n";
echo "    主要.次要: {$parsed['major_minor']}\n";

// 测试版本比较
echo "\n测试版本比较:\n";
$comparisons = [
    ['8.1.0', '8.0.0', '>'],
    ['7.4.0', '8.0.0', '<'],
    ['8.1.15', '8.1.15', '=='],
];

foreach ($comparisons as [$v1, $v2, $op]) {
    $result = VersionHelper::compareVersions($v1, $v2, $op);
    echo "  {$v1} {$op} {$v2}: " . ($result ? '✓' : '✗') . "\n";
}

// 测试PHP版本键生成
echo "\n测试PHP版本键生成:\n";
$versions = ['7.1.0', '7.4.33', '8.0.30', '8.2.10'];

foreach ($versions as $version) {
    $key = VersionHelper::getPhpVersionKey($version);
    echo "  {$version} -> {$key}\n";
}

echo "\n所有测试完成！\n";
