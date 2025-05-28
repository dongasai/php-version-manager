<?php

/**
 * GitHub Redis 扩展版本配置文件
 */

return [
    'name' => 'redis',
    'type' => 'github',
    'description' => 'PHP extension for interfacing with Redis',
    'repository' => 'phpredis/phpredis',
    'source' => 'https://github.com/phpredis/phpredis/archive/refs/tags',
    'pattern' => '{version}.tar.gz',
    'all_versions' => ['6.1.0RC1', '6.1.0RC2', '6.1.0', '6.2.0'],
    'recommended_versions' => ['6.1.0', '6.2.0'],
    'filter' => [
        'stable_only' => false,
        'exclude_patterns' => ['/alpha/', '/beta/'],
    ],
    'metadata' => [
        'total_discovered' => 4,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://api.github.com/repos/phpredis/phpredis/tags',
        'auto_updated' => false,
    ],
];
