<?php

/**
 * PECL Redis 扩展版本配置文件
 * 
 * 此文件由版本发现服务自动更新
 * 最后更新时间: <?= date('Y-m-d H:i:s') ?>
 */

return [
    // 扩展基本信息
    'name' => 'redis',
    'type' => 'pecl',
    'description' => 'PHP extension for interfacing with Redis',

    // 版本范围配置
    'version_range' => ['5.3.7', '6.0.2'],

    // 所有可用版本（由版本发现服务更新）
    'all_versions' => [
        '5.3.7',
        '6.0.2',
    ],

    // 推荐版本（用于同步）
    'recommended_versions' => [
        '5.3.7',
        '6.0.2',
    ],

    // 版本过滤规则
    'filter' => [
        // 是否只包含稳定版本
        'stable_only' => true,
        // 排除的版本模式
        'exclude_patterns' => [
            '/alpha/',
            '/beta/',
            '/RC/',
        ],
    ],

    // 元数据
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/redis/allreleases.xml',
        'auto_updated' => false,
    ],
];
