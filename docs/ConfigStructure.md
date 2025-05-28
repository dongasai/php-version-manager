# PVM-Mirror 配置文件结构优化

## 📁 新的配置文件结构

为了更好地管理版本信息，我们将配置文件进行了模块化重构：

```
configMirror/
├── mirror.php              # 主配置文件（基本设置）
├── runtime.php             # 运行时配置
├── download.php            # 下载配置
├── extensions/             # 扩展配置目录
│   ├── pecl/              # PECL 扩展版本
│   │   ├── redis.php
│   │   ├── memcached.php
│   │   ├── xdebug.php
│   │   ├── mongodb.php
│   │   ├── imagick.php
│   │   ├── swoole.php
│   │   ├── yaml.php
│   │   ├── protobuf.php
│   │   ├── grpc.php
│   │   └── igbinary.php
│   ├── github/            # GitHub 扩展版本
│   │   ├── redis.php
│   │   ├── memcached.php
│   │   ├── xdebug.php
│   │   ├── mongodb.php
│   │   ├── imagick.php
│   │   └── swoole.php
│   └── php/               # PHP 版本
│       └── versions.php
└── composer/              # Composer 版本
    └── versions.php
```

## 🎯 优化目标

### 1. **模块化管理**
- 每个扩展使用独立的配置文件
- 版本信息与基本配置分离
- 便于维护和更新

### 2. **智能版本管理**
- 支持发现的所有版本和推荐版本
- 自动智能选择合适数量的版本
- 避免配置文件过大

### 3. **元数据支持**
- 记录版本发现来源
- 记录最后更新时间
- 记录版本统计信息

## 📄 配置文件格式

### PHP 版本配置 (`extensions/php/versions.php`)

```php
<?php
return [
    // 版本范围配置
    'versions' => [
        '5.6' => ['5.6.0', '5.6.40'],
        '7.0' => ['7.0.0', '7.0.33'],
        '7.1' => ['7.1.0', '7.1.33'],
        '7.2' => ['7.2.0', '7.2.34'],
        '7.3' => ['7.3.0', '7.3.33'],
        '7.4' => ['7.4.0', '7.4.33'],
        '8.0' => ['8.0.0', '8.0.30'],
        '8.1' => ['8.1.0', '8.1.27'],
        '8.2' => ['8.2.0', '8.2.17'],
        '8.3' => ['8.3.0', '8.3.5'],
    ],

    // 版本过滤规则
    'filter' => [
        'min_version' => '5.6.0',
        'max_version' => null,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
    ],

    // 元数据
    'metadata' => [
        'total_versions' => 20,
        'last_updated' => '2024-01-15 10:30:00',
        'discovery_source' => 'https://www.php.net/releases/index.php?json=1',
        'auto_updated' => true,
    ],
];
```

### PECL 扩展配置 (`extensions/pecl/swoole.php`)

```php
<?php
return [
    // 扩展基本信息
    'name' => 'swoole',
    'type' => 'pecl',
    'description' => 'Event-driven asynchronous and concurrent networking engine',

    // 版本范围配置
    'version_range' => ['4.8.13', '5.0.3'],

    // 所有可用版本（由版本发现服务更新）
    'all_versions' => [
        '4.4.15', '4.4.16', '4.4.17', '4.4.18', '4.4.19', '4.4.20',
        '4.5.0', '4.5.1', '4.5.2', '4.5.3', '4.5.4', '4.5.5',
        '4.6.0', '4.6.1', '4.6.2', '4.6.3', '4.6.4', '4.6.5',
        '4.7.0', '4.7.1', '4.8.0', '4.8.1', '4.8.2', '4.8.3',
        '4.8.4', '4.8.5', '4.8.6', '4.8.7', '4.8.8', '4.8.9',
        '4.8.10', '4.8.11', '4.8.12', '4.8.13',
        '5.0.0', '5.0.1', '5.0.2', '5.0.3',
        '5.1.0', '5.1.1',
    ],

    // 推荐版本（用于同步）
    'recommended_versions' => [
        '4.8.11', '4.8.12', '4.8.13',  // 4.x 最新3个
        '5.0.1', '5.0.2', '5.0.3',     // 5.0.x 最新3个
        '5.1.0', '5.1.1',              // 5.1.x 最新3个
    ],

    // 版本过滤规则
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
        'smart_selection' => [
            'enabled' => true,
            'max_versions_per_major' => 3,
            'max_total_versions' => 20,
        ],
    ],

    // 元数据
    'metadata' => [
        'total_discovered' => 42,
        'total_recommended' => 8,
        'last_updated' => '2024-01-15 10:30:00',
        'discovery_source' => 'https://pecl.php.net/rest/r/swoole/allreleases.xml',
        'auto_updated' => true,
    ],
];
```

### GitHub 扩展配置 (`extensions/github/swoole.php`)

```php
<?php
return [
    // 扩展基本信息
    'name' => 'swoole',
    'type' => 'github',
    'description' => 'Event-driven asynchronous and concurrent networking engine',
    'repository' => 'swoole/swoole-src',

    // GitHub 源配置
    'source' => 'https://github.com/swoole/swoole-src/archive/refs/tags',
    'pattern' => '{version}.tar.gz',

    // 所有可用版本（由版本发现服务更新）
    'all_versions' => [
        'v4.8.11', 'v4.8.12', 'v4.8.13',
        'v5.0.1', 'v5.0.2', 'v5.0.3',
        'v5.1.0', 'v5.1.1',
    ],

    // 推荐版本（用于同步）
    'recommended_versions' => [
        'v4.8.11', 'v4.8.12', 'v4.8.13',
        'v5.0.1', 'v5.0.2', 'v5.0.3',
        'v5.1.0', 'v5.1.1',
    ],

    // 版本过滤规则
    'filter' => [
        'stable_only' => true,
        'exclude_patterns' => ['/alpha/', '/beta/', '/RC/'],
        'smart_selection' => [
            'enabled' => true,
            'max_versions_per_major' => 3,
            'max_total_versions' => 20,
        ],
    ],

    // 元数据
    'metadata' => [
        'total_discovered' => 151,
        'total_recommended' => 8,
        'last_updated' => '2024-01-15 10:30:00',
        'discovery_source' => 'https://api.github.com/repos/swoole/swoole-src/tags',
        'auto_updated' => true,
    ],
];
```

## 🔧 使用方式

### 1. **版本发现和更新**

```bash
# 发现所有版本
./bin/pvm-mirror discover

# 更新配置文件
./bin/pvm-mirror update-config

# 更新指定扩展
./bin/pvm-mirror update-config github swoole
```

### 2. **同步镜像**

同步功能保持不变，继续使用配置文件中的版本信息：

```bash
# 同步所有内容
./bin/pvm-mirror sync

# 同步指定类型
./bin/pvm-mirror sync php
./bin/pvm-mirror sync pecl
./bin/pvm-mirror sync extensions
```

## 🎨 优势

### 1. **模块化管理**
- 每个扩展独立配置文件
- 便于维护和版本控制
- 支持并行更新

### 2. **智能版本选择**
- 自动处理大量版本（如 swoole 的 150+ 版本）
- 智能选择每个主版本的最新版本
- 避免配置文件过大

### 3. **丰富的元数据**
- 记录版本发现来源
- 记录更新时间和统计信息
- 支持手动和自动更新标记

### 4. **向后兼容**
- 同步逻辑保持不变
- 现有命令继续工作
- 平滑迁移

这种新的配置结构既解决了版本数量过多的问题，又提供了更好的管理和维护体验。
