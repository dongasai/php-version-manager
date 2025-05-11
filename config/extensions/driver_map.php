<?php

/**
 * 扩展驱动映射配置
 *
 * 返回格式：[扩展名] = 驱动类名
 */
return [
    // 图像处理相关扩展
    'gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd',

    // 数据库相关扩展
    'mysqli' => 'VersionManager\\Core\\Extension\\Drivers\\Mysqli',
    'pdo_mysql' => 'VersionManager\\Core\\Extension\\Drivers\\PdoMysql',

    // 缓存相关扩展
    'redis' => 'VersionManager\\Core\\Extension\\Drivers\\Redis',
    'memcached' => 'VersionManager\\Core\\Extension\\Drivers\\Memcached',

    // 特定发行版的驱动
    'ubuntu:gd' => 'VersionManager\\Core\\Extension\\Drivers\\GdUbuntu',

    // 特定架构的驱动
    'ubuntu:x86_64:gd' => 'VersionManager\\Core\\Extension\\Drivers\\GdUbuntuX8664',
];
