<?php

/**
 * 版本安装驱动映射配置
 *
 * 返回格式：[驱动键] = 驱动类名
 */
return [
    // 发行版驱动
    'ubuntu' => 'VersionManager\\Core\\Version\\Drivers\\UbuntuDriver',
    'ubuntu22' => 'VersionManager\\Core\\Version\\Drivers\\Ubuntu22Driver',

    // 发行版+架构驱动
    'ubuntu:x86_64' => 'VersionManager\\Core\\Version\\Drivers\\UbuntuX8664Driver',
    'ubuntu22:x86_64' => 'VersionManager\\Core\\Version\\Drivers\\Ubuntu22X8664Driver',

    // 架构驱动
    'x86_64' => 'VersionManager\\Core\\Version\\Drivers\\X8664Driver',

    // PHP版本驱动
    'php71' => 'VersionManager\\Core\\Version\\Drivers\\Php71Driver',
    'php80' => 'VersionManager\\Core\\Version\\Drivers\\Php80Driver',

    // 发行版+PHP版本驱动
    'ubuntu:php71' => 'VersionManager\\Core\\Version\\Drivers\\UbuntuPhp71Driver',
    'ubuntu:php80' => 'VersionManager\\Core\\Version\\Drivers\\UbuntuPhp80Driver',

    // 发行版+PHP版本+架构驱动
    'ubuntu:php71:x86_64' => 'VersionManager\\Core\\Version\\Drivers\\UbuntuPhp71X8664Driver',
    'ubuntu:php80:x86_64' => 'VersionManager\\Core\\Version\\Drivers\\UbuntuPhp80X8664Driver',
];
