<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>目录列表: /<?= $path ?></title>
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
        footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; color: #777; }
    </style>
</head>
<body>
    <h1>目录列表: /<?= $path ?></h1>
    
    <div class="breadcrumb">
        <a href="/">首页</a> / 
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <a href="<?= $crumb['path'] ?>"><?= $crumb['name'] ?></a>
            <?= $index < count($breadcrumbs) - 1 ? ' / ' : '' ?>
        <?php endforeach; ?>
    </div>
    
    <table>
        <tr>
            <th>文件名</th>
            <th>大小</th>
            <th>修改时间</th>
        </tr>
        
        <?php if (!empty($path)): ?>
        <tr>
            <td><a href="/<?= dirname($path) ?>">../</a></td>
            <td>-</td>
            <td>-</td>
        </tr>
        <?php endif; ?>
        
        <?php foreach ($files as $file): ?>
            <?php 
                $fullPath = $filePath . '/' . $file;
                $isDir = is_dir($fullPath);
                $size = $isDir ? '-' : $formatSize(filesize($fullPath));
                $modTime = date('Y-m-d H:i:s', filemtime($fullPath));
            ?>
            <tr>
                <td><a href="/<?= $path ?>/<?= $file ?>"><?= $file ?><?= $isDir ? '/' : '' ?></a></td>
                <td><?= $size ?></td>
                <td><?= $modTime ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <footer>
        <p>PVM 镜像应用 &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>
