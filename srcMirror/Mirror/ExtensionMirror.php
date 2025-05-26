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

        // 获取数据目录
        $configManager = new \Mirror\Config\ConfigManager();
        $baseDir = $configManager->getDataDir();

        $success = true;

        // 遍历扩展
        foreach ($config as $extension => $extConfig) {
            if (!$this->syncExtension($baseDir, $extension, $extConfig)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 同步单个扩展
     *
     * @param string $baseDir 基础数据目录
     * @param string $extension 扩展名
     * @param array $extConfig 扩展配置
     * @return bool 是否成功
     */
    private function syncExtension($baseDir, $extension, $extConfig)
    {
        $source = $extConfig['source'];
        $pattern = $extConfig['pattern'];
        $dataDir = $baseDir . '/extensions/' . $extension; // 强制添加二级目录结构

        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $success = true;

        // 遍历版本
        foreach ($extConfig['versions'] as $version) {
            if (!$this->downloadExtensionVersion($source, $pattern, $dataDir, $extension, $version)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 下载指定扩展版本
     *
     * @param string $source 源地址
     * @param string $pattern 文件名模式
     * @param string $dataDir 数据目录
     * @param string $extension 扩展名
     * @param string $version 版本号
     * @return bool 是否成功
     */
    private function downloadExtensionVersion($source, $pattern, $dataDir, $extension, $version)
    {
        $filename = str_replace('{version}', $version, $pattern);
        $sourceUrl = $source . '/' . $filename;
        $targetFile = $dataDir . '/' . $filename;

        // 如果文件不存在，则下载
        if (!file_exists($targetFile)) {
            echo "  下载 $extension $version: $sourceUrl\n";
            try {
                FileUtils::downloadFile($sourceUrl, $targetFile);
                echo "  $extension $version 下载完成\n";
                return true;
            } catch (Exception $e) {
                echo "  错误: $extension $version 下载失败: " . $e->getMessage() . "\n";
                return false;
            }
        } else {
            echo "  $extension $version 已存在\n";
            return true;
        }
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
