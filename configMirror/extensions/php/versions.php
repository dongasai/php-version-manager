<?php

/**
 * Extension 版本配置文件
 * 
 * 此文件由版本发现服务自动更新
 * 最后更新时间: 2025-05-28 23:38:28
 */

return [
    'versions' => [
        '8.3' => [
            '8.3.0',
            '8.3.5',
        ],
        '8.2' => [
            '8.2.0',
            '8.2.17',
        ],
    ],
    'filter' => [
        'min_version' => '5.4.0',
        'max_version' => null,
        'exclude_patterns' => [
            '/alpha/',
            '/beta/',
            '/RC/',
        ],
    ],
    'metadata' => [
        'total_versions' => 2,
        'last_updated' => '2025-05-28 23:38:28',
        'discovery_source' => 'https://www.php.net/releases/index.php?json=1',
        'auto_updated' => true,
    ],
];
