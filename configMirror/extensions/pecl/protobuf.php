<?php

/**
 * PECL Protobuf 扩展版本配置文件
 */

return [
    'name' => 'protobuf',
    'type' => 'pecl',
    'description' => 'Google\'s language-neutral, platform-neutral, extensible mechanism for serializing structured data',
    'version_range' => ['3.21.12', '3.25.1'],
    'all_versions' => ['3.21.12', '3.25.1'],
    'recommended_versions' => ['3.21.12', '3.25.1'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/protobuf/allreleases.xml',
        'auto_updated' => false,
    ],
];
