<?php

/**
 * PVM 镜像内容配置文件
 *
 * 用于配置需要镜像的内容，包括PHP版本、扩展等
 * 版本信息已移至独立的配置文件中
 */

return [
    // 版本发现配置
    'discovery' => [
        // 是否启用自动版本发现
        'enabled' => true,

        // API 调用超时时间（秒）
        'timeout' => 30,

        // 缓存时间（秒），0表示不缓存
        'cache_ttl' => 3600,

        // 是否使用配置文件作为版本发现的补充
        'use_config_fallback' => true,

        // 是否只获取稳定版本
        'stable_only' => true,
    ],

    // PHP 源码镜像配置
    'php' => [
        // 官方源
        'source' => 'https://www.php.net/distributions',

        // 版本发现 API
        'discovery_api' => 'https://www.php.net/releases/index.php?json=1',

        // 文件名模式
        'pattern' => 'php-{version}.tar.gz',

        // 版本配置文件路径
        'version_config' => 'extensions/php/versions.php',

        // 是否启用此镜像
        'enabled' => true,
    ],
    // PECL 扩展镜像配置
    'pecl' => [
        // 官方源
        'source' => 'https://pecl.php.net/get',

        // 文件名模式
        'pattern' => '{extension}-{version}.tgz',

        // 扩展配置目录
        'config_dir' => 'extensions/pecl',

        // 支持的扩展列表
        'extensions' => [
            'redis', 'memcached', 'xdebug', 'mongodb',
            'imagick', 'swoole', 'yaml', 'protobuf',
            'grpc', 'igbinary'
        ],

        // 是否启用此镜像
        'enabled' => true,
    ],
    // GitHub 扩展镜像配置
    'extensions' => [
        // 扩展配置目录
        'config_dir' => 'extensions/github',

        // 支持的扩展列表
        'extensions' => [
            'redis', 'memcached', 'xdebug', 'mongodb',
            'imagick', 'swoole'
        ],

        // 是否启用此镜像
        'enabled' => true,
    ],
    // Composer 镜像配置
    'composer' => [
        // 官方源
        'source' => 'https://getcomposer.org/download',

        // 文件名模式
        'pattern' => 'composer-{version}.phar',

        // 版本配置文件路径
        'version_config' => 'composer/versions.php',

        // 是否启用此镜像
        'enabled' => true,
    ],
];
