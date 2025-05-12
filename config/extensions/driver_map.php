<?php

/**
 * 扩展驱动映射配置
 *
 * 返回格式：[扩展名] = 驱动类名
 */
return [
    // 图像处理相关扩展
    'gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\Base',
    'imagick' => 'VersionManager\\Core\\Extension\\Drivers\\Imagick\\Base',

    // 数据库相关扩展
    'mysqli' => 'VersionManager\\Core\\Extension\\Drivers\\Mysqli\\Base',
    'pdo_mysql' => 'VersionManager\\Core\\Extension\\Drivers\\PdoMysql',
    'mongodb' => 'VersionManager\\Core\\Extension\\Drivers\\Mongodb\\Base',

    // 缓存相关扩展
    'redis' => 'VersionManager\\Core\\Extension\\Drivers\\Redis\\Base',
    'memcached' => 'VersionManager\\Core\\Extension\\Drivers\\Memcached\\Base',

    // 网络相关扩展
    'swoole' => 'VersionManager\\Core\\Extension\\Drivers\\Swoole\\Base',
    'curl' => 'VersionManager\\Core\\Extension\\Drivers\\Curl\\Base',

    // 调试相关扩展
    'xdebug' => 'VersionManager\\Core\\Extension\\Drivers\\Xdebug\\Base',
    'opcache' => 'VersionManager\\Core\\Extension\\Drivers\\Opcache\\Base',

    // 国际化相关扩展
    'intl' => 'VersionManager\\Core\\Extension\\Drivers\\Intl\\Base',
    'mbstring' => 'VersionManager\\Core\\Extension\\Drivers\\Mbstring\\Base',

    // XML相关扩展
    'xml' => 'VersionManager\\Core\\Extension\\Drivers\\Xml\\Base',
    'dom' => 'VersionManager\\Core\\Extension\\Drivers\\Dom\\Base',
    'simplexml' => 'VersionManager\\Core\\Extension\\Drivers\\Simplexml\\Base',
    'soap' => 'VersionManager\\Core\\Extension\\Drivers\\Soap\\Base',

    // 其他扩展
    'zip' => 'VersionManager\\Core\\Extension\\Drivers\\Zip\\Base',

    // 特定发行版的驱动
    'ubuntu:gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\Ubuntu',

    // 特定架构的驱动
    'ubuntu:x86_64:gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\UbuntuX8664',
];
