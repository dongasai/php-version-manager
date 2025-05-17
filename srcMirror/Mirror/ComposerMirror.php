<?php

namespace Mirror\Mirror;

use Mirror\Utils\FileUtils;

/**
 * Composer镜像类
 */
class ComposerMirror
{
    /**
     * 同步Composer包
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function sync(array $config)
    {
        echo "同步 Composer 包...\n";
        
        $source = $config['source'];
        $pattern = $config['pattern'];
        $baseDir = $config['data_dir'] ?? ROOT_DIR . '/data';
        $dataDir = $baseDir . '/composer';  // 强制添加composer子目录
        
        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        // 遍历版本
        foreach ($config['versions'] as $version) {
            $filename = str_replace('{version}', $version, $pattern);
            $sourceUrl = $source . '/' . $filename;
            $targetFile = $dataDir . '/' . $filename;
            
            // 如果文件不存在，则下载
            if (!file_exists($targetFile)) {
                echo "  下载 Composer $version: $sourceUrl\n";
                FileUtils::downloadFile($sourceUrl, $targetFile);
            } else {
                echo "  Composer $version 已存在\n";
            }
        }
        
        return true;
    }

    /**
     * 清理Composer包
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function clean(array $config)
    {
        echo "清理 Composer 包...\n";
        
        // 实现清理逻辑
        // ...
        
        return true;
    }
}
