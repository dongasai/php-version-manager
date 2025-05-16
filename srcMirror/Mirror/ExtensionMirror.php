<?php

namespace Mirror\Mirror;

use Mirror\Utils\FileUtils;

/**
 * 扩展镜像类
 */
class ExtensionMirror
{
    /**
     * 同步特定扩展的GitHub源码
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function sync(array $config)
    {
        echo "同步特定扩展的 GitHub 源码...\n";
        
        // 遍历扩展
        foreach ($config as $extension => $extConfig) {
            $source = $extConfig['source'];
            $pattern = $extConfig['pattern'];
            $dataDir = ROOT_DIR . '/data/extensions/' . $extension;
            
            // 确保目录存在
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            // 遍历版本
            foreach ($extConfig['versions'] as $version) {
                $filename = str_replace('{version}', $version, $pattern);
                $sourceUrl = $source . '/' . $filename;
                $targetFile = $dataDir . '/' . $filename;
                
                // 如果文件不存在，则下载
                if (!file_exists($targetFile)) {
                    echo "  下载 $extension $version: $sourceUrl\n";
                    FileUtils::downloadFile($sourceUrl, $targetFile);
                } else {
                    echo "  $extension $version 已存在\n";
                }
            }
        }
        
        return true;
    }

    /**
     * 清理特定扩展的GitHub源码
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function clean(array $config)
    {
        echo "清理特定扩展的 GitHub 源码...\n";
        
        // 实现清理逻辑
        // ...
        
        return true;
    }
}
