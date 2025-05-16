<!DOCTYPE html>
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
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; color: #777; }
    </style>
</head>
<body>
    <h1>PVM 镜像站</h1>
    
    <div class="info">
        <p><strong>总文件数:</strong> <?= $status['total_files'] ?></p>
        <p><strong>总大小:</strong> <?= $formatSize($status['total_size']) ?></p>
        <p><strong>最后更新:</strong> <?= date('Y-m-d H:i:s', $status['last_update']) ?></p>
    </div>
    
    <div class="section">
        <h2>PHP 源码包</h2>
        <div class="version-list">
            <?php foreach ($config['php']['versions'] as $majorVersion => $versionRange): ?>
                <div class="version-item">
                    <a href="/php/?version=<?= $majorVersion ?>">PHP <?= $majorVersion ?></a>
                </div>
            <?php endforeach; ?>
        </div>
        <p><a href="/php/">浏览所有 PHP 源码包</a></p>
    </div>
    
    <div class="section">
        <h2>PECL 扩展包</h2>
        <div class="version-list">
            <?php foreach ($config['pecl']['extensions'] as $extension => $versionRange): ?>
                <div class="version-item">
                    <a href="/pecl/?extension=<?= $extension ?>"><?= $extension ?></a>
                </div>
            <?php endforeach; ?>
        </div>
        <p><a href="/pecl/">浏览所有 PECL 扩展包</a></p>
    </div>
    
    <div class="section">
        <h2>特定扩展源码</h2>
        <div class="version-list">
            <?php foreach ($config['extensions'] as $extension => $extConfig): ?>
                <div class="version-item">
                    <a href="/extensions/<?= $extension ?>/"><?= $extension ?></a>
                </div>
            <?php endforeach; ?>
        </div>
        <p><a href="/extensions/">浏览所有特定扩展源码</a></p>
    </div>
    
    <div class="section">
        <h2>Composer 包</h2>
        <div class="version-list">
            <?php foreach ($config['composer']['versions'] as $version): ?>
                <div class="version-item">
                    <a href="/composer/composer-<?= $version ?>.phar">Composer <?= $version ?></a>
                </div>
            <?php endforeach; ?>
        </div>
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
    'php' => [
        'official' => 'https://www.php.net/distributions',
        'mirrors' => [
            'local' => '<?= $config['server']['public_url'] ?>/php',
        ],
        'default' => 'local',
    ],
    // 其他配置...
];
        </pre>
    </div>
    
    <footer>
        <p>PVM 镜像应用 &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>
