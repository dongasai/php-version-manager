<?php

/**
 * ARMv7 (armhf) 架构支持的PHP版本配置
 * 
 * 返回格式：[发行版][发行版版本][PHP版本] = 支持级别
 */
return [
    // Raspberry Pi OS (基于Debian)
    'raspbian' => [
        'bullseye' => [
            '8.3' => 'none',
            '8.2' => 'partial',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'full',
            '7.1' => 'partial',
        ],
        'buster' => [
            '8.3' => 'none',
            '8.2' => 'none',
            '8.1' => 'partial',
            '8.0' => 'partial',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'full',
            '7.1' => 'full',
        ],
    ],
    // Ubuntu
    'ubuntu' => [
        '22.04' => [
            '8.3' => 'none',
            '8.2' => 'partial',
            '8.1' => 'full',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'partial',
            '7.1' => 'partial',
        ],
        '20.04' => [
            '8.3' => 'none',
            '8.2' => 'partial',
            '8.1' => 'partial',
            '8.0' => 'full',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'full',
            '7.1' => 'partial',
        ],
        '18.04' => [
            '8.3' => 'none',
            '8.2' => 'none',
            '8.1' => 'partial',
            '8.0' => 'partial',
            '7.4' => 'full',
            '7.3' => 'full',
            '7.2' => 'full',
            '7.1' => 'full',
        ],
    ],
];
