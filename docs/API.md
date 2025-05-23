# PHP Version Manager API 文档

## 简介

PHP Version Manager (PVM) 提供了一组 API，允许您以编程方式管理 PHP 版本、扩展和配置。本文档详细介绍了这些 API 的使用方法。

## 核心类

### VersionManager

`VersionManager` 类是 PVM 的核心类，负责管理 PHP 版本。

#### 命名空间

```php
namespace VersionManager\Core;
```

#### 方法

##### 获取当前 PHP 版本

```php
/**
 * 获取当前使用的PHP版本
 *
 * @return string 当前PHP版本
 */
public function getCurrentVersion(): string
```

##### 获取已安装的 PHP 版本

```php
/**
 * 获取已安装的PHP版本列表
 *
 * @return array 已安装的PHP版本列表
 */
public function getInstalledVersions(): array
```

##### 获取可用的 PHP 版本

```php
/**
 * 获取可用的PHP版本列表
 *
 * @return array 可用的PHP版本列表
 */
public function getAvailableVersions(): array
```

##### 安装 PHP 版本

```php
/**
 * 安装指定版本的PHP
 *
 * @param string $version PHP版本
 * @param array $options 安装选项
 * @return bool 安装结果
 */
public function installVersion(string $version, array $options = []): bool
```

选项参数：

- `with_default_extensions`：是否安装默认扩展（默认：`true`）
- `configure_options`：配置选项（默认：`[]`）
- `install_dir`：安装目录（默认：`~/.pvm/versions/{version}`）

##### 切换 PHP 版本

```php
/**
 * 切换到指定版本的PHP
 *
 * @param string $version PHP版本
 * @param bool $temporary 是否临时切换（默认：false，永久切换）
 * @return bool 切换结果
 */
public function switchVersion(string $version, bool $temporary = false): bool
```

##### 删除 PHP 版本

```php
/**
 * 删除指定版本的PHP
 *
 * @param string $version PHP版本
 * @param bool $force 是否强制删除（默认：false）
 * @return bool 删除结果
 */
public function removeVersion(string $version, bool $force = false): bool
```

### ExtensionManager

`ExtensionManager` 类负责管理 PHP 扩展。

#### 命名空间

```php
namespace VersionManager\Core;
```

#### 方法

##### 获取已安装的扩展

```php
/**
 * 获取已安装的扩展列表
 *
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return array 已安装的扩展列表
 */
public function getInstalledExtensions(string $phpVersion = null): array
```

##### 获取可用的扩展

```php
/**
 * 获取可用的扩展列表
 *
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return array 可用的扩展列表
 */
public function getAvailableExtensions(string $phpVersion = null): array
```

##### 安装扩展

```php
/**
 * 安装指定扩展
 *
 * @param string $name 扩展名
 * @param string $version 扩展版本（默认：最新版本）
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @param array $options 安装选项
 * @return bool 安装结果
 */
public function installExtension(string $name, string $version = null, string $phpVersion = null, array $options = []): bool
```

选项参数：

- `configure_options`：配置选项（默认：`[]`）
- `enable`：是否启用扩展（默认：`true`）
- `source`：扩展源（默认：`pecl`，可选：`pecl`、`source`、`custom`）

##### 启用/禁用扩展

```php
/**
 * 启用扩展
 *
 * @param string $name 扩展名
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return bool 启用结果
 */
public function enableExtension(string $name, string $phpVersion = null): bool

/**
 * 禁用扩展
 *
 * @param string $name 扩展名
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return bool 禁用结果
 */
public function disableExtension(string $name, string $phpVersion = null): bool
```

##### 删除扩展

```php
/**
 * 删除扩展
 *
 * @param string $name 扩展名
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return bool 删除结果
 */
public function removeExtension(string $name, string $phpVersion = null): bool
```

##### 获取扩展信息

```php
/**
 * 获取扩展信息
 *
 * @param string $name 扩展名
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return array|null 扩展信息
 */
public function getExtensionInfo(string $name, string $phpVersion = null): ?array
```

### ComposerManager

`ComposerManager` 类负责管理 Composer。

#### 命名空间

```php
namespace VersionManager\Core;
```

#### 方法

##### 获取 Composer 版本

```php
/**
 * 获取当前使用的Composer版本
 *
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return string|null Composer版本
 */
public function getComposerVersion(string $phpVersion = null): ?string
```

##### 获取可用的 Composer 版本

```php
/**
 * 获取可用的Composer版本列表
 *
 * @return array 可用的Composer版本列表
 */
public function getAvailableComposerVersions(): array
```

##### 安装 Composer

```php
/**
 * 安装Composer
 *
 * @param string $version Composer版本（默认：最新版本）
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return bool 安装结果
 */
public function installComposer(string $version = null, string $phpVersion = null): bool
```

##### 设置默认 Composer 版本

```php
/**
 * 设置默认Composer版本
 *
 * @param string $version Composer版本
 * @param string $phpVersion PHP版本（默认：当前版本）
 * @return bool 设置结果
 */
public function setDefaultComposer(string $version, string $phpVersion = null): bool
```

### MonitorManager

`MonitorManager` 类负责监控 PHP 进程和系统资源。

#### 命名空间

```php
namespace VersionManager\Core\System;
```

#### 方法

##### 获取 PHP 进程

```php
/**
 * 获取PHP进程列表
 *
 * @return array PHP进程列表
 */
public function getPhpProcesses(): array
```

##### 获取 PHP-FPM 进程

```php
/**
 * 获取PHP-FPM进程列表
 *
 * @return array PHP-FPM进程列表
 */
public function getFpmProcesses(): array
```

##### 获取 PHP-FPM 状态

```php
/**
 * 获取PHP-FPM状态
 *
 * @return array PHP-FPM状态信息
 */
public function getFpmStatus(): array
```

##### 获取内存使用情况

```php
/**
 * 获取PHP内存使用情况
 *
 * @return array 内存使用情况
 */
public function getMemoryUsage(): array
```

##### 获取 CPU 使用情况

```php
/**
 * 获取PHP CPU使用情况
 *
 * @return array CPU使用情况
 */
public function getCpuUsage(): array
```

##### 获取系统信息

```php
/**
 * 获取系统信息
 *
 * @return array 系统信息
 */
public function getSystemInfo(): array
```

## Web API

PVM Web 管理界面提供了一组 RESTful API，允许您通过 HTTP 请求管理 PHP 版本、扩展和配置。

### 基本信息

- 基础 URL：`http://localhost:8000/api`
- 内容类型：`application/json`

### 版本管理 API

#### 获取版本列表

```
GET /versions
```

响应：

```json
{
  "current": "8.2.0",
  "installed": ["7.4.33", "8.0.30", "8.1.27", "8.2.0"],
  "available": [
    {
      "version": "8.3.0",
      "release_date": "2023-11-23"
    },
    {
      "version": "8.2.0",
      "release_date": "2022-12-08"
    },
    ...
  ]
}
```

#### 获取版本信息

```
GET /version-info?version={version}
```

响应：

```json
{
  "version": "8.2.0",
  "path": "/home/user/.pvm/versions/8.2.0",
  "php_ini": "/home/user/.pvm/versions/8.2.0/etc/php.ini",
  "extension_dir": "/home/user/.pvm/versions/8.2.0/lib/php/extensions",
  "extensions": [
    {
      "name": "mysqli",
      "version": "8.2.0",
      "enabled": true,
      "type": "core"
    },
    ...
  ],
  "configure_options": [
    "--prefix=/home/user/.pvm/versions/8.2.0",
    "--with-mysqli",
    ...
  ],
  "install_date": "2023-01-15"
}
```

### 扩展管理 API

#### 获取扩展列表

```
GET /extensions
```

响应：

```json
{
  "installed": [
    {
      "name": "mysqli",
      "version": "8.2.0",
      "enabled": true,
      "type": "core"
    },
    ...
  ],
  "available": [
    {
      "name": "redis",
      "version": "5.3.7",
      "description": "PHP extension for Redis"
    },
    ...
  ]
}
```

#### 获取扩展信息

```
GET /extension-info?name={name}
```

响应：

```json
{
  "name": "redis",
  "version": "5.3.7",
  "enabled": true,
  "type": "pecl",
  "path": "/home/user/.pvm/versions/8.2.0/lib/php/extensions/redis.so",
  "ini_file": "/home/user/.pvm/versions/8.2.0/etc/conf.d/redis.ini",
  "install_date": "2023-02-10",
  "dependencies": ["json"],
  "config": {
    "redis.session.locking_enabled": {
      "value": "0",
      "default": "0",
      "access": "PHP_INI_ALL"
    },
    ...
  },
  "description": "PHP extension for Redis"
}
```

### 监控 API

#### 获取监控信息

```
GET /monitor
```

响应：

```json
{
  "php_processes": [
    {
      "pid": "12345",
      "ppid": "1",
      "user": "www-data",
      "cpu": "0.5",
      "mem": "1.2",
      "vsz": "123456",
      "rss": "12345",
      "stat": "S",
      "start": "10:30",
      "time": "0:01",
      "command": "/home/user/.pvm/versions/8.2.0/bin/php-fpm"
    },
    ...
  ],
  "fpm_processes": [...],
  "memory_usage": {
    "php": {
      "processes": 5,
      "rss": 12345678,
      "vsz": 123456789,
      "rss_mb": 11.77,
      "vsz_mb": 117.74
    },
    "fpm": {...},
    "total": {...}
  },
  "cpu_usage": {
    "php": {
      "processes": 5,
      "cpu": 2.5
    },
    "fpm": {...},
    "total": {...}
  },
  "system_info": {
    "os": "Linux server 5.15.0-56-generic #62-Ubuntu SMP Tue Nov 22 19:54:14 UTC 2022 x86_64",
    "php_version": "8.2.0",
    "php_binary": "/home/user/.pvm/versions/8.2.0/bin/php",
    "php_fpm_binary": "/home/user/.pvm/versions/8.2.0/sbin/php-fpm",
    "php_config_dir": "/home/user/.pvm/versions/8.2.0/etc",
    "php_extension_dir": "/home/user/.pvm/versions/8.2.0/lib/php/extensions",
    "php_ini": "/home/user/.pvm/versions/8.2.0/etc/php.ini",
    "php_fpm_conf": "/home/user/.pvm/versions/8.2.0/etc/php-fpm.conf",
    "php_fpm_www_conf": "/home/user/.pvm/versions/8.2.0/etc/php-fpm.d/www.conf",
    "php_fpm_running": true,
    "load": {
      "1min": 0.15,
      "5min": 0.10,
      "15min": 0.05
    },
    "memory": {...},
    "disk": {...}
  }
}
```
