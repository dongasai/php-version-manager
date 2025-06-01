<?php

namespace VersionManager\Core\Download;

use VersionManager\Core\Cache\CacheManager;
use VersionManager\Core\Security\SignatureVerifier;
use VersionManager\Core\Security\PermissionManager;
use VersionManager\Core\Download\IntegrityVerifier;
use VersionManager\Core\Logger\FileLogger;

/**
 * 下载管理类
 *
 * 负责管理文件下载，支持多线程下载和缓存
 */
class DownloadManager
{
    /**
     * 缓存管理器
     *
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * 签名验证器
     *
     * @var SignatureVerifier
     */
    private $signatureVerifier;

    /**
     * 权限管理器
     *
     * @var PermissionManager
     */
    private $permissionManager;

    /**
     * 是否使用缓存
     *
     * @var bool
     */
    private $useCache = true;

    /**
     * 是否使用多线程下载
     *
     * @var bool
     */
    private $useMultiThread = false;

    /**
     * 是否验证签名
     *
     * @var bool
     */
    private $verifySignature = true;

    /**
     * 线程数
     *
     * @var int
     */
    private $threadCount = 4;

    /**
     * 是否显示进度
     *
     * @var bool
     */
    private $showProgress = true;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->cacheManager = new CacheManager();
        $this->signatureVerifier = new SignatureVerifier();
        $this->permissionManager = new PermissionManager();
    }

    /**
     * 设置是否使用缓存
     *
     * @param bool $useCache 是否使用缓存
     * @return $this
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
        return $this;
    }

    /**
     * 设置是否使用多线程下载
     *
     * @param bool $useMultiThread 是否使用多线程下载
     * @return $this
     */
    public function setUseMultiThread($useMultiThread)
    {
        $this->useMultiThread = $useMultiThread;
        return $this;
    }

    /**
     * 设置是否验证签名
     *
     * @param bool $verifySignature 是否验证签名
     * @return $this
     */
    public function setVerifySignature($verifySignature)
    {
        $this->verifySignature = $verifySignature;
        $this->signatureVerifier->setEnabled($verifySignature);
        return $this;
    }

    /**
     * 设置线程数
     *
     * @param int $threadCount 线程数
     * @return $this
     */
    public function setThreadCount($threadCount)
    {
        $this->threadCount = max(1, $threadCount);
        return $this;
    }

    /**
     * 设置是否显示进度
     *
     * @param bool $showProgress 是否显示进度
     * @return $this
     */
    public function setShowProgress($showProgress)
    {
        $this->showProgress = $showProgress;
        return $this;
    }

    /**
     * 下载文件
     *
     * @param string|array $url 文件URL或URL数组（按优先级排序）
     * @param string $destination 目标路径
     * @param array $options 下载选项
     * @return bool 是否下载成功
     * @throws \Exception 下载失败时抛出异常
     */
    public function download($url, $destination, array $options = [])
    {
        $startTime = microtime(true);

        // 检查用户权限
        $this->permissionManager->checkUserPermission();

        // 如果传入的是数组，按优先级尝试下载
        if (is_array($url)) {
            return $this->downloadWithFallback($url, $destination, $options);
        }

        // 记录下载开始
        $fileSize = $this->getFileSize($url);
        FileLogger::logDownloadStart($url, $destination, $fileSize ?: 0);

        // 检查缓存
        if ($this->useCache) {
            $cacheFile = $this->cacheManager->getDownloadCache($url, true);
            if ($cacheFile !== null) {
                // 从缓存复制文件
                if (copy($cacheFile, $destination)) {
                    $duration = microtime(true) - $startTime;
                    $actualSize = filesize($destination);

                    if ($this->showProgress) {
                        echo "从缓存获取文件: " . basename($url) . PHP_EOL;
                    }

                    // 设置文件权限
                    $this->permissionManager->setSecureFilePermission($destination);

                    // 验证签名
                    if ($this->verifySignature && isset($options['verify_type']) && isset($options['verify_version'])) {
                        $this->verifyFileSignature($destination, $options['verify_type'], $options['verify_version']);
                    }

                    // 记录下载完成（来自缓存）
                    FileLogger::logDownloadComplete($url, $destination, $actualSize, $duration, true);

                    return true;
                }
            }
        }

        try {
            // 如果不使用多线程下载或者不支持多线程下载，则使用单线程下载
            if (!$this->useMultiThread || !$this->isMultiThreadSupported()) {
                $success = $this->downloadSingleThread($url, $destination, $options);
            } else {
                // 使用多线程下载
                $success = $this->downloadMultiThread($url, $destination, $options);
            }

            if ($success) {
                $duration = microtime(true) - $startTime;
                $actualSize = filesize($destination);

                // 设置文件权限
                $this->permissionManager->setSecureFilePermission($destination);

                // 验证文件完整性（如果提供了校验和）
                if (isset($options['checksums']) && !empty($options['checksums'])) {
                    if (!IntegrityVerifier::verifyDownloadedFile($destination, $options['checksums'])) {
                        throw new \Exception("文件完整性校验失败");
                    }
                }

                // 验证签名
                if ($this->verifySignature && isset($options['verify_type']) && isset($options['verify_version'])) {
                    $this->verifyFileSignature($destination, $options['verify_type'], $options['verify_version']);
                }

                // 记录下载完成（来自网络）
                FileLogger::logDownloadComplete($url, $destination, $actualSize, $duration, false);
            }

            return $success;

        } catch (\Exception $e) {
            FileLogger::logDownloadError($url, $e->getMessage());
            throw $e;
        }
    }

    /**
     * 使用多个URL按优先级尝试下载
     *
     * @param array $urls URL数组（按优先级排序）
     * @param string $destination 目标路径
     * @param array $options 下载选项
     * @return bool 是否下载成功
     * @throws \Exception 所有URL都下载失败时抛出异常
     */
    private function downloadWithFallback(array $urls, $destination, array $options = [])
    {
        $lastException = null;
        $attemptCount = 0;

        foreach ($urls as $url) {
            $attemptCount++;

            try {
                if ($this->showProgress) {
                    // 判断是否为镜像源
                    $isMirror = $this->isMirrorUrl($url);
                    $sourceType = $isMirror ? "镜像源" : "官方源";
                    echo "\033[1;36m尝试从{$sourceType}下载 (第{$attemptCount}个源): " . $this->getUrlHost($url) . "\033[0m" . PHP_EOL;
                }

                // 尝试下载
                $success = $this->download($url, $destination, $options);

                if ($success) {
                    if ($this->showProgress && $attemptCount > 1) {
                        echo "\033[1;32m下载成功！\033[0m" . PHP_EOL;
                    }
                    return true;
                }
            } catch (\Exception $e) {
                $lastException = $e;

                // 记录下载失败
                FileLogger::logDownloadError($url, $e->getMessage(), $attemptCount);

                if ($this->showProgress) {
                    echo "\033[1;33m下载失败: " . $e->getMessage() . "\033[0m" . PHP_EOL;

                    // 如果还有其他URL可以尝试，显示切换信息
                    if ($attemptCount < count($urls)) {
                        echo "\033[1;33m正在切换到下一个源...\033[0m" . PHP_EOL;
                    }
                }

                // 继续尝试下一个URL
                continue;
            }
        }

        // 所有URL都失败了
        $errorMessage = "所有下载源都失败了";
        if ($lastException) {
            $errorMessage .= "，最后一个错误: " . $lastException->getMessage();
        }

        throw new \Exception($errorMessage);
    }

    /**
     * 判断URL是否为镜像源
     *
     * @param string $url URL
     * @return bool
     */
    private function isMirrorUrl($url)
    {
        // 简单判断：如果不是官方域名，则认为是镜像源
        $officialHosts = [
            'www.php.net',
            'pecl.php.net',
            'getcomposer.org',
            'github.com'
        ];

        $host = parse_url($url, PHP_URL_HOST);
        return !in_array($host, $officialHosts);
    }

    /**
     * 获取URL的主机名
     *
     * @param string $url URL
     * @return string
     */
    private function getUrlHost($url)
    {
        return parse_url($url, PHP_URL_HOST) ?: $url;
    }

    /**
     * 单线程下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @param array $options 下载选项
     * @return bool 是否下载成功
     * @throws \Exception 下载失败时抛出异常
     */
    private function downloadSingleThread($url, $destination, array $options = [])
    {
        if ($this->showProgress) {
            echo "\033[1;34m下载文件: " . basename($url) . "\033[0m" . PHP_EOL;

            // 获取文件大小
            $fileSize = $this->getFileSize($url);
            if ($fileSize !== false) {
                echo "\033[33m文件大小: " . $this->formatSize($fileSize) . "\033[0m" . PHP_EOL;
            }

            echo "\033[33m开始下载...\033[0m" . PHP_EOL;
        }

        // 使用curl下载
        $ch = curl_init($url);
        $fp = fopen($destination, 'w');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($this->showProgress) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) {
                if ($downloadSize > 0) {
                    $percent = round($downloaded / $downloadSize * 100);
                    $progressWidth = 50; // 进度条宽度
                    $progressDone = round($progressWidth * ($percent / 100));
                    $progressRemain = $progressWidth - $progressDone;
                    $progressBar = str_repeat("=", $progressDone) . str_repeat(" ", $progressRemain);

                    // 使用颜色输出
                    echo "\r\033[32m["; // 绿色
                    echo $progressBar;
                    echo "]\033[0m "; // 重置颜色
                    echo "{$percent}% (" . $this->formatSize($downloaded) . " / " . $this->formatSize($downloadSize) . ")";

                    // 计算下载速度
                    static $lastTime = 0;
                    static $lastDownloaded = 0;
                    $currentTime = microtime(true);

                    if ($lastTime > 0 && $currentTime - $lastTime >= 1) {
                        $speed = ($downloaded - $lastDownloaded) / ($currentTime - $lastTime);
                        echo " " . $this->formatSize($speed) . "/s";
                        $lastTime = $currentTime;
                        $lastDownloaded = $downloaded;
                    } elseif ($lastTime == 0) {
                        $lastTime = $currentTime;
                        $lastDownloaded = $downloaded;
                    }
                }
            });
        }

        $success = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if ($this->showProgress) {
            echo PHP_EOL;
            echo "\033[32m下载完成!\033[0m" . PHP_EOL;
        }

        if (!$success) {
            throw new \Exception("文件下载失败: " . $error);
        }

        // 验证下载的文件
        if (!IntegrityVerifier::isFileValid($destination)) {
            throw new \Exception("下载的文件无效或损坏");
        }

        // 添加到缓存
        if ($this->useCache) {
            $cacheOptions = [];
            if (isset($options['checksums'])) {
                $cacheOptions['checksums'] = $options['checksums'];
            }
            $this->cacheManager->setDownloadCache($url, $destination, $cacheOptions);
        }

        if ($this->showProgress) {
            $fileSize = filesize($destination);
            echo "\033[1;32m文件下载完成: " . basename($url) . "\033[0m" . PHP_EOL;
            echo "\033[32m文件大小: " . $this->formatSize($fileSize) . "\033[0m" . PHP_EOL;
            echo "\033[32m保存路径: " . $destination . "\033[0m" . PHP_EOL;
        }

        return true;
    }

    /**
     * 多线程下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @param array $options 下载选项
     * @return bool 是否下载成功
     * @throws \Exception 下载失败时抛出异常
     */
    private function downloadMultiThread($url, $destination, array $options = [])
    {
        // 获取文件大小
        $fileSize = $this->getFileSize($url);
        if ($fileSize === false) {
            // 如果无法获取文件大小，则使用单线程下载
            return $this->downloadSingleThread($url, $destination);
        }

        if ($this->showProgress) {
            echo "\033[1;34m多线程下载文件: " . basename($url) . "\033[0m" . PHP_EOL;
            echo "\033[33m文件大小: " . $this->formatSize($fileSize) . "\033[0m" . PHP_EOL;
            echo "\033[33m线程数: {$this->threadCount}\033[0m" . PHP_EOL;
            echo "\033[33m开始下载...\033[0m" . PHP_EOL;
        }

        // 计算每个线程下载的大小
        $partSize = ceil($fileSize / $this->threadCount);

        // 创建临时目录
        $tempDir = sys_get_temp_dir() . '/pvm_download_' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // 创建多个线程下载
        $parts = [];
        $success = true;

        for ($i = 0; $i < $this->threadCount; $i++) {
            $start = $i * $partSize;
            $end = min($start + $partSize - 1, $fileSize - 1);

            $partFile = $tempDir . '/part_' . $i;
            $parts[] = $partFile;

            $success = $success && $this->downloadPart($url, $partFile, $start, $end, $i + 1, $this->threadCount, $fileSize);
        }

        if (!$success) {
            // 清理临时文件
            foreach ($parts as $part) {
                if (file_exists($part)) {
                    unlink($part);
                }
            }
            rmdir($tempDir);

            throw new \Exception("文件下载失败");
        }

        // 合并文件
        $fp = fopen($destination, 'w');
        foreach ($parts as $part) {
            $content = file_get_contents($part);
            fwrite($fp, $content);
            unlink($part);
        }
        fclose($fp);

        // 清理临时目录
        rmdir($tempDir);

        // 验证下载的文件
        if (!IntegrityVerifier::isFileValid($destination)) {
            throw new \Exception("下载的文件无效或损坏");
        }

        // 添加到缓存
        if ($this->useCache) {
            $cacheOptions = [];
            if (isset($options['checksums'])) {
                $cacheOptions['checksums'] = $options['checksums'];
            }
            $this->cacheManager->setDownloadCache($url, $destination, $cacheOptions);
        }

        if ($this->showProgress) {
            echo "\033[1;32m文件下载完成: " . basename($url) . "\033[0m" . PHP_EOL;
            echo "\033[32m文件大小: " . $this->formatSize($fileSize) . "\033[0m" . PHP_EOL;
            echo "\033[32m保存路径: " . $destination . "\033[0m" . PHP_EOL;
        }

        return true;
    }

    /**
     * 下载文件的一部分
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @param int $start 开始位置
     * @param int $end 结束位置
     * @param int $partNumber 部分编号
     * @param int $totalParts 总部分数
     * @param int $fileSize 文件大小
     * @return bool 是否下载成功
     */
    private function downloadPart($url, $destination, $start, $end, $partNumber, $totalParts, $fileSize)
    {
        $ch = curl_init($url);
        $fp = fopen($destination, 'w');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RANGE, $start . '-' . $end);

        if ($this->showProgress) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($start, $end, $partNumber, $totalParts, $fileSize) {
                if ($downloadSize > 0) {
                    $partSize = $end - $start + 1;
                    $percent = round($downloaded / $partSize * 100);
                    $totalPercent = round(($start + $downloaded) / $fileSize * 100);

                    // 进度条
                    $progressWidth = 30; // 进度条宽度
                    $progressDone = round($progressWidth * ($totalPercent / 100));
                    $progressRemain = $progressWidth - $progressDone;
                    $progressBar = str_repeat("=", $progressDone) . str_repeat(" ", $progressRemain);

                    // 使用颜色输出
                    echo "\r\033[36m线程 {$partNumber}/{$totalParts}:\033[0m "; // 青色
                    echo "\033[32m["; // 绿色
                    echo $progressBar;
                    echo "]\033[0m "; // 重置颜色
                    echo "{$totalPercent}% (" . $this->formatSize($start + $downloaded) . " / " . $this->formatSize($fileSize) . ")";

                    // 计算下载速度
                    static $lastTime = 0;
                    static $lastDownloaded = 0;
                    $currentTime = microtime(true);

                    if ($lastTime > 0 && $currentTime - $lastTime >= 1) {
                        $speed = ($downloaded - $lastDownloaded) / ($currentTime - $lastTime);
                        echo " " . $this->formatSize($speed) . "/s";
                        $lastTime = $currentTime;
                        $lastDownloaded = $downloaded;
                    } elseif ($lastTime == 0) {
                        $lastTime = $currentTime;
                        $lastDownloaded = $downloaded;
                    }
                }
            });
        }

        $success = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if ($this->showProgress) {
            echo PHP_EOL;
            echo "\033[32m线程 {$partNumber} 下载完成!\033[0m" . PHP_EOL;
        }

        if (!$success) {
            echo "下载失败: " . $error . PHP_EOL;
            return false;
        }

        return true;
    }

    /**
     * 获取文件大小
     *
     * @param string $url 文件URL
     * @return int|false 文件大小，如果无法获取则返回false
     */
    private function getFileSize($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);

        if ($size <= 0) {
            return false;
        }

        return $size;
    }

    /**
     * 检查是否支持多线程下载
     *
     * @return bool
     */
    private function isMultiThreadSupported()
    {
        return function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec');
    }

    /**
     * 格式化文件大小
     *
     * @param int $size 文件大小（字节）
     * @return string 格式化后的大小
     */
    private function formatSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * 验证文件签名
     *
     * @param string $filePath 文件路径
     * @param string $type 验证类型，可以是'php'或'extension'
     * @param string $version 版本
     * @return bool 是否验证成功
     * @throws \Exception 验证失败时抛出异常
     */
    private function verifyFileSignature($filePath, $type, $version)
    {
        if (!$this->verifySignature) {
            return true;
        }

        if ($type === 'php') {
            return $this->signatureVerifier->verifyPhpSignature($filePath, $version);
        } elseif ($type === 'extension') {
            $extension = isset($options['extension']) ? $options['extension'] : '';
            return $this->signatureVerifier->verifyExtensionSignature($filePath, $extension, $version);
        }

        return true;
    }
}
