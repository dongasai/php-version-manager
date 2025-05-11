<?php

/**
 * ARM64 (AArch64) 架构支持的PHP版本配置
 * 
 * 返回格式：[发行版][发行版版本][PHP版本] = 支持级别
 */
return [
    // Ubuntu
    'ubuntu' => [
        '22.04' => [
            '8.3' => 'full',
            '8.2' => 'full',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'partial',
            '7.2' => 'partial',
            '7.1' => 'partial',
        ],
        '20.04' => [
            '8.3' => 'partial',
            '8.2' => 'full',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'partial',
            '7.1' => 'partial',
        ],
        '18.04' => [
            '8.3' => 'none',
            '8.2' => 'partial',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'full',
            '7.1' => 'partial',
        ],
    ],
    // Debian
    'debian' => [
        '12' => [
            '8.3' => 'full',
            '8.2' => 'full',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'partial',
            '7.2' => 'partial',
            '7.1' => 'partial',
        ],
        '11' => [
            '8.3' => 'partial',
            '8.2' => 'full',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'partial',
            '7.1' => 'partial',
        ],
        '10' => [
            '8.3' => 'none',
            '8.2' => 'partial',
            '8.1' => 'partial',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'full',
            '7.1' => 'partial',
        ],
    ],
    // Raspberry Pi OS (基于Debian)
    'raspbian' => [
        'bullseye' => [
            '8.3' => 'partial',
            '8.2' => 'full',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'partial',
            '7.1' => 'partial',
        ],
        'buster' => [
            '8.3' => 'none',
            '8.2' => 'partial',
            '8.1' => 'partial',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'full',
            '7.1' => 'partial',
        ],
    ],
    // Alpine Linux
    'alpine' => [
        '3.18' => [
            '8.3' => 'full',
            '8.2' => 'full',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'partial',
            '7.2' => 'partial',
            '7.1' => 'none',
        ],
        '3.17' => [
            '8.3' => 'partial',
            '8.2' => 'full',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'partial',
            '7.2' => 'partial',
            '7.1' => 'none',
        ],
        '3.16' => [
            '8.3' => 'none',
            '8.2' => 'partial',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'partial',
            '7.1' => 'partial',
        ],
    ],
];
