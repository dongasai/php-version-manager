# PVM镜像同步策略文档

## 📋 概述

本文档定义了pvm-mirror项目的镜像同步策略，确保镜像源的目录结构和文件命名与UrlManager的URL转换规则保持一致。

## 🎯 同步目标

根据[URL转换规则文档](URL_CONVERSION_RULES.md)，镜像同步需要创建以下目录结构：

```
镜像源根目录/
├── php/                    # PHP源码包
├── pecl/                   # PECL扩展包  
├── composer/               # Composer包
└── github/                 # GitHub扩展包
    └── {owner}/
        └── {repo}/
```

## 🔄 同步策略

### 1. PHP源码包同步

**目标目录**: `/php/`
**文件命名**: `php-{version}.tar.gz`

**同步逻辑**:
- 源地址: `https://www.php.net/distributions/php-{version}.tar.gz`
- 目标文件: `{data_dir}/php/php-{version}.tar.gz`
- 版本范围: 根据配置文件中的版本列表

**配置示例**:
```php
'php' => [
    'source' => 'https://www.php.net/distributions',
    'versions' => [
        '8.3' => ['8.3.0', '8.3.5'],
        '8.2' => ['8.2.0', '8.2.17'],
        '8.1' => ['8.1.0', '8.1.27'],
        // ...
    ],
    'pattern' => 'php-{version}.tar.gz',
    'enabled' => true,
],
```

### 2. PECL扩展包同步

**目标目录**: `/pecl/`
**文件命名**: `{extension}-{version}.tgz`

**同步逻辑**:
- 源地址: `https://pecl.php.net/get/{extension}-{version}.tgz`
- 目标文件: `{data_dir}/pecl/{extension}-{version}.tgz`
- 扩展列表: 根据配置文件中的扩展列表

**配置示例**:
```php
'pecl' => [
    'source' => 'https://pecl.php.net/get',
    'extensions' => [
        'redis' => ['5.3.7', '6.0.2'],
        'memcached' => ['3.1.5', '3.2.0'],
        'xdebug' => ['3.1.0', '3.2.2'],
        // ...
    ],
    'pattern' => '{extension}-{version}.tgz',
    'enabled' => true,
],
```

### 3. Composer包同步

**目标目录**: `/composer/`
**文件命名**: 
- 稳定版: `composer.phar`
- 指定版本: `composer-{version}.phar`

**同步逻辑**:
- 稳定版源地址: `https://getcomposer.org/download/composer.phar`
- 指定版本源地址: `https://getcomposer.org/download/{version}/composer.phar`
- 稳定版目标文件: `{data_dir}/composer/composer.phar`
- 指定版本目标文件: `{data_dir}/composer/composer-{version}.phar`

**配置示例**:
```php
'composer' => [
    'source' => 'https://getcomposer.org/download',
    'versions' => ['stable', '2.2.21', '2.3.10', '2.4.4', '2.5.8'],
    'pattern' => 'composer-{version}.phar',
    'enabled' => true,
],
```

**实现逻辑**:
```php
if ($version === 'stable') {
    $sourceUrl = $source . '/composer.phar';
    $targetFile = $dataDir . '/composer.phar';
} else {
    $sourceUrl = $source . '/' . $version . '/composer.phar';
    $targetFile = $dataDir . '/composer-' . $version . '.phar';
}
```

### 4. GitHub扩展包同步

**目标目录**: `/github/{owner}/{repo}/`
**文件命名**: `{version}.tar.gz` (保持原始版本号格式)

**同步逻辑**:
- 源地址: `https://github.com/{owner}/{repo}/archive/refs/tags/{version}.tar.gz`
- 目标文件: `{data_dir}/github/{owner}/{repo}/{version}.tar.gz`
- 自动解析GitHub源地址提取owner和repo信息

**配置示例**:
```php
'extensions' => [
    'redis' => [
        'source' => 'https://github.com/phpredis/phpredis/archive/refs/tags',
        'versions' => ['5.3.7', '6.0.2'],
        'pattern' => '{version}.tar.gz',
        'enabled' => true,
    ],
    'memcached' => [
        'source' => 'https://github.com/php-memcached-dev/php-memcached/archive/refs/tags',
        'versions' => ['3.1.5', '3.2.0'],
        'pattern' => 'v{version}.tar.gz',
        'enabled' => true,
    ],
    // ...
],
```

**实现逻辑**:
```php
private function parseGithubSource($source)
{
    if (preg_match('#^https://github\.com/([^/]+)/([^/]+)/archive/refs/tags$#', $source, $matches)) {
        return [
            'owner' => $matches[1],
            'repo' => $matches[2]
        ];
    }
    return null;
}

// 使用解析结果创建目录
$githubInfo = $this->parseGithubSource($source);
if ($githubInfo) {
    $dataDir = $baseDir . '/github/' . $githubInfo['owner'] . '/' . $githubInfo['repo'];
}
```

## 🔧 同步命令

### 完整同步
```bash
./bin/pvm-mirror sync
```

### 指定类型同步
```bash
./bin/pvm-mirror sync php
./bin/pvm-mirror sync pecl  
./bin/pvm-mirror sync composer
./bin/pvm-mirror sync extensions
```

### 指定版本同步
```bash
./bin/pvm-mirror sync php 8.3
./bin/pvm-mirror sync composer 2.5.8
./bin/pvm-mirror sync extensions redis
```

## 📁 目录结构示例

同步完成后的目录结构：

```
data/
├── php/
│   ├── php-8.3.5.tar.gz
│   ├── php-8.2.17.tar.gz
│   └── php-8.1.27.tar.gz
├── pecl/
│   ├── redis-5.3.7.tgz
│   ├── redis-6.0.2.tgz
│   ├── memcached-3.1.5.tgz
│   └── xdebug-3.2.2.tgz
├── composer/
│   ├── composer.phar
│   ├── composer-2.5.8.phar
│   └── composer-2.4.4.phar
└── github/
    ├── phpredis/
    │   └── phpredis/
    │       ├── 5.3.7.tar.gz
    │       └── 6.0.2.tar.gz
    ├── php-memcached-dev/
    │   └── php-memcached/
    │       ├── v3.1.5.tar.gz
    │       └── v3.2.0.tar.gz
    └── xdebug/
        └── xdebug/
            └── 3.2.2.tar.gz
```

## 🔗 与UrlManager的对应关系

| 原始URL | 镜像文件路径 | UrlManager转换结果 |
|---------|-------------|-------------------|
| `https://www.php.net/distributions/php-8.1.0.tar.gz` | `/php/php-8.1.0.tar.gz` | `http://pvm.2sxo.com/php/php-8.1.0.tar.gz` |
| `https://pecl.php.net/get/redis-5.3.4.tgz` | `/pecl/redis-5.3.4.tgz` | `http://pvm.2sxo.com/pecl/redis-5.3.4.tgz` |
| `https://getcomposer.org/download/composer.phar` | `/composer/composer.phar` | `http://pvm.2sxo.com/composer/composer.phar` |
| `https://getcomposer.org/download/2.5.1/composer.phar` | `/composer/composer-2.5.1.phar` | `http://pvm.2sxo.com/composer/composer-2.5.1.phar` |
| `https://github.com/phpredis/phpredis/archive/refs/tags/5.3.4.tar.gz` | `/github/phpredis/phpredis/5.3.4.tar.gz` | `http://pvm.2sxo.com/github/phpredis/phpredis/5.3.4.tar.gz` |

## ⚠️ 注意事项

1. **目录结构一致性**: 镜像同步的目录结构必须与UrlManager的转换规则保持一致
2. **文件命名规范**: 文件命名必须遵循URL转换规则中定义的格式
3. **版本号处理**: GitHub扩展的版本号保持原始格式（包括v前缀）
4. **兼容性考虑**: 保持对旧目录结构的兼容性，但优先使用新的目录结构
5. **同步验证**: 每次同步后验证文件完整性和格式正确性

## 📝 更新历史

- **v1.0** (2024-05-28): 初始版本，定义基础同步策略
- **v1.1** (2024-05-28): 根据URL转换规则调整Composer和GitHub扩展的同步策略
