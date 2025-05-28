<?php

/**
 * PECL Memcached 扩展版本配置文件
 */

return [
    'name' => 'memcached',
    'type' => 'pecl',
    'description' => 'PHP extension for interfacing with memcached via libmemcached library',
    'version_range' => ['3.1.5', '3.2.0'],
    'all_versions' => ['3.1.5', '3.2.0'],
    'recommended_versions' => ['3.1.5', '3.2.0'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/memcached/allreleases.xml',
        'auto_updated' => false,
    ],
];
