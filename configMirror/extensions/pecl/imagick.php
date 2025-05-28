<?php

/**
 * PECL Imagick 扩展版本配置文件
 */

return [
    'name' => 'imagick',
    'type' => 'pecl',
    'description' => 'Provides a wrapper to the ImageMagick library',
    'version_range' => ['3.7.0', '3.7.0'],
    'all_versions' => ['3.7.0'],
    'recommended_versions' => ['3.7.0'],
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],
    'metadata' => [
        'total_discovered' => 1,
        'total_recommended' => 1,
        'last_updated' => null,
        'discovery_source' => 'https://pecl.php.net/rest/r/imagick/allreleases.xml',
        'auto_updated' => false,
    ],
];
