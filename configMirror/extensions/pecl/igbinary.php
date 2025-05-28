<?php

/**
 * PECL igbinary 扩展版本配置文件
 */

return [
    'name' => 'igbinary',
    'type' => 'pecl',
    'description' => 'Igbinary is a drop in replacement for the standard php serializer',
    'version_range' => ['3.2.7', '3.2.14'],
    'all_versions' => ['3.2.7', '3.2.14'],
    'recommended_versions' => ['3.2.7', '3.2.14'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/igbinary/allreleases.xml',
        'auto_updated' => false,
    ],
];
