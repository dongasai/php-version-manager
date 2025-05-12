<?php

// 读取配置文件
$configFile = 'config/supported_versions/x86_64.php';
$content = file_get_contents($configFile);

// 查找所有需要添加PHP 5.x支持的位置
$pattern = "/('7\.1' => '(full|partial|none)',)/";
$replacement = "$1\n            '5.6' => '$2',\n            '5.5' => '$2',\n            '5.4' => '$2',";

// 替换内容
$newContent = preg_replace($pattern, $replacement, $content);

// 写入文件
file_put_contents($configFile, $newContent);

echo "PHP 5.x support added to x86_64.php\n";

// 读取ARM64配置文件
$configFile = 'config/supported_versions/aarch64.php';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    
    // 替换内容
    $newContent = preg_replace($pattern, $replacement, $content);
    
    // 写入文件
    file_put_contents($configFile, $newContent);
    
    echo "PHP 5.x support added to aarch64.php\n";
}

echo "Done!\n";
