<?php

/**
 * PVM 下载缓存配置文件
 *
 * 用于配置下载缓存的行为和策略
 */

return [
    // 缓存基本设置
    'enabled' => true,                      // 是否启用下载缓存
    'cache_expire' => 86400 * 7,           // 缓存过期时间（秒），默认7天
    'max_cache_size' => 1024 * 1024 * 1024, // 最大缓存大小（字节），默认1GB
    'auto_cleanup' => true,                 // 是否自动清理过期缓存
    
    // 完整性校验设置
    'integrity_check' => [
        'enabled' => true,                  // 是否启用完整性校验
        'algorithms' => ['md5', 'sha256'],  // 默认使用的校验算法
        'verify_on_cache_hit' => true,      // 缓存命中时是否验证完整性
        'verify_on_cache_set' => true,      // 设置缓存时是否生成校验和
    ],
    
    // 文件类型特定设置
    'file_types' => [
        'php_source' => [
            'min_size' => 1024 * 1024 * 5,  // PHP源码包最小大小（5MB）
            'max_size' => 1024 * 1024 * 100, // PHP源码包最大大小（100MB）
            'algorithms' => ['md5', 'sha256'],
            'cache_priority' => 'high',      // 缓存优先级
        ],
        'extension' => [
            'min_size' => 1024 * 10,         // 扩展包最小大小（10KB）
            'max_size' => 1024 * 1024 * 50,  // 扩展包最大大小（50MB）
            'algorithms' => ['md5'],
            'cache_priority' => 'medium',
        ],
        'composer' => [
            'min_size' => 1024 * 100,        // Composer包最小大小（100KB）
            'max_size' => 1024 * 1024 * 20,  // Composer包最大大小（20MB）
            'algorithms' => ['sha256'],
            'cache_priority' => 'medium',
        ],
        'binary' => [
            'min_size' => 1024 * 1024,       // 二进制包最小大小（1MB）
            'max_size' => 1024 * 1024 * 200, // 二进制包最大大小（200MB）
            'algorithms' => ['md5', 'sha256'],
            'cache_priority' => 'high',
        ],
    ],
    
    // 缓存清理策略
    'cleanup' => [
        'auto_cleanup_interval' => 3600,    // 自动清理间隔（秒），默认1小时
        'cleanup_on_startup' => false,      // 启动时是否清理缓存
        'cleanup_threshold' => 0.8,         // 缓存使用率达到多少时触发清理
        'keep_recent_files' => 10,          // 保留最近下载的文件数量
        'strategies' => [
            'lru',                           // 最近最少使用
            'size',                          // 按文件大小
            'age',                           // 按文件年龄
        ],
    ],
    
    // 日志设置
    'logging' => [
        'enabled' => true,                   // 是否启用缓存日志
        'log_cache_hits' => true,           // 是否记录缓存命中
        'log_cache_misses' => true,         // 是否记录缓存未命中
        'log_cache_operations' => true,     // 是否记录缓存操作
        'log_integrity_checks' => true,     // 是否记录完整性检查
        'log_cleanup_operations' => true,   // 是否记录清理操作
    ],
    
    // 性能优化设置
    'performance' => [
        'parallel_verification' => false,   // 是否并行验证文件
        'lazy_verification' => true,        // 是否延迟验证（仅在需要时验证）
        'cache_metadata_in_memory' => true, // 是否在内存中缓存元数据
        'use_file_locks' => true,           // 是否使用文件锁防止并发问题
    ],
    
    // 错误处理设置
    'error_handling' => [
        'retry_on_corruption' => true,      // 发现文件损坏时是否重试下载
        'max_retries' => 3,                 // 最大重试次数
        'fallback_to_direct_download' => true, // 缓存失败时是否回退到直接下载
        'remove_corrupted_files' => true,   // 是否自动删除损坏的缓存文件
    ],
    
    // 统计和监控
    'statistics' => [
        'enabled' => true,                   // 是否启用统计
        'track_hit_rate' => true,           // 是否跟踪命中率
        'track_download_speed' => true,     // 是否跟踪下载速度
        'track_cache_efficiency' => true,   // 是否跟踪缓存效率
        'save_statistics' => true,          // 是否保存统计数据
    ],
];
