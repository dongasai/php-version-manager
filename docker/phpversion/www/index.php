<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PVM PHP 版本测试</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .version-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }
        .version-item {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            flex: 1 0 200px;
            text-align: center;
        }
        .version-item a {
            display: block;
            text-decoration: none;
            color: #0066cc;
            font-weight: bold;
        }
        .version-item a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>PVM PHP 版本测试</h1>
    
    <p>这个页面展示了使用 PVM 构建的不同 PHP 版本的容器。点击下面的链接查看每个版本的 PHP 信息。</p>
    
    <div class="version-list">
        <div class="version-item">
            <h3>PHP 7.1</h3>
            <a href="/php71/info.php">查看 PHP 信息</a>
        </div>
        
        <div class="version-item">
            <h3>PHP 7.2</h3>
            <a href="/php72/info.php">查看 PHP 信息</a>
        </div>
        
        <div class="version-item">
            <h3>PHP 7.3</h3>
            <a href="/php73/info.php">查看 PHP 信息</a>
        </div>
        
        <div class="version-item">
            <h3>PHP 7.4</h3>
            <a href="/php74/info.php">查看 PHP 信息</a>
        </div>
        
        <div class="version-item">
            <h3>PHP 8.0</h3>
            <a href="/php80/info.php">查看 PHP 信息</a>
        </div>
        
        <div class="version-item">
            <h3>PHP 8.1</h3>
            <a href="/php81/info.php">查看 PHP 信息</a>
        </div>
        
        <div class="version-item">
            <h3>PHP 8.2</h3>
            <a href="/php82/info.php">查看 PHP 信息</a>
        </div>
        
        <div class="version-item">
            <h3>PHP 8.3</h3>
            <a href="/php83/info.php">查看 PHP 信息</a>
        </div>
    </div>
    
    <p>当前PHP版本: <?php echo PHP_VERSION; ?></p>
    <p>当前时间: <?php echo date('Y-m-d H:i:s'); ?></p>
    
    <footer>
        <p>由 <a href="https://github.com/dongasai/php-version-manager" target="_blank">PHP Version Manager (PVM)</a> 提供支持</p>
    </footer>
</body>
</html>
