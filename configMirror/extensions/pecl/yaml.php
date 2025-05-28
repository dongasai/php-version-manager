<?php

/**
 * PECL YAML 扩展版本配置文件
 */

return [
    'name' => 'yaml',
    'type' => 'pecl',
    'description' => 'YAML-1.1 parser and emitter',
    'version_range' => ['2.2.2', '2.2.3'],
    'all_versions' => ['2.2.2', '2.2.3'],
    'recommended_versions' => ['2.2.2', '2.2.3'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/yaml/allreleases.xml',
        'auto_updated' => false,
    ],
];
