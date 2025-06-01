<?php

namespace VersionManager\Core\Cache;

use VersionManager\Core\Download\IntegrityVerifier;
use VersionManager\Core\Logger\FileLogger;

/**
 * 缓存管理类
 *
 * 负责管理各种缓存，包括版本信息缓存、扩展信息缓存和下载文件缓存
 */
class CacheManager
{
    /**
     * 缓存目录
     *
     * @var string
     */
    private $cacheDir;
    
    /**
     * 版本信息缓存目录
     *
     * @var string
     */
    private $versionCacheDir;
    
    /**
     * 扩展信息缓存目录
     *
     * @var string
     */
    private $extensionCacheDir;
    
    /**
     * 下载文件缓存目录
     *
     * @var string
     */
    private $downloadCacheDir;
    
    /**
     * 缓存过期时间（秒）
     *
     * @var int
     */
    private $cacheExpire = 86400; // 默认1天
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 设置缓存目录
        $pvmDir = getenv('HOME') . '/.pvm';
        $this->cacheDir = $pvmDir . '/cache';
        $this->versionCacheDir = $this->cacheDir . '/versions';
        $this->extensionCacheDir = $this->cacheDir . '/extensions';
        $this->downloadCacheDir = $this->cacheDir . '/downloads';
        
        // 确保目录存在
        $this->ensureCacheDirsExist();
    }
    
    /**
     * 确保缓存目录存在
     */
    private function ensureCacheDirsExist()
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        if (!is_dir($this->versionCacheDir)) {
            mkdir($this->versionCacheDir, 0755, true);
        }
        
        if (!is_dir($this->extensionCacheDir)) {
            mkdir($this->extensionCacheDir, 0755, true);
        }
        
        if (!is_dir($this->downloadCacheDir)) {
            mkdir($this->downloadCacheDir, 0755, true);
        }
    }
    
    /**
     * 设置缓存过期时间
     *
     * @param int $seconds 过期时间（秒）
     * @return $this
     */
    public function setCacheExpire($seconds)
    {
        $this->cacheExpire = $seconds;
        return $this;
    }
    
    /**
     * 获取缓存过期时间
     *
     * @return int
     */
    public function getCacheExpire()
    {
        return $this->cacheExpire;
    }
    
    /**
     * 获取版本信息缓存
     *
     * @param string $key 缓存键
     * @return mixed|null 缓存数据，如果不存在或已过期则返回null
     */
    public function getVersionCache($key)
    {
        return $this->getCache($this->versionCacheDir . '/' . $key);
    }
    
    /**
     * 设置版本信息缓存
     *
     * @param string $key 缓存键
     * @param mixed $data 缓存数据
     * @return bool 是否成功
     */
    public function setVersionCache($key, $data)
    {
        return $this->setCache($this->versionCacheDir . '/' . $key, $data);
    }
    
    /**
     * 删除版本信息缓存
     *
     * @param string $key 缓存键
     * @return bool 是否成功
     */
    public function deleteVersionCache($key)
    {
        return $this->deleteCache($this->versionCacheDir . '/' . $key);
    }
    
    /**
     * 清除所有版本信息缓存
     *
     * @return bool 是否成功
     */
    public function clearVersionCache()
    {
        return $this->clearCacheDir($this->versionCacheDir);
    }
    
    /**
     * 获取扩展信息缓存
     *
     * @param string $key 缓存键
     * @return mixed|null 缓存数据，如果不存在或已过期则返回null
     */
    public function getExtensionCache($key)
    {
        return $this->getCache($this->extensionCacheDir . '/' . $key);
    }
    
    /**
     * 设置扩展信息缓存
     *
     * @param string $key 缓存键
     * @param mixed $data 缓存数据
     * @return bool 是否成功
     */
    public function setExtensionCache($key, $data)
    {
        return $this->setCache($this->extensionCacheDir . '/' . $key, $data);
    }
    
    /**
     * 删除扩展信息缓存
     *
     * @param string $key 缓存键
     * @return bool 是否成功
     */
    public function deleteExtensionCache($key)
    {
        return $this->deleteCache($this->extensionCacheDir . '/' . $key);
    }
    
    /**
     * 清除所有扩展信息缓存
     *
     * @return bool 是否成功
     */
    public function clearExtensionCache()
    {
        return $this->clearCacheDir($this->extensionCacheDir);
    }
    
    /**
     * 获取下载文件缓存路径
     *
     * @param string $url 文件URL
     * @param bool $verifyIntegrity 是否验证文件完整性
     * @return string|null 缓存文件路径，如果不存在或已过期则返回null
     */
    public function getDownloadCache($url, $verifyIntegrity = true)
    {
        $key = md5($url);
        $cacheFile = $this->downloadCacheDir . '/' . $key;
        $metaFile = $cacheFile . '.meta';

        // 检查缓存文件和元数据文件是否存在
        if (!file_exists($cacheFile) || !file_exists($metaFile)) {
            FileLogger::logCacheOperation('MISS', $key, "文件不存在: {$url}");
            return null;
        }

        // 读取元数据
        $meta = json_decode(file_get_contents($metaFile), true);
        if (!$meta || !isset($meta['url']) || !isset($meta['time'])) {
            FileLogger::logCacheOperation('MISS', $key, "元数据损坏: {$url}");
            $this->deleteDownloadCache($url); // 清理损坏的缓存
            return null;
        }

        // 检查URL是否匹配
        if ($meta['url'] !== $url) {
            FileLogger::logCacheOperation('MISS', $key, "URL不匹配: {$url}");
            return null;
        }

        // 检查缓存是否过期
        if (time() - $meta['time'] > $this->cacheExpire) {
            FileLogger::logCacheOperation('MISS', $key, "缓存过期: {$url}");
            $this->deleteDownloadCache($url); // 清理过期缓存
            return null;
        }

        // 验证文件完整性
        if ($verifyIntegrity) {
            // 检查文件大小
            if (isset($meta['size'])) {
                if (!IntegrityVerifier::verifyFileSize($cacheFile, $meta['size'])) {
                    FileLogger::logCacheOperation('MISS', $key, "文件大小不匹配: {$url}");
                    $this->deleteDownloadCache($url); // 清理损坏的缓存
                    return null;
                }
            }

            // 检查文件哈希值（如果有）
            if (isset($meta['checksums']) && !empty($meta['checksums'])) {
                if (!IntegrityVerifier::verifyDownloadedFile($cacheFile, $meta['checksums'])) {
                    FileLogger::logCacheOperation('MISS', $key, "文件完整性校验失败: {$url}");
                    $this->deleteDownloadCache($url); // 清理损坏的缓存
                    return null;
                }
            }

            // 基本文件有效性检查
            if (!IntegrityVerifier::isFileValid($cacheFile)) {
                FileLogger::logCacheOperation('MISS', $key, "文件无效或损坏: {$url}");
                $this->deleteDownloadCache($url); // 清理损坏的缓存
                return null;
            }
        }

        FileLogger::logCacheOperation('HIT', $key, "缓存命中: {$url}");
        return $cacheFile;
    }
    
    /**
     * 设置下载文件缓存
     *
     * @param string $url 文件URL
     * @param string $filePath 文件路径
     * @param array $options 选项，可包含 checksums 等
     * @return bool 是否成功
     */
    public function setDownloadCache($url, $filePath, array $options = [])
    {
        // 验证源文件
        if (!IntegrityVerifier::isFileValid($filePath)) {
            FileLogger::error("无法缓存无效文件: {$filePath}", 'CACHE');
            return false;
        }

        $key = md5($url);
        $cacheFile = $this->downloadCacheDir . '/' . $key;
        $metaFile = $cacheFile . '.meta';

        // 复制文件到缓存目录
        if (!copy($filePath, $cacheFile)) {
            FileLogger::error("复制文件到缓存失败: {$filePath} -> {$cacheFile}", 'CACHE');
            return false;
        }

        // 生成文件校验和
        $checksums = [];
        if (isset($options['checksums']) && is_array($options['checksums'])) {
            // 使用提供的校验和
            $checksums = $options['checksums'];
        } else {
            // 自动生成校验和
            $checksums = IntegrityVerifier::generateChecksums($cacheFile, ['md5', 'sha256']);
        }

        // 写入元数据
        $meta = [
            'url' => $url,
            'time' => time(),
            'size' => filesize($cacheFile),
            'checksums' => $checksums,
            'original_path' => $filePath,
            'cache_version' => '1.0'
        ];

        $success = file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT)) !== false;

        if ($success) {
            FileLogger::logCacheOperation('SET', $key, "缓存文件: {$url}");
        } else {
            FileLogger::error("写入缓存元数据失败: {$metaFile}", 'CACHE');
            // 清理已复制的文件
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }

        return $success;
    }
    
    /**
     * 删除下载文件缓存
     *
     * @param string $url 文件URL
     * @return bool 是否成功
     */
    public function deleteDownloadCache($url)
    {
        $key = md5($url);
        $cacheFile = $this->downloadCacheDir . '/' . $key;
        $metaFile = $cacheFile . '.meta';

        $success = true;
        $deletedFiles = [];

        if (file_exists($cacheFile)) {
            $success = $success && unlink($cacheFile);
            if ($success) {
                $deletedFiles[] = '缓存文件';
            }
        }

        if (file_exists($metaFile)) {
            $success = $success && unlink($metaFile);
            if ($success) {
                $deletedFiles[] = '元数据文件';
            }
        }

        if ($success && !empty($deletedFiles)) {
            $details = "删除: " . implode(', ', $deletedFiles);
            FileLogger::logCacheOperation('DELETE', $key, $details . " - {$url}");
        } elseif (!$success) {
            FileLogger::error("删除缓存失败: {$url}", 'CACHE');
        }

        return $success;
    }
    
    /**
     * 清除所有下载文件缓存
     *
     * @return bool 是否成功
     */
    public function clearDownloadCache()
    {
        $success = $this->clearCacheDir($this->downloadCacheDir);

        if ($success) {
            FileLogger::logCacheOperation('CLEAR', 'ALL', "清除所有下载缓存");
        } else {
            FileLogger::error("清除下载缓存失败", 'CACHE');
        }

        return $success;
    }

    /**
     * 清理过期的下载缓存
     *
     * @return array 清理统计信息
     */
    public function cleanExpiredDownloadCache()
    {
        $stats = [
            'total_checked' => 0,
            'expired_removed' => 0,
            'corrupted_removed' => 0,
            'bytes_freed' => 0
        ];

        $files = glob($this->downloadCacheDir . '/*.meta');

        foreach ($files as $metaFile) {
            $stats['total_checked']++;

            $meta = json_decode(file_get_contents($metaFile), true);
            if (!$meta || !isset($meta['url']) || !isset($meta['time'])) {
                // 元数据损坏
                $this->deleteCorruptedCacheFile($metaFile, $stats);
                continue;
            }

            // 检查是否过期
            if (time() - $meta['time'] > $this->cacheExpire) {
                $this->deleteExpiredCacheFile($meta['url'], $metaFile, $stats);
                continue;
            }

            // 检查文件完整性
            $cacheFile = str_replace('.meta', '', $metaFile);
            if (!file_exists($cacheFile) || !IntegrityVerifier::isFileValid($cacheFile)) {
                $this->deleteCorruptedCacheFile($metaFile, $stats);
                continue;
            }
        }

        FileLogger::info(
            "缓存清理完成 - 检查: {$stats['total_checked']}, 过期删除: {$stats['expired_removed']}, 损坏删除: {$stats['corrupted_removed']}, 释放空间: " .
            $this->formatSize($stats['bytes_freed']),
            'CACHE'
        );

        return $stats;
    }

    /**
     * 删除过期的缓存文件
     */
    private function deleteExpiredCacheFile($url, $metaFile, &$stats)
    {
        $cacheFile = str_replace('.meta', '', $metaFile);
        $size = 0;

        if (file_exists($cacheFile)) {
            $size = filesize($cacheFile);
            unlink($cacheFile);
        }

        if (file_exists($metaFile)) {
            unlink($metaFile);
        }

        $stats['expired_removed']++;
        $stats['bytes_freed'] += $size;

        FileLogger::debug("删除过期缓存: {$url}", 'CACHE');
    }

    /**
     * 删除损坏的缓存文件
     */
    private function deleteCorruptedCacheFile($metaFile, &$stats)
    {
        $cacheFile = str_replace('.meta', '', $metaFile);
        $size = 0;

        if (file_exists($cacheFile)) {
            $size = filesize($cacheFile);
            unlink($cacheFile);
        }

        if (file_exists($metaFile)) {
            unlink($metaFile);
        }

        $stats['corrupted_removed']++;
        $stats['bytes_freed'] += $size;

        FileLogger::debug("删除损坏缓存: {$metaFile}", 'CACHE');
    }

    /**
     * 格式化文件大小
     */
    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 获取缓存
     *
     * @param string $cacheFile 缓存文件路径
     * @return mixed|null 缓存数据，如果不存在或已过期则返回null
     */
    private function getCache($cacheFile)
    {
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        // 读取缓存数据
        $data = file_get_contents($cacheFile);
        if ($data === false) {
            return null;
        }
        
        // 解析缓存数据
        $cache = json_decode($data, true);
        if (!$cache || !isset($cache['data']) || !isset($cache['time'])) {
            return null;
        }
        
        // 检查缓存是否过期
        if (time() - $cache['time'] > $this->cacheExpire) {
            return null;
        }
        
        return $cache['data'];
    }
    
    /**
     * 设置缓存
     *
     * @param string $cacheFile 缓存文件路径
     * @param mixed $data 缓存数据
     * @return bool 是否成功
     */
    private function setCache($cacheFile, $data)
    {
        // 构建缓存数据
        $cache = [
            'data' => $data,
            'time' => time(),
        ];
        
        // 写入缓存文件
        return file_put_contents($cacheFile, json_encode($cache)) !== false;
    }
    
    /**
     * 删除缓存
     *
     * @param string $cacheFile 缓存文件路径
     * @return bool 是否成功
     */
    private function deleteCache($cacheFile)
    {
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return true;
    }
    
    /**
     * 清除缓存目录
     *
     * @param string $dir 缓存目录
     * @return bool 是否成功
     */
    private function clearCacheDir($dir)
    {
        $success = true;
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $success = $success && unlink($file);
            }
        }
        
        return $success;
    }
    
    /**
     * 清除所有缓存
     *
     * @return bool 是否成功
     */
    public function clearAllCache()
    {
        $success = true;
        
        $success = $success && $this->clearVersionCache();
        $success = $success && $this->clearExtensionCache();
        $success = $success && $this->clearDownloadCache();
        
        return $success;
    }
    
    /**
     * 获取缓存统计信息
     *
     * @return array
     */
    public function getCacheStats()
    {
        $stats = [
            'version' => [
                'count' => 0,
                'size' => 0,
            ],
            'extension' => [
                'count' => 0,
                'size' => 0,
            ],
            'download' => [
                'count' => 0,
                'size' => 0,
            ],
            'total' => [
                'count' => 0,
                'size' => 0,
            ],
        ];
        
        // 统计版本缓存
        $files = glob($this->versionCacheDir . '/*');
        $stats['version']['count'] = count($files);
        foreach ($files as $file) {
            if (is_file($file)) {
                $stats['version']['size'] += filesize($file);
            }
        }
        
        // 统计扩展缓存
        $files = glob($this->extensionCacheDir . '/*');
        $stats['extension']['count'] = count($files);
        foreach ($files as $file) {
            if (is_file($file)) {
                $stats['extension']['size'] += filesize($file);
            }
        }
        
        // 统计下载缓存
        $files = glob($this->downloadCacheDir . '/*');
        $metaFiles = glob($this->downloadCacheDir . '/*.meta');
        $stats['download']['count'] = count($files) - count($metaFiles);
        foreach ($files as $file) {
            if (is_file($file) && !preg_match('/\.meta$/', $file)) {
                $stats['download']['size'] += filesize($file);
            }
        }
        
        // 计算总计
        $stats['total']['count'] = $stats['version']['count'] + $stats['extension']['count'] + $stats['download']['count'];
        $stats['total']['size'] = $stats['version']['size'] + $stats['extension']['size'] + $stats['download']['size'];
        
        return $stats;
    }
}
