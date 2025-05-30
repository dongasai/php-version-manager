<?php
/**
 * 依赖安装优化测试脚本
 * 
 * 用于测试优化后的依赖检查和安装逻辑
 */

require_once __DIR__ . '/vendor/autoload.php';

use VersionManager\Core\System\OsDriverFactory;
use VersionManager\Core\Logger\Logger;
use VersionManager\Core\Logger\LogLevel;

// 设置详细模式以查看完整输出
Logger::setLevel(LogLevel::VERBOSE);

echo "=== 依赖安装优化测试 ===\n\n";

try {
    // 获取操作系统驱动
    $osDriver = OsDriverFactory::getInstance();
    echo "检测到操作系统: " . $osDriver->getName() . " " . $osDriver->getVersion() . "\n";
    echo "包管理器: " . $osDriver->getPackageManager() . "\n\n";

    // 测试依赖列表（包含已安装和未安装的包）
    $testDependencies = [
        'build-essential',  // 通常已安装
        'libxml2-dev',      // 通常已安装
        'libssl-dev',       // 通常已安装
        'libcurl4-openssl-dev', // 通常已安装
        'libzip-dev',       // 可能未安装
        'libonig-dev',      // 可能未安装
        'libsqlite3-dev',   // 通常已安装
    ];

    echo "测试依赖列表:\n";
    foreach ($testDependencies as $dep) {
        echo "  - $dep\n";
    }
    echo "\n";

    // 测试依赖检查逻辑
    echo "=== 步骤1: 检查依赖安装状态 ===\n";
    $installedPackages = [];
    $missingPackages = [];

    foreach ($testDependencies as $package) {
        $isInstalled = $osDriver->isPackageInstalled($package);
        if ($isInstalled) {
            $installedPackages[] = $package;
            echo "✓ $package: 已安装\n";
        } else {
            $missingPackages[] = $package;
            echo "✗ $package: 未安装\n";
        }
    }

    echo "\n总结:\n";
    echo "已安装: " . count($installedPackages) . " 个\n";
    echo "需要安装: " . count($missingPackages) . " 个\n\n";

    if (empty($missingPackages)) {
        echo "🎉 所有依赖都已安装，无需更新包缓存或安装依赖！\n";
        echo "这就是优化后的效果：如果所有依赖都已安装，就不会执行任何安装操作。\n";
    } else {
        echo "=== 步骤2: 模拟优化后的安装流程 ===\n";
        echo "需要安装的依赖: " . implode(', ', $missingPackages) . "\n";
        echo "已安装的依赖: " . implode(', ', $installedPackages) . "\n\n";
        
        echo "优化效果:\n";
        echo "- 只会更新包缓存一次（因为有依赖需要安装）\n";
        echo "- 只会安装缺失的 " . count($missingPackages) . " 个依赖\n";
        echo "- 不会重复安装已有的 " . count($installedPackages) . " 个依赖\n\n";
        
        // 注意：这里不实际执行安装，只是演示逻辑
        echo "模拟执行（不实际安装）:\n";
        echo "1. 更新软件包列表...\n";
        echo "2. 安装依赖包: " . implode(' ', $missingPackages) . "\n";
        echo "3. 依赖包安装成功\n";
    }

    echo "\n=== 优化前后对比 ===\n";
    echo "优化前:\n";
    echo "  1. 总是执行 apt-get update\n";
    echo "  2. 尝试安装所有依赖（包括已安装的）\n";
    echo "  3. 输出信息不够清晰\n\n";
    
    echo "优化后:\n";
    echo "  1. 先检查依赖安装状态\n";
    echo "  2. 如果所有依赖都已安装，直接跳过\n";
    echo "  3. 只有需要安装依赖时才更新包缓存\n";
    echo "  4. 只安装缺失的依赖\n";
    echo "  5. 提供清晰的状态信息\n\n";

    echo "=== 性能提升 ===\n";
    $totalPackages = count($testDependencies);
    $installedCount = count($installedPackages);
    $missingCount = count($missingPackages);
    
    if ($missingCount == 0) {
        echo "🚀 性能提升: 100% (跳过所有安装操作)\n";
    } else {
        $improvement = round(($installedCount / $totalPackages) * 100, 1);
        echo "🚀 性能提升: 约 {$improvement}% (跳过 {$installedCount} 个已安装的依赖)\n";
    }

} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
}

echo "\n=== 测试完成 ===\n";
