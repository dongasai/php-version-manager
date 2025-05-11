<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\Extension\ExtensionDriverFactory;

// 测试扩展驱动适配器

echo "===== 测试扩展驱动适配器 =====\n\n";

// 获取系统信息
$osInfo = getOsInfo();
echo "系统信息:\n";
echo "  发行版: {$osInfo['type']}\n";
echo "  版本: {$osInfo['version']}\n";
echo "  架构: " . php_uname('m') . "\n\n";

// 测试不同的扩展驱动
$extensions = ['gd', 'mysqli', 'redis'];
$distros = ['ubuntu', 'debian', 'centos', 'alpine'];
$arches = ['x86_64', 'aarch64'];

foreach ($extensions as $extension) {
    echo "扩展: {$extension}\n";
    
    // 测试通用驱动
    $driver = ExtensionDriverFactory::getDriver($extension);
    echo "  通用驱动: " . get_class($driver) . "\n";
    
    // 测试特定发行版的驱动
    foreach ($distros as $distro) {
        $driver = ExtensionDriverFactory::getDriver($extension, $distro);
        echo "  {$distro}驱动: " . get_class($driver) . "\n";
        
        // 测试特定发行版和架构的驱动
        foreach ($arches as $arch) {
            $driver = ExtensionDriverFactory::getDriver($extension, $distro, $arch);
            echo "  {$distro} {$arch}驱动: " . get_class($driver) . "\n";
        }
    }
    
    echo "\n";
}

echo "===== 测试完成 =====\n";

/**
 * 获取操作系统信息
 *
 * @return array [type => 类型, version => 版本]
 */
function getOsInfo()
{
    $type = '';
    $version = '';
    
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
    ];
}
