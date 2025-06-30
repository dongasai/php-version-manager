# PVM配置文件迁移文档

## 📋 概述

本文档记录了PVM项目中配置文件的重新组织，将pvm-mirror相关的配置文件从`config/`目录移动到`configMirror/`目录，以实现更好的配置文件结构管理。

## 🎯 迁移目标

- **分离关注点**：将pvm-mirror相关配置与PVM主项目配置分离
- **清晰的目录结构**：通过目录名称明确配置文件的用途
- **便于维护**：独立的配置目录便于pvm-mirror项目的维护和部署

## 📁 文件迁移清单

### 迁移的配置文件

| 原路径 | 新路径 | 用途 |
|--------|--------|------|
| `config/mirror.php` | `configMirror/mirror.php` | 镜像内容配置 |
| `config/runtime.php` | `configMirror/runtime.php` | 运行时配置 |
| `config/download.php` | `configMirror/download.php` | 下载验证配置 |

### 保留的配置文件

以下配置文件保留在`config/`目录中，因为它们属于PVM主项目：

- `config/php.php` - PHP版本配置
- `config/extension.php` - 扩展配置
- 其他PVM主项目相关配置

## 🔧 代码修改清单

### 1. ConfigManager类修改

**文件**: `srcMirror/Config/ConfigManager.php`

**修改内容**:
- `loadMirrorConfig()`: 配置文件路径从 `config/mirror.php` 改为 `configMirror/mirror.php`
- `loadRuntimeConfig()`: 配置文件路径从 `config/runtime.php` 改为 `configMirror/runtime.php`
- `saveMirrorConfig()`: 保存路径从 `config/mirror.php` 改为 `configMirror/mirror.php`
- `saveRuntimeConfig()`: 保存路径从 `config/runtime.php` 改为 `configMirror/runtime.php`

### 2. ConfigCommand类修改

**文件**: `srcMirror/Command/ConfigCommand.php`

**修改内容**:
- `editConfig()`: 编辑配置文件路径更新
  - runtime配置：`config/runtime.php` → `configMirror/runtime.php`
  - mirror配置：`config/mirror.php` → `configMirror/mirror.php`

### 3. StatusCommand类修改

**文件**: `srcMirror/Command/StatusCommand.php`

**修改内容**:
- 状态显示中的配置文件路径更新
  - 镜像配置文件：`config/mirror.php` → `configMirror/mirror.php`
  - 运行时配置文件：`config/runtime.php` → `configMirror/runtime.php`

### 4. 文档更新

**文件**: `docs/Mirror.md`

**修改内容**:
- 配置文件路径引用更新
- 示例代码中的路径更新
- 使用说明中的路径更新

**文件**: `MIRROR_MANAGEMENT_FIX_REPORT.md`

**修改内容**:
- 文件列表中的路径更新

## 📂 新的目录结构

```
pvm/
├── config/                     # PVM主项目配置
│   ├── php.php
│   ├── extension.php
│   └── ...
├── configMirror/               # pvm-mirror项目配置
│   ├── mirror.php              # 镜像内容配置
│   ├── runtime.php             # 运行时配置
│   └── download.php            # 下载验证配置
├── src/                        # PVM主项目源码
├── srcMirror/                  # pvm-mirror项目源码
└── ...
```

## 🔄 配置文件内容

### configMirror/mirror.php

镜像内容配置文件，定义需要镜像的内容：

```php
<?php

/**
 * PVM 镜像内容配置文件
 * 
 * 用于配置需要镜像的内容，包括PHP版本、扩展等
 */

return [
    // PHP 镜像配置
    'php' => [
        'source' => 'https://www.php.net/distributions',
        'versions' => [
            '8.3' => ['8.3.0', '8.3.5'],
            '8.2' => ['8.2.0', '8.2.17'],
            // ...
        ],
        'pattern' => 'php-{version}.tar.gz',
        'enabled' => true,
    ],
    
    // PECL 镜像配置
    'pecl' => [
        // ...
    ],
    
    // Composer 镜像配置
    'composer' => [
        'source' => 'https://getcomposer.org/download',
        'versions' => ['stable', '2.2.21', '2.3.10', '2.4.4'],
        'pattern' => 'composer-{version}.phar',
        'enabled' => true,
    ],
    
    // GitHub扩展镜像配置
    'extensions' => [
        // ...
    ],
];
```

### configMirror/runtime.php

运行时配置文件，定义镜像服务的运行环境：

```php
<?php

/**
 * PVM 镜像运行时配置文件
 * 
 * 用于配置镜像服务的运行环境和行为
 */

return [
    // 数据存储目录
    'data_dir' => '/data/pvm-mirror',
    
    // 日志目录
    'log_dir' => '/var/log/pvm-mirror',
    
    // Web服务配置
    'web' => [
        'host' => '0.0.0.0',
        'port' => 34403,
        'document_root' => '/data/pvm-mirror/public',
    ],
    
    // 同步配置
    'sync' => [
        'auto_sync_on_start' => false,
        'sync_interval' => 3600,
        'max_concurrent_downloads' => 3,
    ],
];
```

### configMirror/download.php

下载验证配置文件，定义下载文件的验证规则：

```php
<?php

/**
 * PVM 镜像下载验证配置文件
 *
 * 用于配置下载文件的验证规则和选项
 */

return [
    // 全局下载设置
    'global' => [
        'max_retries' => 3,
        'timeout' => 300,
        'verify_content' => true,
        'show_progress' => true,
    ],
    
    // 各类型文件的验证设置
    'php' => [
        'min_size' => 1024 * 1024 * 5,  // 5MB
        'expected_type' => 'tar.gz',
        // ...
    ],
    
    // ...
];
```

## ✅ 迁移验证

### 语法检查

所有迁移的配置文件都通过了PHP语法检查：

```bash
php -l configMirror/mirror.php    # ✅ 通过
php -l configMirror/runtime.php   # ✅ 通过  
php -l configMirror/download.php  # ✅ 通过
```

### 功能验证

- ✅ ConfigManager能正确加载新路径的配置文件
- ✅ 配置编辑命令能正确定位新路径的配置文件
- ✅ 状态显示命令能正确显示新路径信息
- ✅ 文档中的路径引用已全部更新

## 🔄 向后兼容性

本次迁移是一次性的目录重组，不提供向后兼容性。如果需要回滚：

1. 将配置文件从`configMirror/`移回`config/`
2. 恢复代码中的路径引用
3. 更新相关文档

## 📝 注意事项

1. **部署更新**：在生产环境中部署时，需要确保新的`configMirror/`目录存在
2. **权限设置**：确保`configMirror/`目录具有适当的读写权限
3. **备份配置**：在迁移前建议备份原有配置文件
4. **文档同步**：所有相关文档都已更新，但第三方文档可能需要手动更新

## 📅 迁移历史

- **2024-05-28**: 完成配置文件迁移
  - 移动3个配置文件到configMirror目录
  - 更新4个源码文件中的路径引用
  - 更新相关文档

这次配置文件迁移提高了项目的组织结构清晰度，便于pvm-mirror项目的独立维护和部署。
