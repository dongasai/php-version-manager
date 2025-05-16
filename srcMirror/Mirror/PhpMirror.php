<?php

namespace Mirror\Mirror;

use Mirror\Utils\FileUtils;

/**
 * PHP镜像类
 */
class PhpMirror
{
    /**
     * 同步PHP源码包
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function sync(array $config)
    {
        echo "同步 PHP 源码包...\n";
        
        $source = $config['source'];
        $pattern = $config['pattern'];
        $dataDir = ROOT_DIR . '/data/php';
        
        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        // 遍历版本
        foreach ($config['versions'] as $majorVersion => $versionRange) {
            list($minVersion, $maxVersion) = $versionRange;
            
            // 获取版本列表
            $versions = FileUtils::getVersionRange($minVersion, $maxVersion);
            
            foreach ($versions as $version) {
                $filename = str_replace('{version}', $version, $pattern);
                $sourceUrl = $source . '/' . $filename;
                $targetFile = $dataDir . '/' . $filename;
                
                // 如果文件不存在，则下载
                if (!file_exists($targetFile)) {
                    echo "  下载 PHP $version: $sourceUrl\n";
                    FileUtils::downloadFile($sourceUrl, $targetFile);
                } else {
                    echo "  PHP $version 已存在\n";
                }
            }
        }
        
        return true;
    }

    /**
     * 清理PHP源码包
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function clean(array $config)
    {
        echo "清理 PHP 源码包...\n";
        
        // 实现清理逻辑
        // ...
        
        return true;
    }
}
