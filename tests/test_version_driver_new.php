<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\Version\VersionDriverFactory;

// 测试版本安装驱动适配模式

echo "===== 测试版本安装驱动适配模式 =====\n\n";

// 获取系统信息
$osInfo = getOsInfo();
echo "系统信息:\n";
echo "  发行版: {$osInfo['type']}\n";
echo "  版本: {$osInfo['version']}\n";
echo "  架构: {$osInfo['arch']}\n\n";

// 测试不同的PHP版本
$phpVersions = ['7.1.33', '7.4.30', '8.0.20', '8.2.5'];

// 测试自动检测
$driver = VersionDriverFactory::getDriver();
echo "自动检测驱动: " . get_class($driver) . "\n\n";

// 测试特定PHP版本
foreach ($phpVersions as $phpVersion) {
    $driver = VersionDriverFactory::getDriver($phpVersion);
    echo "PHP {$phpVersion} 驱动: " . get_class($driver) . "\n";

    // 测试特定PHP版本和发行版
    $driver = VersionDriverFactory::getDriver($phpVersion, 'debian');
    echo "PHP {$phpVersion} debian 驱动: " . get_class($driver) . "\n";

    // 测试特定PHP版本、发行版和架构
    $driver = VersionDriverFactory::getDriver($phpVersion, 'debian', null, 'x86_64');
    echo "PHP {$phpVersion} debian x86_64 驱动: " . get_class($driver) . "\n";

    // 测试特定PHP版本和ubuntu发行版
    $driver = VersionDriverFactory::getDriver($phpVersion, 'ubuntu');
    echo "PHP {$phpVersion} ubuntu 驱动: " . get_class($driver) . "\n";

    // 测试特定PHP版本、ubuntu发行版和架构
    $driver = VersionDriverFactory::getDriver($phpVersion, 'ubuntu', null, 'x86_64');
    echo "PHP {$phpVersion} ubuntu x86_64 驱动: " . get_class($driver) . "\n";

    // 测试特定PHP版本、ubuntu发行版、版本和架构
    $driver = VersionDriverFactory::getDriver($phpVersion, 'ubuntu', '22.04', 'x86_64');
    echo "PHP {$phpVersion} ubuntu 22.04 x86_64 驱动: " . get_class($driver) . "\n";

    echo "\n";
}

echo "===== 测试完成 =====\n";

/**
 * 获取操作系统信息
 *
 * @return array [type => 类型, version => 版本, arch => 架构]
 */
function getOsInfo()
{
    $type = '';
    $version = '';
    $arch = php_uname('m');

    // 读取/etc/os-release文件
    if (file_exists('/etc/os-release')) {
        $osRelease = parse_ini_file('/etc/os-release');

        if (isset($osRelease['ID'])) {
            $type = strtolower($osRelease['ID']);
        }

        if (isset($osRelease['VERSION_ID'])) {
            $version = $osRelease['VERSION_ID'];
        }
    }

    return [
        'type' => $type,
        'version' => $version,
        'arch' => $arch,
    ];
}
