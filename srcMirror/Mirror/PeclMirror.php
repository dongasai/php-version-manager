<?php

namespace Mirror\Mirror;

use Mirror\Utils\FileUtils;

/**
 * PECL镜像类
 */
class PeclMirror
{
    /**
     * 同步PECL扩展包
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function sync(array $config)
    {
        echo "同步 PECL 扩展包...\n";

        $source = $config['source'];
        $pattern = $config['pattern'];

        // 获取数据目录
        $configManager = new \Mirror\Config\ConfigManager();
        $baseDir = $configManager->getDataDir();
        $dataDir = $baseDir . '/pecl';  // 强制添加pecl子目录

        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        // 遍历扩展
        foreach ($config['extensions'] as $extension => $versionRange) {
            list($minVersion, $maxVersion) = $versionRange;

            // 获取版本列表
            $versions = FileUtils::getVersionRange($minVersion, $maxVersion);

            foreach ($versions as $version) {
                $filename = str_replace(['{extension}', '{version}'], [$extension, $version], $pattern);
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
     * 清理PECL扩展包
     *
     * @param array $config 配置
     * @return bool 是否成功
     */
    public function clean(array $config)
    {
        echo "清理 PECL 扩展包...\n";

        // 实现清理逻辑
        // ...

        return true;
    }
}
