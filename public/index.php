<?php

/**
 * PVM 镜像 Web 服务
 * 
 * 提供 Web 界面和文件下载服务
 */

// 定义根目录
define('ROOT_DIR', dirname(__DIR__));

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
    // 加载配置
    $config = require ROOT_DIR . '/config/mirror.php';
    
    // 获取镜像状态
    $status = getMirrorStatus();
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PVM 镜像站</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
        h1 { color: #333; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #555; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .info p { margin: 5px 0; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 8px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .version-list { display: flex; flex-wrap: wrap; }
        .version-item { margin-right: 15px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>PVM 镜像站</h1>
    
    <div class="info">
        <p><strong>总文件数:</strong> ' . $status['total_files'] . '</p>
        <p><strong>总大小:</strong> ' . formatSize($status['total_size']) . '</p>
        <p><strong>最后更新:</strong> ' . date('Y-m-d H:i:s', $status['last_update']) . '</p>
    </div>
    
    <div class="section">
        <h2>PHP 源码包</h2>
        <div class="version-list">';
        
    // 显示 PHP 版本
    foreach ($config['php']['versions'] as $majorVersion => $versionRange) {
        echo '<div class="version-item"><a href="/php/?version=' . $majorVersion . '">PHP ' . $majorVersion . '</a></div>';
    }
        
    echo '</div>
        <p><a href="/php/">浏览所有 PHP 源码包</a></p>
    </div>
    
    <div class="section">
        <h2>PECL 扩展包</h2>
        <div class="version-list">';
        
    // 显示 PECL 扩展
    foreach ($config['pecl']['extensions'] as $extension => $versionRange) {
        echo '<div class="version-item"><a href="/pecl/?extension=' . $extension . '">' . $extension . '</a></div>';
    }
        
    echo '</div>
        <p><a href="/pecl/">浏览所有 PECL 扩展包</a></p>
    </div>
    
    <div class="section">
        <h2>特定扩展源码</h2>
        <div class="version-list">';
        
    // 显示特定扩展
    foreach ($config['extensions'] as $extension => $extConfig) {
        echo '<div class="version-item"><a href="/extensions/' . $extension . '/">' . $extension . '</a></div>';
    }
        
    echo '</div>
        <p><a href="/extensions/">浏览所有特定扩展源码</a></p>
    </div>
    
    <div class="section">
        <h2>Composer 包</h2>
        <div class="version-list">';
        
    // 显示 Composer 版本
    foreach ($config['composer']['versions'] as $version) {
        echo '<div class="version-item"><a href="/composer/composer-' . $version . '.phar">Composer ' . $version . '</a></div>';
    }
        
    echo '</div>
        <p><a href="/composer/">浏览所有 Composer 包</a></p>
    </div>
    
    <div class="section">
        <h2>API 接口</h2>
        <ul>
            <li><a href="/api/status.json">镜像状态</a></li>
            <li><a href="/api/php.json">PHP 源码包列表</a></li>
            <li><a href="/api/pecl.json">PECL 扩展包列表</a></li>
            <li><a href="/api/extensions.json">特定扩展源码列表</a></li>
            <li><a href="/api/composer.json">Composer 包列表</a></li>
        </ul>
    </div>
    
    <div class="section">
        <h2>使用说明</h2>
        <p>在 PVM 配置文件中添加以下镜像配置：</p>
        <pre>
// 编辑 ~/.pvm/config/mirrors.php
return [
    \'php\' => [
        \'official\' => \'https://www.php.net/distributions\',
        \'mirrors\' => [
            \'local\' => \'' . $config['server']['public_url'] . '/php\',
        ],
        \'default\' => \'local\',
    ],
    // 其他配置...
];
        </pre>
    </div>
    
    <footer style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; color: #777;">
        <p>PVM 镜像应用 &copy; ' . date('Y') . '</p>
    </footer>
</body>
</html>';
}

/**
 * 处理文件下载请求
 */
function handleFileRequest($requestPath) {
    // 解析请求路径
    $path = parse_url($requestPath, PHP_URL_PATH);
    
    // 移除前导斜杠
    $path = ltrim($path, '/');
    
    // 检查路径是否为 API 请求
    if (strpos($path, 'api/') === 0) {
        handleApiRequest($path);
        return;
    }
    
    // 构建文件路径
    $filePath = ROOT_DIR . '/data/' . $path;
    
    // 如果是目录，显示目录列表
    if (is_dir($filePath)) {
        showDirectoryListing($path, $filePath);
        return;
    }
    
    // 如果文件不存在，返回 404
    if (!file_exists($filePath)) {
        header('HTTP/1.0 404 Not Found');
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>404 Not Found</title>
</head>
<body>
    <h1>404 Not Found</h1>
    <p>The requested file was not found on this server.</p>
    <p><a href="/">返回首页</a></p>
</body>
</html>';
        return;
    }
    
    // 发送文件
    $mimeTypes = [
        'tar.gz' => 'application/gzip',
        'tgz' => 'application/gzip',
        'phar' => 'application/octet-stream',
    ];
    
    $extension = '';
    if (preg_match('/\.([^.]+)$/', $filePath, $matches)) {
        $extension = $matches[1];
    } elseif (preg_match('/\.tar\.gz$/', $filePath)) {
        $extension = 'tar.gz';
    }
    
    $contentType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';
    
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));
    
    readfile($filePath);
}

/**
 * 显示目录列表
 */
function showDirectoryListing($path, $filePath) {
    // 获取目录内容
    $files = scandir($filePath);
    
    // 过滤掉 . 和 ..
    $files = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..';
    });
    
    // 构建面包屑导航
    $breadcrumbs = [];
    $parts = explode('/', $path);
    $currentPath = '';
    
    foreach ($parts as $part) {
        if (empty($part)) continue;
        
        $currentPath .= $part . '/';
        $breadcrumbs[] = [
            'name' => $part,
            'path' => '/' . $currentPath,
        ];
    }
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>目录列表: /' . $path . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        h1 { color: #333; }
        .breadcrumb { margin-bottom: 20px; }
        .breadcrumb a { color: #0066cc; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>目录列表: /' . $path . '</h1>
    
    <div class="breadcrumb">
        <a href="/">首页</a> / ';
    
    foreach ($breadcrumbs as $index => $crumb) {
        echo '<a href="' . $crumb['path'] . '">' . $crumb['name'] . '</a>';
        
        if ($index < count($breadcrumbs) - 1) {
            echo ' / ';
        }
    }
    
    echo '</div>
    
    <table>
        <tr>
            <th>文件名</th>
            <th>大小</th>
            <th>修改时间</th>
        </tr>';
    
    // 如果不是根目录，添加返回上级目录的链接
    if (!empty($path)) {
        echo '<tr>
            <td><a href="/' . dirname($path) . '">../</a></td>
            <td>-</td>
            <td>-</td>
        </tr>';
    }
    
    // 显示目录和文件
    foreach ($files as $file) {
        $fullPath = $filePath . '/' . $file;
        $isDir = is_dir($fullPath);
        $size = $isDir ? '-' : formatSize(filesize($fullPath));
        $modTime = date('Y-m-d H:i:s', filemtime($fullPath));
        
        echo '<tr>
            <td><a href="/' . $path . '/' . $file . '">' . $file . ($isDir ? '/' : '') . '</a></td>
            <td>' . $size . '</td>
            <td>' . $modTime . '</td>
        </tr>';
    }
    
    echo '</table>
</body>
</html>';
}

/**
 * 处理 API 请求
 */
function handleApiRequest($path) {
    // 设置内容类型为 JSON
    header('Content-Type: application/json');
    
    // 解析 API 路径
    $apiPath = substr($path, 4); // 移除 'api/'
    $apiPath = rtrim($apiPath, '.json');
    
    // 根据 API 路径返回不同的数据
    switch ($apiPath) {
        case 'status':
            echo json_encode(getMirrorStatus());
            break;
        case 'php':
            echo json_encode(getPhpList());
            break;
        case 'pecl':
            echo json_encode(getPeclList());
            break;
        case 'extensions':
            echo json_encode(getExtensionsList());
            break;
        case 'composer':
            echo json_encode(getComposerList());
            break;
        default:
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'API not found']);
            break;
    }
}

/**
 * 获取镜像状态
 */
function getMirrorStatus() {
    $phpDir = ROOT_DIR . '/data/php';
    $peclDir = ROOT_DIR . '/data/pecl';
    $extensionsDir = ROOT_DIR . '/data/extensions';
    $composerDir = ROOT_DIR . '/data/composer';
    
    $phpFiles = is_dir($phpDir) ? glob($phpDir . '/*.tar.gz') : [];
    $peclFiles = is_dir($peclDir) ? glob($peclDir . '/*.tgz') : [];
    
    $extensionFiles = [];
    if (is_dir($extensionsDir)) {
        $extensionDirs = glob($extensionsDir . '/*', GLOB_ONLYDIR);
        foreach ($extensionDirs as $dir) {
            $files = glob($dir . '/*.tar.gz');
            $extensionFiles = array_merge($extensionFiles, $files);
        }
    }
    
    $composerFiles = is_dir($composerDir) ? glob($composerDir . '/*.phar') : [];
    
    $allFiles = array_merge($phpFiles, $peclFiles, $extensionFiles, $composerFiles);
    $totalSize = 0;
    $lastUpdate = 0;
    
    foreach ($allFiles as $file) {
        $totalSize += filesize($file);
        $mtime = filemtime($file);
        if ($mtime > $lastUpdate) {
            $lastUpdate = $mtime;
        }
    }
    
    return [
        'php_files' => count($phpFiles),
        'pecl_files' => count($peclFiles),
        'extension_files' => count($extensionFiles),
        'composer_files' => count($composerFiles),
        'total_files' => count($allFiles),
        'total_size' => $totalSize,
        'last_update' => $lastUpdate,
    ];
}

/**
 * 获取 PHP 列表
 */
function getPhpList() {
    $phpDir = ROOT_DIR . '/data/php';
    $files = is_dir($phpDir) ? glob($phpDir . '/*.tar.gz') : [];
    
    $result = [];
    foreach ($files as $file) {
        $filename = basename($file);
        if (preg_match('/php-([0-9.]+)\.tar\.gz/', $filename, $matches)) {
            $version = $matches[1];
            $majorVersion = explode('.', $version)[0] . '.' . explode('.', $version)[1];
            
            if (!isset($result[$majorVersion])) {
                $result[$majorVersion] = [];
            }
            
            $result[$majorVersion][] = [
                'version' => $version,
                'filename' => $filename,
                'size' => filesize($file),
                'url' => '/php/' . $filename,
            ];
        }
    }
    
    return $result;
}

/**
 * 获取 PECL 列表
 */
function getPeclList() {
    $peclDir = ROOT_DIR . '/data/pecl';
    $files = is_dir($peclDir) ? glob($peclDir . '/*.tgz') : [];
    
    $result = [];
    foreach ($files as $file) {
        $filename = basename($file);
        if (preg_match('/([a-zA-Z0-9_]+)-([0-9.]+)\.tgz/', $filename, $matches)) {
            $extension = $matches[1];
            $version = $matches[2];
            
            if (!isset($result[$extension])) {
                $result[$extension] = [];
            }
            
            $result[$extension][] = [
                'version' => $version,
                'filename' => $filename,
                'size' => filesize($file),
                'url' => '/pecl/' . $filename,
            ];
        }
    }
    
    return $result;
}

/**
 * 获取扩展列表
 */
function getExtensionsList() {
    $extensionsDir = ROOT_DIR . '/data/extensions';
    $result = [];
    
    if (is_dir($extensionsDir)) {
        $extensionDirs = glob($extensionsDir . '/*', GLOB_ONLYDIR);
        
        foreach ($extensionDirs as $dir) {
            $extension = basename($dir);
            $files = glob($dir . '/*.tar.gz');
            
            $result[$extension] = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                
                $result[$extension][] = [
                    'filename' => $filename,
                    'size' => filesize($file),
                    'url' => '/extensions/' . $extension . '/' . $filename,
                ];
            }
        }
    }
    
    return $result;
}

/**
 * 获取 Composer 列表
 */
function getComposerList() {
    $composerDir = ROOT_DIR . '/data/composer';
    $files = is_dir($composerDir) ? glob($composerDir . '/*.phar') : [];
    
    $result = [];
    foreach ($files as $file) {
        $filename = basename($file);
        if (preg_match('/composer-([0-9.]+)\.phar/', $filename, $matches)) {
            $version = $matches[1];
            
            $result[] = [
                'version' => $version,
                'filename' => $filename,
                'size' => filesize($file),
                'url' => '/composer/' . $filename,
            ];
        }
    }
    
    return $result;
}

/**
 * 格式化文件大小
 */
function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    
    return round($size, 2) . ' ' . $units[$i];
}
