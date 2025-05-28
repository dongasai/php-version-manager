<?php

/**
 * GitHub MongoDB 扩展版本配置文件
 */

return [
    'name' => 'mongodb',
    'type' => 'github',
    'description' => 'MongoDB driver for PHP',
    'repository' => 'mongodb/mongo-php-driver',
    'source' => 'https://github.com/mongodb/mongo-php-driver/archive/refs/tags',
    'pattern' => '{version}.tar.gz',
    'all_versions' => ['1.10.0', '1.16.1'],
    'recommended_versions' => ['1.10.0', '1.16.1'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://api.github.com/repos/mongodb/mongo-php-driver/tags',
        'auto_updated' => false,
    ],
];
