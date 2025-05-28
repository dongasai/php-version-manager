<?php

/**
 * PECL Xdebug 扩展版本配置文件
 */

return [
    'name' => 'xdebug',
    'type' => 'pecl',
    'description' => 'Xdebug is a debugger and profiler tool for PHP',
    'version_range' => ['3.1.0', '3.2.2'],
    'all_versions' => ['3.1.0', '3.2.2'],
    'recommended_versions' => ['3.1.0', '3.2.2'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/xdebug/allreleases.xml',
        'auto_updated' => false,
    ],
];
