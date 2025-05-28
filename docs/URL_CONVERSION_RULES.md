# PVM镜像源URL转换规则文档

## 📋 概述

本文档定义了PVM系统中官方源URL到镜像源URL的转换规则。PVM镜像源支持4类主要官方源的镜像适配，通过统一的URL转换规则实现智能下载地址管理。

## 🎯 支持的官方源

### 1. PHP官方源 (php.net)

**官方源域名**: `www.php.net`

**转换规则**:
- **路径匹配**: `/distributions/*`
- **转换逻辑**: 提取文件名，映射到镜像源的`/php/`路径

**示例**:
```
原始URL: https://www.php.net/distributions/php-8.1.0.tar.gz
镜像URL: http://pvm.2sxo.com/php/php-8.1.0.tar.gz

原始URL: https://www.php.net/distributions/php-7.4.33.tar.gz  
镜像URL: http://pvm.2sxo.com/php/php-7.4.33.tar.gz
```

### 2. PECL扩展源 (pecl.php.net)

**官方源域名**: `pecl.php.net`

**转换规则**:
- **路径匹配**: `/get/*`
- **转换逻辑**: 提取文件名，映射到镜像源的`/pecl/`路径

**示例**:
```
原始URL: https://pecl.php.net/get/redis-5.3.4.tgz
镜像URL: http://pvm.2sxo.com/pecl/redis-5.3.4.tgz

原始URL: https://pecl.php.net/get/memcached-3.2.0.tgz
镜像URL: http://pvm.2sxo.com/pecl/memcached-3.2.0.tgz

原始URL: https://pecl.php.net/get/xdebug-3.2.1.tgz
镜像URL: http://pvm.2sxo.com/pecl/xdebug-3.2.1.tgz
```

### 3. Composer官方源 (getcomposer.org)

**官方源域名**: `getcomposer.org`

**转换规则**:
- **路径匹配**: `/download/*`
- **转换逻辑**: 
  - 稳定版: `/download/composer.phar` → `/composer/composer.phar`
  - 指定版本: `/download/{version}/composer.phar` → `/composer/composer-{version}.phar`

**示例**:
```
# 稳定版
原始URL: https://getcomposer.org/download/composer.phar
镜像URL: http://pvm.2sxo.com/composer/composer.phar

# 指定版本
原始URL: https://getcomposer.org/download/2.5.1/composer.phar
镜像URL: http://pvm.2sxo.com/composer/composer-2.5.1.phar

原始URL: https://getcomposer.org/download/1.10.26/composer.phar
镜像URL: http://pvm.2sxo.com/composer/composer-1.10.26.phar
```

### 4. GitHub扩展源 (github.com)

**官方源域名**: `github.com`

**转换规则**:
- **路径匹配**: `/{owner}/{repo}/archive/refs/tags/{version}`
- **转换逻辑**: 提取owner、repo、version，映射到镜像源的`/github/{owner}/{repo}/`路径

**示例**:
```
原始URL: https://github.com/phpredis/phpredis/archive/refs/tags/5.3.4.tar.gz
镜像URL: http://pvm.2sxo.com/github/phpredis/phpredis/5.3.4.tar.gz

原始URL: https://github.com/php-memcached-dev/php-memcached/archive/refs/tags/v3.2.0.tar.gz
镜像URL: http://pvm.2sxo.com/github/php-memcached-dev/php-memcached/v3.2.0.tar.gz

原始URL: https://github.com/xdebug/xdebug/archive/refs/tags/3.2.1.tar.gz
镜像URL: http://pvm.2sxo.com/github/xdebug/xdebug/3.2.1.tar.gz

原始URL: https://github.com/mongodb/mongo-php-driver/archive/refs/tags/1.15.0.tar.gz
镜像URL: http://pvm.2sxo.com/github/mongodb/mongo-php-driver/1.15.0.tar.gz

原始URL: https://github.com/Imagick/imagick/archive/refs/tags/3.7.0.tar.gz
镜像URL: http://pvm.2sxo.com/github/Imagick/imagick/3.7.0.tar.gz

原始URL: https://github.com/swoole/swoole-src/archive/refs/tags/v4.8.12.tar.gz
镜像URL: http://pvm.2sxo.com/github/swoole/swoole-src/v4.8.12.tar.gz
```

## 🔄 转换算法

### 核心转换逻辑

```php
private function convertToMirrorUrl($originalUrl)
{
    $parsedUrl = parse_url($originalUrl);
    if (!$parsedUrl || !isset($parsedUrl['host'])) {
        return null;
    }
    
    $host = $parsedUrl['host'];
    $path = $parsedUrl['path'] ?? '';
    
    switch ($host) {
        case 'www.php.net':
            if (strpos($path, '/distributions/') === 0) {
                $filename = basename($path);
                return $this->getMirrorBaseUrl() . '/php/' . $filename;
            }
            break;
            
        case 'pecl.php.net':
            if (strpos($path, '/get/') === 0) {
                $filename = basename($path);
                return $this->getMirrorBaseUrl() . '/pecl/' . $filename;
            }
            break;
            
        case 'getcomposer.org':
            if (strpos($path, '/download/') === 0) {
                $pathParts = explode('/', trim($path, '/'));
                if (count($pathParts) === 2) {
                    // /download/composer.phar
                    return $this->getMirrorBaseUrl() . '/composer/composer.phar';
                } elseif (count($pathParts) === 3) {
                    // /download/2.5.1/composer.phar
                    $version = $pathParts[1];
                    return $this->getMirrorBaseUrl() . '/composer/composer-' . $version . '.phar';
                }
            }
            break;
            
        case 'github.com':
            if (preg_match('#^/([^/]+)/([^/]+)/archive/refs/tags/(.+)$#', $path, $matches)) {
                $owner = $matches[1];
                $repo = $matches[2];
                $filename = $matches[3];
                return $this->getMirrorBaseUrl() . '/github/' . $owner . '/' . $repo . '/' . $filename;
            }
            break;
    }
    
    return null;
}
```

## 📁 镜像源目录结构

```
镜像源根目录/
├── php/                    # PHP源码包
│   ├── php-8.1.0.tar.gz
│   ├── php-8.0.28.tar.gz
│   └── php-7.4.33.tar.gz
├── pecl/                   # PECL扩展包
│   ├── redis-5.3.4.tgz
│   ├── memcached-3.2.0.tgz
│   └── xdebug-3.2.1.tgz
├── composer/               # Composer包
│   ├── composer.phar
│   ├── composer-2.5.1.phar
│   └── composer-1.10.26.phar
└── github/                 # GitHub扩展包
    ├── phpredis/
    │   └── phpredis/
    │       └── 5.3.4.tar.gz
    ├── php-memcached-dev/
    │   └── php-memcached/
    │       └── v3.2.0.tar.gz
    └── xdebug/
        └── xdebug/
            └── 3.2.1.tar.gz
```

## 🚀 使用方式

### 1. 通过UrlManager使用

```php
use VersionManager\Core\Download\UrlManager;

$urlManager = new UrlManager();

// 获取PHP下载URL
$phpUrls = $urlManager->getPhpDownloadUrls('8.1.0');

// 获取PECL扩展下载URL  
$peclUrls = $urlManager->getPeclDownloadUrls('redis', '5.3.4');

// 获取Composer下载URL
$composerUrls = $urlManager->getComposerDownloadUrls('2.5.1');

// 获取GitHub扩展下载URL
$githubUrls = $urlManager->getGithubExtensionDownloadUrls('phpredis', 'phpredis', '5.3.4');

// 通用URL转换
$urls = $urlManager->getDownloadUrls('https://www.php.net/distributions/php-8.1.0.tar.gz');
```

### 2. 优先级顺序

当PVM镜像源启用时，URL返回顺序为：
1. **主镜像源**: `http://pvm.2sxo.com/...`
2. **备用镜像源**: `http://localhost:34403/...`, `http://mirror.example.com/...`
3. **官方源**: `https://www.php.net/...`, `https://pecl.php.net/...`

当PVM镜像源禁用时，只返回官方源URL。

## ⚠️ 注意事项

1. **路径匹配严格**: 只有完全匹配指定路径模式的URL才会被转换
2. **文件名保持**: 转换后的文件名与原始文件名保持一致
3. **版本号处理**: GitHub扩展的版本号保持原始格式（包括v前缀）
4. **不支持的URL**: 不匹配规则的URL不会被转换，直接使用原始URL
5. **镜像源状态**: 转换行为受PVM镜像源启用/禁用状态控制

## 🔧 扩展支持

如需添加新的官方源支持，需要：

1. 在`UrlManager::convertToMirrorUrl()`中添加新的case分支
2. 定义相应的路径匹配规则和转换逻辑
3. 在镜像源服务器上创建对应的目录结构
4. 更新本文档的转换规则说明

## 📝 版本历史

- **v1.0** (2024-05-28): 初始版本，支持4类主要官方源
  - php.net (PHP源码)
  - pecl.php.net (PECL扩展)  
  - getcomposer.org (Composer)
  - github.com (GitHub扩展)
