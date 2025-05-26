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

        // 获取数据目录
        $configManager = new \Mirror\Config\ConfigManager();
        $baseDir = $configManager->getDataDir();
        $dataDir = $baseDir . '/php';  // 强制添加php子目录

        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $success = true;

        // 遍历版本
        foreach ($config['versions'] as $majorVersion => $versionRange) {
            list($minVersion, $maxVersion) = $versionRange;

            // 获取版本列表
            $versions = FileUtils::getVersionRange($minVersion, $maxVersion);

            foreach ($versions as $version) {
                if (!$this->downloadVersion($source, $pattern, $dataDir, $version)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * 同步指定版本的PHP源码包
     *
     * @param array $config 配置
     * @param string $majorVersion 指定主版本 (如 8.3)
     * @return bool 是否成功
     */
    public function syncVersion(array $config, $majorVersion)
    {
        echo "同步 PHP 指定版本: $majorVersion\n";

        $source = $config['source'];
        $pattern = $config['pattern'];

        // 获取数据目录
        $configManager = new \Mirror\Config\ConfigManager();
        $baseDir = $configManager->getDataDir();
        $dataDir = $baseDir . '/php';  // 强制添加php子目录

        // 确保目录存在
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        // 检查版本是否在配置中
        if (!isset($config['versions'][$majorVersion])) {
            echo "  错误: 版本 $majorVersion 不在配置的版本列表中\n";
            echo "  可用版本: " . implode(', ', array_keys($config['versions'])) . "\n";
            return false;
        }

        $versionRange = $config['versions'][$majorVersion];
        list($minVersion, $maxVersion) = $versionRange;

        // 获取版本列表
        $versions = FileUtils::getVersionRange($minVersion, $maxVersion);

        $success = true;
        foreach ($versions as $version) {
            if (!$this->downloadVersion($source, $pattern, $dataDir, $version)) {
                $success = false;
            }
        }

        return $success;
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
            echo "  下载 PHP $version: $sourceUrl\n";
            try {
                FileUtils::downloadFile($sourceUrl, $targetFile);
                echo "  PHP $version 下载完成\n";
                return true;
            } catch (Exception $e) {
                echo "  错误: PHP $version 下载失败: " . $e->getMessage() . "\n";
                return false;
            }
        } else {
            echo "  PHP $version 已存在\n";
            return true;
        }
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
