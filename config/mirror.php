<?php

/**
 * PVM 镜像内容配置文件
 *
 * 用于配置需要镜像的内容，包括PHP版本、扩展等
 */

return [
    // PHP 源码镜像配置
    'php' => [
        // 官方源
        'source' => 'https://www.php.net/distributions',

        // 需要镜像的版本范围
        'versions' => [
            '5.6' => ['5.6.0', '5.6.40'],
            '7.0' => ['7.0.0', '7.0.33'],
            '7.1' => ['7.1.0', '7.1.33'],
            '7.2' => ['7.2.0', '7.2.34'],
            '7.3' => ['7.3.0', '7.3.33'],
            '7.4' => ['7.4.0', '7.4.33'],
            '8.0' => ['8.0.0', '8.0.30'],
            '8.1' => ['8.1.0', '8.1.27'],
            '8.2' => ['8.2.0', '8.2.17'],
            '8.3' => ['8.3.0', '8.3.5'],
        ],

        // 文件名模式
        'pattern' => 'php-{version}.tar.gz',

        // 是否启用此镜像
        'enabled' => true,
    ],

    // PECL 扩展镜像配置
    'pecl' => [
        // 官方源
        'source' => 'https://pecl.php.net/get',

        // 需要镜像的扩展及版本
        'extensions' => [
            'redis' => ['5.3.7', '6.0.2'],
            'memcached' => ['3.1.5', '3.2.0'],
            'xdebug' => ['3.1.0', '3.2.2'],
            'mongodb' => ['1.10.0', '1.16.1'],
            'imagick' => ['3.7.0', '3.7.0'],
            'swoole' => ['4.8.13', '5.0.3'],
            'yaml' => ['2.2.2', '2.2.3'],
            'protobuf' => ['3.21.12', '3.25.1'],
            'grpc' => ['1.52.0', '1.58.0'],
            'igbinary' => ['3.2.7', '3.2.14'],
        ],

        // 文件名模式
        'pattern' => '{extension}-{version}.tgz',

        // 是否启用此镜像
        'enabled' => true,
    ],

    // 特定扩展的 GitHub 源码镜像配置
    'extensions' => [
        'redis' => [
            'source' => 'https://github.com/phpredis/phpredis/archive/refs/tags',
            'versions' => ['5.3.7', '6.0.2'],
            'pattern' => '{version}.tar.gz',
            'enabled' => true,
        ],
        'memcached' => [
            'source' => 'https://github.com/php-memcached-dev/php-memcached/archive/refs/tags',
            'versions' => ['3.1.5', '3.2.0'],
            'pattern' => 'v{version}.tar.gz',
            'enabled' => true,
        ],
        'xdebug' => [
            'source' => 'https://github.com/xdebug/xdebug/archive/refs/tags',
            'versions' => ['3.1.0', '3.2.2'],
            'pattern' => '{version}.tar.gz',
            'enabled' => true,
        ],
        'mongodb' => [
            'source' => 'https://github.com/mongodb/mongo-php-driver/archive/refs/tags',
            'versions' => ['1.10.0', '1.16.1'],
            'pattern' => '{version}.tar.gz',
            'enabled' => true,
        ],
        'imagick' => [
            'source' => 'https://github.com/Imagick/imagick/archive/refs/tags',
            'versions' => ['3.7.0'],
            'pattern' => '{version}.tar.gz',
            'enabled' => true,
        ],
        'swoole' => [
            'source' => 'https://github.com/swoole/swoole-src/archive/refs/tags',
            'versions' => ['v4.8.13', 'v5.0.3'],
            'pattern' => '{version}.tar.gz',
            'enabled' => true,
        ],
    ],

    // Composer 镜像配置
    'composer' => [
        // 官方源
        'source' => 'https://getcomposer.org/download',

        // 需要镜像的版本
        'versions' => ['2.2.21', '2.3.10', '2.4.4', '2.5.8', '2.6.5', '2.7.9', '2.8.9'],

        // 文件名模式
        'pattern' => '{version}/composer.phar',

        // 是否启用此镜像
        'enabled' => true,
    ],
];
