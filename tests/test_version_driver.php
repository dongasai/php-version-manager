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

// 测试不同的驱动
$distros = ['ubuntu', 'debian', 'centos', 'alpine'];
$distroVersions = ['18.04', '20.04', '22.04'];
$arches = ['x86_64', 'aarch64'];

// 测试自动检测
$driver = VersionDriverFactory::getDriver();
echo "自动检测驱动: " . get_class($driver) . "\n\n";

// 测试特定发行版
foreach ($distros as $distro) {
    $driver = VersionDriverFactory::getDriver($distro);
    echo "{$distro}驱动: " . get_class($driver) . "\n";
    
    // 测试特定发行版和版本
    foreach ($distroVersions as $distroVersion) {
        $driver = VersionDriverFactory::getDriver($distro, $distroVersion);
        echo "{$distro} {$distroVersion}驱动: " . get_class($driver) . "\n";
        
        // 测试特定发行版、版本和架构
        foreach ($arches as $arch) {
            $driver = VersionDriverFactory::getDriver($distro, $distroVersion, $arch);
            echo "{$distro} {$distroVersion} {$arch}驱动: " . get_class($driver) . "\n";
        }
    }
    
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
