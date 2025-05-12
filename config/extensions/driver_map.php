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
    'apcu' => 'VersionManager\\Core\\Extension\\Drivers\\Apcu\\Base',

    // 网络相关扩展
    'swoole' => 'VersionManager\\Core\\Extension\\Drivers\\Swoole\\Base',
    'curl' => 'VersionManager\\Core\\Extension\\Drivers\\Curl\\Base',
    'openssl' => 'VersionManager\\Core\\Extension\\Drivers\\Openssl\\Base',
    'sockets' => 'VersionManager\\Core\\Extension\\Drivers\\Sockets\\Base',

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

    // 数学相关扩展
    'bcmath' => 'VersionManager\\Core\\Extension\\Drivers\\Bcmath\\Base',
    'gmp' => 'VersionManager\\Core\\Extension\\Drivers\\Gmp\\Base',

    // 序列化相关扩展
    'igbinary' => 'VersionManager\\Core\\Extension\\Drivers\\Igbinary\\Base',
    'msgpack' => 'VersionManager\\Core\\Extension\\Drivers\\Msgpack\\Base',
    'yaml' => 'VersionManager\\Core\\Extension\\Drivers\\Yaml\\Base',

    // 加密相关扩展
    'mcrypt' => 'VersionManager\\Core\\Extension\\Drivers\\Mcrypt\\Base',

    // 系统相关扩展
    'pcntl' => 'VersionManager\\Core\\Extension\\Drivers\\Pcntl\\Base',
    'shmop' => 'VersionManager\\Core\\Extension\\Drivers\\Shmop\\Base',
    'sysvmsg' => 'VersionManager\\Core\\Extension\\Drivers\\Sysvmsg\\Base',
    'sysvsem' => 'VersionManager\\Core\\Extension\\Drivers\\Sysvsem\\Base',
    'sysvshm' => 'VersionManager\\Core\\Extension\\Drivers\\Sysvshm\\Base',

    // 其他扩展
    'zip' => 'VersionManager\\Core\\Extension\\Drivers\\Zip\\Base',
    'fileinfo' => 'VersionManager\\Core\\Extension\\Drivers\\Fileinfo\\Base',
    'json' => 'VersionManager\\Core\\Extension\\Drivers\\Json\\Base',
    'ldap' => 'VersionManager\\Core\\Extension\\Drivers\\Ldap\\Base',

    // 特定发行版的驱动
    'ubuntu:gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\Ubuntu',

    // 特定架构的驱动
    'ubuntu:x86_64:gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\UbuntuX8664',
];
