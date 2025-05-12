<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\Extension\ExtensionType;
use VersionManager\Core\Extension\AbstractExtensionDriver;
use VersionManager\Core\Extension\GenericExtensionDriver;
use VersionManager\Core\Extension\ExtensionDriverFactory;

// 测试扩展类型枚举

echo "===== 测试扩展类型枚举 =====\n\n";

// 获取所有扩展类型
$types = ExtensionType::getAll();
echo "所有扩展类型:\n";
foreach ($types as $type) {
    echo "  * {$type}: " . ExtensionType::getDescription($type) . "\n";
}

echo "\n";

// 测试扩展类型验证
$validTypes = [
    ExtensionType::BUILTIN,
    ExtensionType::PECL,
    ExtensionType::SOURCE,
    ExtensionType::SYSTEM,
    ExtensionType::CUSTOM,
];

$invalidTypes = [
    'invalid',
    'unknown',
    '',
    null,
];

echo "扩展类型验证:\n";
foreach ($validTypes as $type) {
    $isValid = ExtensionType::isValid($type) ? '有效' : '无效';
    echo "  * {$type}: {$isValid}\n";
}

foreach ($invalidTypes as $type) {
    $isValid = ExtensionType::isValid($type) ? '有效' : '无效';
    echo "  * " . (is_null($type) ? 'null' : $type) . ": {$isValid}\n";
}

echo "\n";

// 测试扩展驱动类型
echo "扩展驱动类型:\n";

// 创建一个通用扩展驱动
$driver = new GenericExtensionDriver('test');
echo "  * 通用扩展驱动类型: " . $driver->getType() . " (" . $driver->getTypeDescription() . ")\n";

// 获取一些特定扩展的驱动
$extensions = ['mysqli', 'gd', 'redis', 'memcached'];
foreach ($extensions as $extension) {
    $driver = ExtensionDriverFactory::getDriver($extension);
    echo "  * {$extension}扩展驱动类型: " . $driver->getType() . " (" . $driver->getTypeDescription() . ")\n";
}

echo "\n";

// 测试无效类型的异常
echo "测试无效类型的异常:\n";
try {
    // 创建一个匿名类来测试无效类型
    $driver = new class('test', '', '', 'invalid') extends AbstractExtensionDriver {
        public function install($phpVersion, array $options = []) { return true; }
        public function remove($phpVersion, array $options = []) { return true; }
        public function enable($phpVersion, array $config = []) { return true; }
        public function disable($phpVersion) { return true; }
        public function configure($phpVersion, array $config) { return true; }
        public function isInstalled($phpVersion) { return true; }
        public function isAvailable($phpVersion) { return true; }
    };
    echo "  * 创建无效类型的驱动成功\n";
} catch (\InvalidArgumentException $e) {
    echo "  * 创建无效类型的驱动失败: " . $e->getMessage() . "\n";
}

echo "\n===== 测试完成 =====\n";
