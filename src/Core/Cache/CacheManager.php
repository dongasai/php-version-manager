<?php

namespace VersionManager\Core\Cache;

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
     * @return string|null 缓存文件路径，如果不存在或已过期则返回null
     */
    public function getDownloadCache($url)
    {
        $key = md5($url);
        $cacheFile = $this->downloadCacheDir . '/' . $key;
        $metaFile = $cacheFile . '.meta';
        
        // 检查缓存文件和元数据文件是否存在
        if (!file_exists($cacheFile) || !file_exists($metaFile)) {
            return null;
        }
        
        // 读取元数据
        $meta = json_decode(file_get_contents($metaFile), true);
        if (!$meta || !isset($meta['url']) || !isset($meta['time'])) {
            return null;
        }
        
        // 检查URL是否匹配
        if ($meta['url'] !== $url) {
            return null;
        }
        
        // 检查缓存是否过期
        if (time() - $meta['time'] > $this->cacheExpire) {
            return null;
        }
        
        return $cacheFile;
    }
    
    /**
     * 设置下载文件缓存
     *
     * @param string $url 文件URL
     * @param string $filePath 文件路径
     * @return bool 是否成功
     */
    public function setDownloadCache($url, $filePath)
    {
        $key = md5($url);
        $cacheFile = $this->downloadCacheDir . '/' . $key;
        $metaFile = $cacheFile . '.meta';
        
        // 复制文件到缓存目录
        if (!copy($filePath, $cacheFile)) {
            return false;
        }
        
        // 写入元数据
        $meta = [
            'url' => $url,
            'time' => time(),
            'size' => filesize($filePath),
        ];
        
        return file_put_contents($metaFile, json_encode($meta)) !== false;
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
        
        if (file_exists($cacheFile)) {
            $success = $success && unlink($cacheFile);
        }
        
        if (file_exists($metaFile)) {
            $success = $success && unlink($metaFile);
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
        return $this->clearCacheDir($this->downloadCacheDir);
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
