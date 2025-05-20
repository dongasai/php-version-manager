<?php

/**
 * PVM 镜像运行时配置文件
 *
 * 用于配置镜像服务的运行环境和行为
 */

return [
    // 自定义数据目录，留空则使用默认目录 ROOT_DIR/data
    'data_dir' => '',

    // 日志目录，留空则使用默认目录 ROOT_DIR/logs
    'log_dir' => '',

    // 日志级别：debug, info, warning, error
    'log_level' => 'info',

    // 镜像服务配置
    'server' => [
        // 监听主机
        'host' => '0.0.0.0',

        // 监听端口
        'port' => 34403,

        // 公开URL，用于生成下载链接
        'public_url' => 'http://localhost:34403',

        // 最大并发连接数
        'max_connections' => 100,

        // 请求超时时间（秒）
        'timeout' => 30,

        // 是否启用HTTPS
        'enable_https' => false,

        // SSL证书路径（如果启用HTTPS）
        'ssl_cert' => '',

        // SSL密钥路径（如果启用HTTPS）
        'ssl_key' => '',
    ],

    // 镜像同步配置
    'sync' => [
        // 同步间隔（小时）
        'interval' => 24,

        // 最大重试次数
        'max_retries' => 3,

        // 重试间隔（秒）
        'retry_interval' => 300,

        // 下载超时时间（秒）
        'download_timeout' => 600,

        // 最大并行下载数
        'max_parallel_downloads' => 1,

        // 是否在启动时自动同步
        'auto_sync_on_start' => true,

        // 是否使用代理
        'use_proxy' => false,

        // 代理服务器
        'proxy' => '',
    ],

    // 镜像清理配置
    'cleanup' => [
        // 每个主版本保留的最新版本数量
        'keep_versions' => 9999,

        // 最小保留天数
        'min_age' => 30,

        // 是否自动清理
        'auto_cleanup' => true,

        // 清理间隔（天）
        'cleanup_interval' => 7,
    ],

    // 缓存配置
    'cache' => [
        // 是否启用缓存
        'enabled' => true,

        // 缓存驱动：file, redis, memcached
        'driver' => 'file',

        // 缓存过期时间（秒）
        'ttl' => 3600,

        // Redis配置（如果使用Redis缓存）
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'database' => 0,
        ],

        // Memcached配置（如果使用Memcached缓存）
        'memcached' => [
            'host' => '127.0.0.1',
            'port' => 11211,
        ],
    ],

    // 安全配置
    'security' => [
        // 是否启用访问控制
        'enable_access_control' => false,

        // 是否启用IP白名单
        'enable_ip_whitelist' => false,

        // 允许的IP地址列表
        'allowed_ips' => [],

        // 是否启用基本认证（用户名/密码）
        'enable_basic_auth' => false,

        // 基本认证用户列表 (格式: ['username' => 'password', ...])
        'auth_users' => [],

        // 是否验证文件完整性
        'verify_integrity' => true,
    ],
];
