<?php

/**
 * GitHub Imagick 扩展版本配置文件
 */

return [
    'name' => 'imagick',
    'type' => 'github',
    'description' => 'Provides a wrapper to the ImageMagick library',
    'repository' => 'Imagick/imagick',
    'source' => 'https://github.com/Imagick/imagick/archive/refs/tags',
    'pattern' => '{version}.tar.gz',
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
        'discovery_source' => 'https://api.github.com/repos/Imagick/imagick/tags',
        'auto_updated' => false,
    ],
];
