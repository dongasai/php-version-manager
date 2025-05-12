<?php

// 引入自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\Config\MirrorConfig;

// 测试镜像配置

echo "===== 测试镜像配置 =====\n\n";

// 创建镜像配置实例
$config = new MirrorConfig();

// 获取PHP镜像
echo "PHP镜像:\n";
$defaultPhpMirror = $config->getDefaultPhpMirrorName();
$phpMirrors = $config->getAllPhpMirrors();

foreach ($phpMirrors as $name => $url) {
    $default = ($name === $defaultPhpMirror) ? ' [默认]' : '';
    echo "  * {$name}: {$url}{$default}\n";
}

echo "\n";

// 获取PECL镜像
echo "PECL镜像:\n";
$defaultPeclMirror = $config->getDefaultPeclMirrorName();
$peclMirrors = $config->getAllPeclMirrors();

foreach ($peclMirrors as $name => $url) {
    $default = ($name === $defaultPeclMirror) ? ' [默认]' : '';
    echo "  * {$name}: {$url}{$default}\n";
}

echo "\n";

// 获取扩展镜像
echo "扩展镜像:\n";
$extensions = ['redis', 'memcached', 'xdebug'];

foreach ($extensions as $extension) {
    $defaultExtensionMirror = $config->getDefaultExtensionMirrorName($extension);
    $extensionMirrors = $config->getAllExtensionMirrors($extension);
    
    echo "  {$extension}:\n";
    foreach ($extensionMirrors as $name => $url) {
        $default = ($name === $defaultExtensionMirror) ? ' [默认]' : '';
        echo "    * {$name}: {$url}{$default}\n";
    }
}

echo "\n";

// 获取Composer镜像
echo "Composer镜像:\n";
$defaultComposerMirror = $config->getDefaultComposerMirrorName();
$composerMirrors = $config->getAllComposerMirrors();

foreach ($composerMirrors as $name => $url) {
    $default = ($name === $defaultComposerMirror) ? ' [默认]' : '';
    echo "  * {$name}: {$url}{$default}\n";
}

echo "\n";

// 测试添加镜像
echo "测试添加镜像:\n";

// 添加PHP镜像
$config->addPhpMirror('test', 'https://test.com/php');
echo "  * 添加PHP镜像 test: https://test.com/php\n";

// 添加PECL镜像
$config->addPeclMirror('test', 'https://test.com/pecl');
echo "  * 添加PECL镜像 test: https://test.com/pecl\n";

// 添加扩展镜像
$config->addExtensionMirror('redis', 'test', 'https://test.com/redis');
echo "  * 添加redis扩展镜像 test: https://test.com/redis\n";

// 添加Composer镜像
$config->addComposerMirror('test', 'https://test.com/composer');
echo "  * 添加Composer镜像 test: https://test.com/composer\n";

echo "\n";

// 测试设置默认镜像
echo "测试设置默认镜像:\n";

// 设置默认PHP镜像
$config->setDefaultPhpMirror('test');
echo "  * 设置默认PHP镜像为 test\n";

// 设置默认PECL镜像
$config->setDefaultPeclMirror('test');
echo "  * 设置默认PECL镜像为 test\n";

// 设置默认扩展镜像
$config->setDefaultExtensionMirror('redis', 'test');
echo "  * 设置默认redis扩展镜像为 test\n";

// 设置默认Composer镜像
$config->setDefaultComposerMirror('test');
echo "  * 设置默认Composer镜像为 test\n";

echo "\n";

// 测试删除镜像
echo "测试删除镜像:\n";

// 删除PHP镜像
$config->removePhpMirror('test');
echo "  * 删除PHP镜像 test\n";

// 删除PECL镜像
$config->removePeclMirror('test');
echo "  * 删除PECL镜像 test\n";

// 删除扩展镜像
$config->removeExtensionMirror('redis', 'test');
echo "  * 删除redis扩展镜像 test\n";

// 删除Composer镜像
$config->removeComposerMirror('test');
echo "  * 删除Composer镜像 test\n";

echo "\n";

// 测试获取镜像URL
echo "测试获取镜像URL:\n";

// 获取PHP镜像URL
$phpMirrorUrl = $config->getPhpMirror();
echo "  * 默认PHP镜像URL: {$phpMirrorUrl}\n";

// 获取PECL镜像URL
$peclMirrorUrl = $config->getPeclMirror();
echo "  * 默认PECL镜像URL: {$peclMirrorUrl}\n";

// 获取扩展镜像URL
$redisMirrorUrl = $config->getExtensionMirror('redis');
echo "  * 默认redis扩展镜像URL: {$redisMirrorUrl}\n";

// 获取Composer镜像URL
$composerMirrorUrl = $config->getComposerMirror();
echo "  * 默认Composer镜像URL: {$composerMirrorUrl}\n";

echo "\n";

// 测试获取特定镜像URL
echo "测试获取特定镜像URL:\n";

// 获取特定PHP镜像URL
$phpMirrorUrl = $config->getPhpMirror('aliyun');
echo "  * aliyun PHP镜像URL: {$phpMirrorUrl}\n";

// 获取特定PECL镜像URL
$peclMirrorUrl = $config->getPeclMirror('ustc');
echo "  * ustc PECL镜像URL: {$peclMirrorUrl}\n";

// 获取特定扩展镜像URL
$redisMirrorUrl = $config->getExtensionMirror('redis', 'github');
echo "  * github redis扩展镜像URL: {$redisMirrorUrl}\n";

// 获取特定Composer镜像URL
$composerMirrorUrl = $config->getComposerMirror('aliyun');
echo "  * aliyun Composer镜像URL: {$composerMirrorUrl}\n";

echo "\n===== 测试完成 =====\n";
