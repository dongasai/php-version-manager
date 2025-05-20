# PVM 镜像应用

本文档描述了如何创建和使用 PVM 镜像应用，用于镜像本项目涉及的所有需要下载的内容（系统包不包含）。

## 需要镜像的内容

PVM 项目在安装和使用过程中需要从互联网下载以下内容：

1. **PHP 源码包**：从 php.net 下载的各版本 PHP 源码
2. **PECL 扩展包**：从 pecl.php.net 下载的 PHP 扩展
3. **特定扩展的 GitHub 源码**：某些扩展（如 Redis、Memcached、Xdebug 等）从 GitHub 下载的源码
4. **Composer 包**：从 getcomposer.org 下载的 Composer 安装包

## 镜像应用设计

### 1. 目录结构

镜像应用将使用以下目录结构：

```
pvm-mirror/
├── bin/                    # 可执行脚本
│   ├── pvm-mirror          # 主程序
│   └── sync.sh             # 同步脚本
├── config/                 # 配置文件
│   └── mirror.php          # 镜像配置
├── data/                   # 镜像数据
│   ├── php/                # PHP 源码镜像
│   ├── pecl/               # PECL 扩展镜像
│   ├── extensions/         # 特定扩展镜像
│   │   ├── redis/          # Redis 扩展镜像
│   │   ├── memcached/      # Memcached 扩展镜像
│   │   └── xdebug/         # Xdebug 扩展镜像
│   └── composer/           # Composer 镜像
├── logs/                   # 日志文件
├── public/                 # Web 服务根目录
│   ├── index.php           # 下载站点首页
│   ├── php/                # PHP 源码镜像（符号链接到 data/php）
│   ├── pecl/               # PECL 扩展镜像（符号链接到 data/pecl）
│   ├── extensions/         # 特定扩展镜像（符号链接到 data/extensions）
│   └── composer/           # Composer 镜像（符号链接到 data/composer）
└── src/                    # 源代码
    ├── Mirror/             # 镜像相关类
    │   ├── PhpMirror.php   # PHP 镜像类
    │   ├── PeclMirror.php  # PECL 镜像类
    │   └── ...
    └── Web/                # Web 相关类
        ├── Controller.php  # 控制器
        └── ...
```

### 2. 功能设计

镜像应用将提供以下功能：

#### 2.1 镜像同步

- 同步 PHP 源码包
- 同步 PECL 扩展包
- 同步特定扩展的 GitHub 源码
- 同步 Composer 包

#### 2.2 镜像服务

- 提供 Web 服务，允许通过 HTTP 访问镜像内容
- 提供 API 接口，用于查询镜像状态和内容

#### 2.3 镜像管理

- 管理镜像配置
- 查看镜像状态
- 清理过期镜像

### 3. 配置设计

镜像配置文件 `config/mirror.php` 示例：

```php
<?php

return [
    // PHP 源码镜像配置
    'php' => [
        'source' => 'https://www.php.net/distributions',
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
        'pattern' => 'php-{version}.tar.gz',
    ],
    
    // PECL 扩展镜像配置
    'pecl' => [
        'source' => 'https://pecl.php.net/get',
        'extensions' => [
            'redis' => ['5.3.7', '6.0.2'],
            'memcached' => ['3.1.5', '3.2.0'],
            'xdebug' => ['3.1.0', '3.2.2'],
            'mongodb' => ['1.10.0', '1.16.1'],
            'imagick' => ['3.7.0', '3.7.0'],
        ],
        'pattern' => '{extension}-{version}.tgz',
    ],
    
    // 特定扩展的 GitHub 源码镜像配置
    'extensions' => [
        'redis' => [
            'source' => 'https://github.com/phpredis/phpredis/archive/refs/tags',
            'versions' => ['5.3.7', '6.0.2'],
            'pattern' => '{version}.tar.gz',
        ],
        'memcached' => [
            'source' => 'https://github.com/php-memcached-dev/php-memcached/archive/refs/tags',
            'versions' => ['3.1.5', '3.2.0'],
            'pattern' => 'v{version}.tar.gz',
        ],
        'xdebug' => [
            'source' => 'https://github.com/xdebug/xdebug/archive/refs/tags',
            'versions' => ['3.1.0', '3.2.2'],
            'pattern' => '{version}.tar.gz',
        ],
    ],
    
    // Composer 镜像配置
    'composer' => [
        'source' => 'https://getcomposer.org/download',
        'versions' => ['2.2.21', '2.3.10', '2.4.4', '2.5.8', '2.6.5'],
        'pattern' => 'composer-{version}.phar',
    ],
];
```

## 实现方案

### 1. 镜像同步脚本

创建一个 PHP 脚本，用于同步镜像内容：

```php
#!/usr/bin/env php
<?php

// 加载配置
$config = require __DIR__ . '/../config/mirror.php';

// 同步 PHP 源码包
syncPhpSources($config['php']);

// 同步 PECL 扩展包
syncPeclExtensions($config['pecl']);

// 同步特定扩展的 GitHub 源码
syncGithubExtensions($config['extensions']);

// 同步 Composer 包
syncComposerPackages($config['composer']);

/**
 * 同步 PHP 源码包
 */
function syncPhpSources($config) {
    $source = $config['source'];
    $pattern = $config['pattern'];
    $dataDir = __DIR__ . '/../data/php';
    
    // 确保目录存在
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // 遍历版本
    foreach ($config['versions'] as $majorVersion => $versionRange) {
        list($minVersion, $maxVersion) = $versionRange;
        
        // 获取版本列表
        $versions = getVersionRange($minVersion, $maxVersion);
        
        foreach ($versions as $version) {
            $filename = str_replace('{version}', $version, $pattern);
            $sourceUrl = $source . '/' . $filename;
            $targetFile = $dataDir . '/' . $filename;
            
            // 如果文件不存在，则下载
            if (!file_exists($targetFile)) {
                echo "下载 PHP $version: $sourceUrl\n";
                downloadFile($sourceUrl, $targetFile);
            }
        }
    }
}

// 其他同步函数类似...
```

### 2. Web 服务

创建一个简单的 Web 服务，用于提供镜像内容：

```php
<?php
// public/index.php

// 设置内容类型
header('Content-Type: text/html; charset=utf-8');

// 获取请求路径
$requestPath = $_SERVER['REQUEST_URI'];

// 如果是根路径，显示首页
if ($requestPath === '/' || $requestPath === '/index.php') {
    showHomePage();
    exit;
}

// 处理文件下载请求
handleFileRequest($requestPath);

/**
 * 显示首页
 */
function showHomePage() {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PVM 下载站</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        h1 { color: #333; }
        .section { margin-bottom: 20px; }
        .section h2 { color: #555; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 5px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>PVM 下载站</h1>
    
    <div class="section">
        <h2>PHP 源码包</h2>
        <ul>
            <li><a href="/php/">浏览所有 PHP 源码包</a></li>
        </ul>
    </div>
    
    <div class="section">
        <h2>PECL 扩展包</h2>
        <ul>
            <li><a href="/pecl/">浏览所有 PECL 扩展包</a></li>
        </ul>
    </div>
    
    <div class="section">
        <h2>特定扩展源码</h2>
        <ul>
            <li><a href="/extensions/">浏览所有特定扩展源码</a></li>
        </ul>
    </div>
    
    <div class="section">
        <h2>Composer 包</h2>
        <ul>
            <li><a href="/composer/">浏览所有 Composer 包</a></li>
        </ul>
    </div>
</body>
</html>';
}

// 其他函数...
```

## 使用方法

### 1. 安装镜像应用

```bash
# 克隆仓库
git clone https://github.com/yourusername/pvm-mirror.git
cd pvm-mirror

# 创建数据目录
mkdir -p data/{php,pecl,extensions,composer}
mkdir -p logs
mkdir -p public/{php,pecl,extensions,composer}

# 创建符号链接
ln -s ../data/php public/php
ln -s ../data/pecl public/pecl
ln -s ../data/extensions public/extensions
ln -s ../data/composer public/composer

# 设置权限
chmod +x bin/pvm-mirror
chmod +x bin/sync.sh
```

### 2. 配置镜像应用

编辑 `config/mirror.php` 文件，根据需要调整配置。

### 3. 同步镜像内容

```bash
# 同步所有镜像内容
./bin/pvm-mirror sync

# 或者使用 sync.sh 脚本
./bin/sync.sh
```

### 4. 启动 Web 服务

```bash
# 使用 PHP 内置 Web 服务器
cd public
php -S 0.0.0.0:8080

# 或者配置 Nginx/Apache
```

### 5. 配置 PVM 使用镜像

编辑 PVM 的镜像配置文件 `~/.pvm/config/mirrors.php`：

```php
<?php

// 镜像配置文件
// 由 PVM 自动生成，可以手动修改

return [
    'php' => [
        'official' => 'https://www.php.net/distributions',
        'mirrors' => [
            'local' => 'http://localhost:8080/php',  // 添加本地镜像
        ],
        'default' => 'local',  // 设置默认使用本地镜像
    ],
    // 其他配置...
];
```

## 注意事项

1. 镜像应用需要足够的磁盘空间来存储所有镜像内容
2. 定期同步镜像内容，以获取最新的版本
3. 如果在生产环境使用，建议配置 HTTPS 和访问控制
4. 可以根据需要调整镜像的版本范围，减少存储空间占用
