<?php

/**
 * GitHub Memcached 扩展版本配置文件
 */

return [
    'name' => 'memcached',
    'type' => 'github',
    'description' => 'PHP extension for interfacing with memcached via libmemcached library',
    'repository' => 'php-memcached-dev/php-memcached',
    'source' => 'https://github.com/php-memcached-dev/php-memcached/archive/refs/tags',
    'pattern' => 'v{version}.tar.gz',
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
        'discovery_source' => 'https://api.github.com/repos/php-memcached-dev/php-memcached/tags',
        'auto_updated' => false,
    ],
];
