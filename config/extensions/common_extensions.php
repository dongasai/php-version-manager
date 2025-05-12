<?php

/**
 * 常用PHP扩展配置
 *
 * 返回格式：[扩展名] = [配置信息]
 */
return [
    // 数据库相关扩展
    'mysqli' => [
        'name' => 'mysqli',
        'description' => 'MySQL Improved Extension',
        'type' => 'builtin',
        'dependencies' => [],
        'config' => [
            'default_socket' => '/tmp/mysql.sock',
            'default_host' => 'localhost',
            'default_user' => 'root',
            'default_pw' => '',
            'default_port' => '3306',
            'default_charset' => 'utf8mb4',
        ],
    ],
    'pdo_mysql' => [
        'name' => 'pdo_mysql',
        'description' => 'PDO MySQL Extension',
        'type' => 'builtin',
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'pdo' => [
        'name' => 'pdo',
        'description' => 'PHP Data Objects',
        'type' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'pdo_sqlite' => [
        'name' => 'pdo_sqlite',
        'description' => 'PDO SQLite Extension',
        'type' => 'builtin',
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'pdo_pgsql' => [
        'name' => 'pdo_pgsql',
        'description' => 'PDO PostgreSQL Extension',
        'type' => 'builtin',
        'dependencies' => ['pdo'],
        'config' => [],
    ],

    // 图像处理相关扩展
    'gd' => [
        'name' => 'gd',
        'description' => 'GD Graphics Library',
        'type' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'exif' => [
        'name' => 'exif',
        'description' => 'Exchangeable image information',
        'type' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'imagick' => [
        'name' => 'imagick',
        'description' => 'ImageMagick Image Processing',
        'type' => 'pecl',
        'dependencies' => [],
        'config' => [],
    ],

    // 缓存相关扩展
    'redis' => [
        'name' => 'redis',
        'description' => 'Redis Extension',
        'type' => 'pecl',
        'dependencies' => [],
        'config' => [],
    ],
    'memcached' => [
        'name' => 'memcached',
        'description' => 'Memcached Extension',
        'type' => 'pecl',
        'dependencies' => [],
        'config' => [],
    ],
    'apcu' => [
        'name' => 'apcu',
        'description' => 'APC User Cache',
        'type' => 'pecl',
        'dependencies' => [],
        'config' => [],
    ],

    // 网络相关扩展
    'curl' => [
        'name' => 'curl',
        'description' => 'cURL support',
        'type' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'openssl' => [
        'name' => 'openssl',
        'description' => 'OpenSSL support',
        'type' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'soap' => [
        'name' => 'soap',
        'description' => 'SOAP support',
        'type' => 'builtin',
        'dependencies' => ['xml'],
        'config' => [],
    ],

    // XML相关扩展
    'xml' => [
        'name' => 'xml',
        'description' => 'XML Parser',
        'source' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'dom' => [
        'name' => 'dom',
        'description' => 'DOM support',
        'source' => 'builtin',
        'dependencies' => ['xml'],
        'config' => [],
    ],
    'simplexml' => [
        'name' => 'simplexml',
        'description' => 'SimpleXML support',
        'source' => 'builtin',
        'dependencies' => ['xml'],
        'config' => [],
    ],

    // 其他常用扩展
    'mbstring' => [
        'name' => 'mbstring',
        'description' => 'Multibyte String',
        'source' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'intl' => [
        'name' => 'intl',
        'description' => 'Internationalization Functions',
        'source' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'zip' => [
        'name' => 'zip',
        'description' => 'Zip File Format',
        'source' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'fileinfo' => [
        'name' => 'fileinfo',
        'description' => 'File Information',
        'source' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'json' => [
        'name' => 'json',
        'description' => 'JavaScript Object Notation',
        'source' => 'builtin',
        'dependencies' => [],
        'config' => [],
    ],
    'opcache' => [
        'name' => 'opcache',
        'description' => 'OPcache',
        'source' => 'builtin',
        'dependencies' => [],
        'config' => [
            'enable' => '1',
            'memory_consumption' => '128',
            'interned_strings_buffer' => '8',
            'max_accelerated_files' => '4000',
            'revalidate_freq' => '60',
            'fast_shutdown' => '1',
            'enable_cli' => '1',
        ],
        'zend' => true,
    ],
    'xdebug' => [
        'name' => 'xdebug',
        'description' => 'Xdebug',
        'source' => 'pecl',
        'dependencies' => [],
        'config' => [
            'mode' => 'debug',
            'client_host' => 'localhost',
            'client_port' => '9003',
            'idekey' => 'PHPSTORM',
        ],
        'zend' => true,
    ],
    // phalcon
    // swoole
    // amqp
    // mongodb
    // swow
];
