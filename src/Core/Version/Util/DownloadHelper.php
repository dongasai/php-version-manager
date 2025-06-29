<?php

namespace VersionManager\Core\Version\Util;

use VersionManager\Core\Logger\FileLogger;

/**
 * 下载助手类
 *
 * 提供文件下载相关的通用功能
 */
class DownloadHelper
{
    /**
     * 下载文件
     *
     * @param string|array $url 文件URL或URL数组
     * @param string $destination 目标路径
     * @return bool
     */
    public static function downloadFile($url, $destination)
    {
        // 调试信息
        FileLogger::info("downloadFile被调用，URL类型: " . gettype($url), 'DEBUG');
        if (is_array($url)) {
            FileLogger::info("URL数组内容: " . json_encode($url), 'DEBUG');
        } else {
            FileLogger::info("URL字符串: " . $url, 'DEBUG');
        }

        // 如果传入的是数组，按优先级尝试下载
        if (is_array($url)) {
            return self::downloadFileWithFallback($url, $destination);
        }

        // 确保URL是字符串
        if (!is_string($url)) {
            throw new \Exception("downloadFile收到非字符串URL: " . gettype($url) . " - " . var_export($url, true));
        }

        return self::downloadSingleFile($url, $destination);
    }

    /**
     * 使用多个URL按优先级尝试下载
     *
     * @param array $urls URL数组
     * @param string $destination 目标路径
     * @return bool
     */
    public static function downloadFileWithFallback(array $urls, $destination)
    {
        $lastException = null;
        $attemptCount = 0;

        foreach ($urls as $url) {
            $attemptCount++;

            try {
                // 确保URL是字符串
                if (!is_string($url)) {
                    throw new \Exception("无效的URL格式: " . gettype($url));
                }

                echo "尝试从源 {$attemptCount} 下载: " . parse_url($url, PHP_URL_HOST) . "\n";

                // 直接调用单个URL的下载逻辑，避免递归
                $success = self::downloadSingleFile($url, $destination);

                if ($success) {
                    if ($attemptCount > 1) {
                        echo "下载成功！\n";
                    }
                    return true;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                echo "下载失败: " . $e->getMessage() . "\n";

                // 如果还有其他URL可以尝试，显示切换信息
                if ($attemptCount < count($urls)) {
                    echo "正在切换到下一个源...\n";
                }

                // 继续尝试下一个URL
                continue;
            }
        }

        // 所有URL都失败了
        if ($lastException) {
            throw new \Exception("所有下载源都失败了，最后一个错误: " . $lastException->getMessage());
        } else {
            throw new \Exception("所有下载源都失败了");
        }
    }

    /**
     * 下载单个文件（仅处理字符串URL）
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return bool
     */
    public static function downloadSingleFile($url, $destination)
    {
        // 确保URL是字符串
        if (!is_string($url)) {
            throw new \Exception("downloadSingleFile只接受字符串URL，收到: " . gettype($url));
        }

        $command = "curl -L -o " . escapeshellarg($destination) . " " . escapeshellarg($url);
        $output = [];
        $returnCode = 0;

        // 记录命令执行
        FileLogger::info("执行下载命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        exec($command . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("下载命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("下载文件失败: " . implode("\n", $output));
        } else {
            FileLogger::info("下载命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        return true;
    }

    /**
     * 使用进度条下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return bool
     */
    public static function downloadFileWithProgress($url, $destination)
    {
        $command = "curl -L --progress-bar -o " . escapeshellarg($destination) . " " . escapeshellarg($url);
        $returnCode = 0;

        // 记录命令执行
        FileLogger::info("执行带进度条的下载命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        passthru($command, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("下载命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            throw new \Exception("下载文件失败");
        } else {
            FileLogger::info("下载命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        return true;
    }

    /**
     * 检查URL是否可访问
     *
     * @param string $url URL地址
     * @return bool
     */
    public static function isUrlAccessible($url)
    {
        $command = "curl -I -s -o /dev/null -w '%{http_code}' " . escapeshellarg($url);
        $output = [];
        $returnCode = 0;

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            return false;
        }

        $httpCode = isset($output[0]) ? (int)$output[0] : 0;
        return $httpCode >= 200 && $httpCode < 400;
    }

    /**
     * 获取URL的文件大小
     *
     * @param string $url URL地址
     * @return int|false 文件大小（字节），失败返回false
     */
    public static function getUrlFileSize($url)
    {
        $command = "curl -I -s " . escapeshellarg($url) . " | grep -i content-length | awk '{print $2}' | tr -d '\r'";
        $output = [];
        $returnCode = 0;

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || empty($output)) {
            return false;
        }

        $size = (int)trim($output[0]);
        return $size > 0 ? $size : false;
    }

    /**
     * 获取URL的文件名
     *
     * @param string $url URL地址
     * @return string 文件名
     */
    public static function getUrlFileName($url)
    {
        $parsedUrl = parse_url($url);
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        
        if (empty($path) || $path === '/') {
            return 'download';
        }
        
        return basename($path);
    }

    /**
     * 验证下载的文件
     *
     * @param string $file 文件路径
     * @param string $expectedHash 期望的哈希值（可选）
     * @param string $hashAlgorithm 哈希算法（默认md5）
     * @return bool
     */
    public static function validateDownloadedFile($file, $expectedHash = null, $hashAlgorithm = 'md5')
    {
        if (!file_exists($file)) {
            return false;
        }

        // 检查文件大小
        if (filesize($file) === 0) {
            return false;
        }

        // 如果提供了期望的哈希值，则验证
        if ($expectedHash !== null) {
            $actualHash = hash_file($hashAlgorithm, $file);
            return $actualHash === $expectedHash;
        }

        return true;
    }

    /**
     * 计算文件哈希值
     *
     * @param string $file 文件路径
     * @param string $algorithm 哈希算法（默认md5）
     * @return string|false 哈希值，失败返回false
     */
    public static function calculateFileHash($file, $algorithm = 'md5')
    {
        if (!file_exists($file)) {
            return false;
        }

        return hash_file($algorithm, $file);
    }

    /**
     * 下载并验证文件
     *
     * @param string|array $url 文件URL或URL数组
     * @param string $destination 目标路径
     * @param string $expectedHash 期望的哈希值（可选）
     * @param string $hashAlgorithm 哈希算法（默认md5）
     * @return bool
     */
    public static function downloadAndValidateFile($url, $destination, $expectedHash = null, $hashAlgorithm = 'md5')
    {
        // 下载文件
        $success = self::downloadFile($url, $destination);
        
        if (!$success) {
            return false;
        }

        // 验证文件
        return self::validateDownloadedFile($destination, $expectedHash, $hashAlgorithm);
    }

    /**
     * 获取下载速度统计
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return array 下载统计信息
     */
    public static function downloadWithStats($url, $destination)
    {
        $startTime = microtime(true);
        $startSize = file_exists($destination) ? filesize($destination) : 0;

        $success = self::downloadFile($url, $destination);

        $endTime = microtime(true);
        $endSize = file_exists($destination) ? filesize($destination) : 0;

        $duration = $endTime - $startTime;
        $downloadedBytes = $endSize - $startSize;
        $speed = $duration > 0 ? $downloadedBytes / $duration : 0;

        return [
            'success' => $success,
            'duration' => $duration,
            'downloaded_bytes' => $downloadedBytes,
            'speed_bytes_per_second' => $speed,
            'speed_human_readable' => FileHelper::getHumanReadableFileSize($speed) . '/s'
        ];
    }

    /**
     * 断点续传下载
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return bool
     */
    public static function resumeDownload($url, $destination)
    {
        $command = "curl -L -C - -o " . escapeshellarg($destination) . " " . escapeshellarg($url);
        $output = [];
        $returnCode = 0;

        // 记录命令执行
        FileLogger::info("执行断点续传下载命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        exec($command . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("断点续传下载命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("断点续传下载失败: " . implode("\n", $output));
        } else {
            FileLogger::info("断点续传下载命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        return true;
    }
}
