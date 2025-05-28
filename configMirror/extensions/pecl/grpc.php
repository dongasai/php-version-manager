<?php

/**
 * PECL gRPC 扩展版本配置文件
 */

return [
    'name' => 'grpc',
    'type' => 'pecl',
    'description' => 'A high performance, open source, general RPC framework',
    'version_range' => ['1.52.0', '1.58.0'],
    'all_versions' => ['1.52.0', '1.58.0'],
    'recommended_versions' => ['1.52.0', '1.58.0'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 2,
        'total_recommended' => 2,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/grpc/allreleases.xml',
        'auto_updated' => false,
    ],
];
