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

            // 设置下载选项
            $downloadOptions = [
                'min_size' => 1024 * 1024 * 5,  // PHP 源码包至少 5MB
                'max_retries' => 3,
                'timeout' => 600,               // PHP 源码包较大，增加超时时间
                'verify_content' => true,
                'expected_type' => 'tar.gz'
            ];

            try {
                $success = FileUtils::downloadFile($sourceUrl, $targetFile, $downloadOptions);
                if ($success) {
                    // 额外验证 PHP 源码包
                    if ($this->validatePhpSourcePackage($targetFile, $version)) {
                        echo "  PHP $version 下载并验证完成\n";
                        return true;
                    } else {
                        echo "  错误: PHP $version 源码包验证失败\n";
                        if (file_exists($targetFile)) {
                            unlink($targetFile);
                        }
                        return false;
                    }
                } else {
                    echo "  错误: PHP $version 下载失败\n";
                    return false;
                }
            } catch (Exception $e) {
                echo "  错误: PHP $version 下载失败: " . $e->getMessage() . "\n";
                return false;
            }
        } else {
            // 验证已存在的文件
            if ($this->validateExistingFile($targetFile, $version)) {
                echo "  PHP $version 已存在且验证通过\n";
                return true;
            } else {
                echo "  PHP $version 文件损坏，重新下载\n";
                unlink($targetFile);
                return $this->downloadVersion($source, $pattern, $dataDir, $version);
            }
        }
    }

    /**
     * 验证 PHP 源码包
     *
     * @param string $filePath 文件路径
     * @param string $version 版本号
     * @return bool 是否验证通过
     */
    private function validatePhpSourcePackage($filePath, $version)
    {
        // 检查是否为有效的 tar.gz 文件
        if (!$this->isValidTarGz($filePath)) {
            return false;
        }

        // 尝试列出压缩包内容
        try {
            $output = [];
            $returnCode = 0;
            exec("tar -tzf " . escapeshellarg($filePath) . " 2>/dev/null | head -20", $output, $returnCode);

            if ($returnCode !== 0) {
                echo "  验证失败: 无法读取 tar.gz 文件内容\n";
                return false;
            }

            // 检查是否包含 PHP 源码的关键文件
            $hasConfigureScript = false;
            $hasMainDirectory = false;
            $expectedDir = "php-$version/";

            foreach ($output as $line) {
                if (strpos($line, $expectedDir) === 0) {
                    $hasMainDirectory = true;
                }
                if (strpos($line, 'configure') !== false) {
                    $hasConfigureScript = true;
                }
            }

            if (!$hasMainDirectory) {
                echo "  验证失败: 压缩包不包含预期的目录结构\n";
                return false;
            }

            if (!$hasConfigureScript) {
                echo "  验证失败: 压缩包不包含 configure 脚本\n";
                return false;
            }

            return true;
        } catch (Exception $e) {
            echo "  验证失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 检查是否为有效的 tar.gz 文件
     *
     * @param string $filePath 文件路径
     * @return bool 是否有效
     */
    private function isValidTarGz($filePath)
    {
        // 检查文件头
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 3);
        fclose($handle);

        // Gzip 文件的魔数是 1f 8b 08
        return substr($header, 0, 2) === "\x1f\x8b";
    }

    /**
     * 验证已存在的文件
     *
     * @param string $filePath 文件路径
     * @param string $version 版本号
     * @return bool 是否验证通过
     */
    private function validateExistingFile($filePath, $version)
    {
        // 检查文件大小
        $fileSize = filesize($filePath);
        if ($fileSize < 1024 * 1024 * 5) { // 小于 5MB
            return false;
        }

        // 检查文件格式
        return $this->validatePhpSourcePackage($filePath, $version);
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
