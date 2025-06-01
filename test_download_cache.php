<?php

/**
 * 下载缓存功能测试脚本
 * 
 * 用于测试下载缓存和完整性校验功能
 */

require_once __DIR__ . '/src/Core/Logger/FileLogger.php';
require_once __DIR__ . '/src/Core/Download/IntegrityVerifier.php';
require_once __DIR__ . '/src/Core/Cache/CacheManager.php';
require_once __DIR__ . '/src/Core/Config/DownloadCacheConfig.php';

use VersionManager\Core\Logger\FileLogger;
use VersionManager\Core\Download\IntegrityVerifier;
use VersionManager\Core\Cache\CacheManager;
use VersionManager\Core\Config\DownloadCacheConfig;

echo "=== PVM 下载缓存功能测试 ===\n\n";

// 初始化日志系统
FileLogger::init('test_download_cache', ['test']);

// 测试配置管理
echo "1. 测试配置管理...\n";
$config = new DownloadCacheConfig();
echo "缓存启用状态: " . ($config->isEnabled() ? '是' : '否') . "\n";
echo "缓存过期时间: " . $config->getCacheExpire() . " 秒\n";
echo "完整性校验启用: " . ($config->isIntegrityCheckEnabled() ? '是' : '否') . "\n";
echo "默认校验算法: " . implode(', ', $config->getDefaultAlgorithms()) . "\n\n";

// 测试完整性校验
echo "2. 测试完整性校验...\n";

// 创建测试文件
$testFile = sys_get_temp_dir() . '/pvm_test_file.txt';
$testContent = "这是一个测试文件，用于验证PVM的下载缓存和完整性校验功能。\n时间戳: " . date('Y-m-d H:i:s');
file_put_contents($testFile, $testContent);

echo "创建测试文件: {$testFile}\n";
echo "文件大小: " . filesize($testFile) . " 字节\n";

// 生成校验和
$checksums = IntegrityVerifier::generateChecksums($testFile, ['md5', 'sha256']);
echo "生成的校验和:\n";
foreach ($checksums as $algorithm => $hash) {
    echo "  {$algorithm}: {$hash}\n";
}

// 验证文件完整性
$verifyResult = IntegrityVerifier::verifyDownloadedFile($testFile, $checksums);
echo "完整性校验结果: " . ($verifyResult ? '通过' : '失败') . "\n\n";

// 测试缓存管理
echo "3. 测试缓存管理...\n";
$cacheManager = new CacheManager();

// 模拟URL
$testUrl = 'https://example.com/test-file.txt';

// 设置缓存
echo "设置缓存...\n";
$setCacheResult = $cacheManager->setDownloadCache($testUrl, $testFile, ['checksums' => $checksums]);
echo "设置缓存结果: " . ($setCacheResult ? '成功' : '失败') . "\n";

// 获取缓存
echo "获取缓存...\n";
$cachedFile = $cacheManager->getDownloadCache($testUrl, true);
if ($cachedFile) {
    echo "缓存命中: {$cachedFile}\n";
    echo "缓存文件大小: " . filesize($cachedFile) . " 字节\n";
    
    // 验证缓存文件内容
    $cachedContent = file_get_contents($cachedFile);
    $contentMatch = ($cachedContent === $testContent);
    echo "内容匹配: " . ($contentMatch ? '是' : '否') . "\n";
} else {
    echo "缓存未命中\n";
}

// 获取缓存统计
echo "\n4. 缓存统计信息...\n";
$stats = $cacheManager->getCacheStats();
echo "下载缓存统计:\n";
echo "  文件数量: {$stats['download']['count']}\n";
echo "  占用空间: " . formatSize($stats['download']['size']) . "\n";

// 测试缓存清理
echo "\n5. 测试缓存清理...\n";
$cleanupStats = $cacheManager->cleanExpiredDownloadCache();
echo "清理统计:\n";
echo "  检查文件数: {$cleanupStats['total_checked']}\n";
echo "  过期删除数: {$cleanupStats['expired_removed']}\n";
echo "  损坏删除数: {$cleanupStats['corrupted_removed']}\n";
echo "  释放空间: " . formatSize($cleanupStats['bytes_freed']) . "\n";

// 清理测试文件
echo "\n6. 清理测试文件...\n";
if (file_exists($testFile)) {
    unlink($testFile);
    echo "删除测试文件: {$testFile}\n";
}

// 删除测试缓存
$cacheManager->deleteDownloadCache($testUrl);
echo "删除测试缓存\n";

// 记录测试完成
FileLogger::logCommandEnd(0);

echo "\n=== 测试完成 ===\n";
echo "日志文件: " . FileLogger::getCurrentLogFile() . "\n";

/**
 * 格式化文件大小
 */
function formatSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, 2) . ' ' . $units[$pow];
}
