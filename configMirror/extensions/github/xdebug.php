<?php

/**
 * GitHub Xdebug 扩展版本配置文件
 */

return [
    'name' => 'xdebug',
    'type' => 'github',
    'description' => 'Xdebug is a debugger and profiler tool for PHP',
    'repository' => 'xdebug/xdebug',
    'source' => 'https://github.com/xdebug/xdebug/archive/refs/tags',
    'pattern' => '{version}.tar.gz',
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
        'discovery_source' => 'https://api.github.com/repos/xdebug/xdebug/tags',
        'auto_updated' => false,
    ],
];
