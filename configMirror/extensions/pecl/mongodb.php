<?php

/**
 * PECL MongoDB 扩展版本配置文件
 */

return [
    'name' => 'mongodb',
    'type' => 'pecl',
    'description' => 'MongoDB driver for PHP',
    'version_range' => ['1.10.0', '1.16.1'],
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
        'discovery_source' => 'https://pecl.php.net/rest/r/mongodb/allreleases.xml',
        'auto_updated' => false,
    ],
];
