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

        // 获取数据目录
        $configManager = new \Mirror\Config\ConfigManager();
        $baseDir = $configManager->getDataDir();
        $dataDir = $baseDir . '/composer';  // 强制添加composer子目录

        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $success = true;

        // 遍历版本
        foreach ($config['versions'] as $version) {
            if (!$this->downloadVersion($source, $pattern, $dataDir, $version)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 同步指定版本的Composer包
     *
     * @param array $config 配置
     * @param string $version 指定版本
     * @return bool 是否成功
     */
    public function syncVersion(array $config, $version)
    {
        echo "同步 Composer 指定版本: $version\n";

        $source = $config['source'];
        $pattern = $config['pattern'];

        // 获取数据目录
        $configManager = new \Mirror\Config\ConfigManager();
        $baseDir = $configManager->getDataDir();
        $dataDir = $baseDir . '/composer';  // 强制添加composer子目录

        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        // 检查版本是否在配置的版本列表中
        if (!in_array($version, $config['versions'])) {
            echo "  警告: 版本 $version 不在配置的版本列表中，但仍尝试下载\n";
        }

        return $this->downloadVersion($source, $pattern, $dataDir, $version);
    }

    /**
     * 下载指定版本
     *
     * @param string $source 源地址
     * @param string $pattern 文件名模式
     * @param string $dataDir 数据目录
     * @param string $version 版本号
     * @return bool 是否成功
     */
    private function downloadVersion($source, $pattern, $dataDir, $version)
    {
        $filename = str_replace('{version}', $version, $pattern);
        $sourceUrl = $source . '/' . $filename;
        $targetFile = $dataDir . '/' . $filename;

        // 如果文件不存在，则下载
        if (!file_exists($targetFile)) {
            echo "  下载 Composer $version: $sourceUrl\n";
            try {
                FileUtils::downloadFile($sourceUrl, $targetFile);
                echo "  Composer $version 下载完成\n";
                return true;
            } catch (Exception $e) {
                echo "  错误: Composer $version 下载失败: " . $e->getMessage() . "\n";
                return false;
            }
        } else {
            echo "  Composer $version 已存在\n";
            return true;
        }
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
