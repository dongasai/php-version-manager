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

        $success = true;

        // 遍历扩展
        foreach ($config['extensions'] as $extension => $versionRange) {
            if (!$this->syncExtensionVersions($source, $pattern, $dataDir, $extension, $versionRange)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 同步指定PECL扩展
     *
     * @param array $config 配置
     * @param string $extensionName 指定扩展名
     * @return bool 是否成功
     */
    public function syncExtension(array $config, $extensionName)
    {
        echo "同步 PECL 指定扩展: $extensionName\n";

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

        // 检查扩展是否在配置中
        if (!isset($config['extensions'][$extensionName])) {
            echo "  错误: 扩展 $extensionName 不在配置的扩展列表中\n";
            echo "  可用扩展: " . implode(', ', array_keys($config['extensions'])) . "\n";
            return false;
        }

        $versionRange = $config['extensions'][$extensionName];
        return $this->syncExtensionVersions($source, $pattern, $dataDir, $extensionName, $versionRange);
    }

    /**
     * 同步扩展的所有版本
     *
     * @param string $source 源地址
     * @param string $pattern 文件名模式
     * @param string $dataDir 数据目录
     * @param string $extension 扩展名
     * @param array $versionRange 版本范围
     * @return bool 是否成功
     */
    private function syncExtensionVersions($source, $pattern, $dataDir, $extension, $versionRange)
    {
        list($minVersion, $maxVersion) = $versionRange;

        // 获取版本列表
        $versions = FileUtils::getVersionRange($minVersion, $maxVersion);

        $success = true;
        foreach ($versions as $version) {
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
        $filename = str_replace(['{extension}', '{version}'], [$extension, $version], $pattern);
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
