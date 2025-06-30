<?php

namespace VersionManager\Core\Download;

use VersionManager\Core\Logger\FileLogger;

/**
 * 镜像源测速类
 * 
 * 用于测试多个镜像源的响应速度，选择最优镜像源
 */
class MirrorSpeedTest
{
    /**
     * 缓存文件路径
     *
     * @var string
     */
    private $cacheFile;
    
    /**
     * 缓存有效期（秒）
     *
     * @var int
     */
    private $cacheTtl;
    
    /**
     * 测速超时时间（秒）
     *
     * @var int
     */
    private $timeout;
    
    /**
     * 构造函数
     *
     * @param int $cacheTtl 缓存有效期，默认1天
     * @param int $timeout 测速超时时间，默认10秒
     */
    public function __construct($cacheTtl = 86400, $timeout = 10)
    {
        $this->cacheTtl = $cacheTtl;
        $this->timeout = $timeout;
        
        // 设置缓存文件路径
        $cacheDir = getenv('HOME') . '/.pvm/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $this->cacheFile = $cacheDir . '/mirror_speed.cache';
    }
    
    /**
     * 获取最优镜像源列表（按速度排序）
     *
     * @param array $mirrors 镜像源列表
     * @return array 按速度排序的镜像源列表
     */
    public function getOptimalMirrors($mirrors)
    {
        if (empty($mirrors)) {
            return [];
        }
        
        // 检查缓存
        if ($this->isCacheValid()) {
            $cachedResults = $this->loadFromCache();
            if ($cachedResults && $this->isCacheResultValid($cachedResults, $mirrors)) {
                FileLogger::info("使用缓存的镜像源测速结果", 'MIRROR_SPEED');
                return $cachedResults;
            }
        }
        
        // 执行测速
        FileLogger::info("开始镜像源测速，测试 " . count($mirrors) . " 个镜像源", 'MIRROR_SPEED');
        $results = $this->testMirrorSpeeds($mirrors);
        
        // 缓存结果
        $this->saveToCache($results);
        
        return $results;
    }
    
    /**
     * 测试多个镜像源的速度
     *
     * @param array $mirrors 镜像源列表
     * @return array 按速度排序的结果
     */
    private function testMirrorSpeeds($mirrors)
    {
        $results = [];
        
        foreach ($mirrors as $mirror) {
            $speed = $this->testSingleMirror($mirror);
            $results[] = [
                'url' => $mirror,
                'speed' => $speed,
                'status' => $speed < PHP_FLOAT_MAX ? 'online' : 'offline',
                'response_time_ms' => $speed < PHP_FLOAT_MAX ? round($speed * 1000, 2) : null
            ];
            
            FileLogger::info("镜像源测速: {$mirror} - " . 
                ($speed < PHP_FLOAT_MAX ? round($speed * 1000, 2) . "ms" : "失败"), 'MIRROR_SPEED');
        }
        
        // 按速度排序（速度快的在前）
        usort($results, function($a, $b) {
            return $a['speed'] <=> $b['speed'];
        });
        
        return $results;
    }
    
    /**
     * 测试单个镜像源的速度
     *
     * @param string $mirror 镜像源URL
     * @return float 响应时间（秒），失败返回PHP_FLOAT_MAX
     */
    private function testSingleMirror($mirror)
    {
        $pingUrl = rtrim($mirror, '/') . '/ping';
        $startTime = microtime(true);
        
        // 使用wget测试，静默模式，输出到标准输出
        $command = sprintf(
            'wget --quiet --timeout=%d --tries=1 --output-document=- %s 2>/dev/null',
            $this->timeout,
            escapeshellarg($pingUrl)
        );
        
        // 执行命令
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 计算响应时间
        $responseTime = microtime(true) - $startTime;
        
        // 检查是否成功
        if ($returnCode === 0 && !empty($output)) {
            // 验证响应内容是否包含预期的ping响应
            $responseText = implode("\n", $output);
            if (strpos($responseText, 'pong') === 0) {
                return $responseTime;
            }
        }
        
        return PHP_FLOAT_MAX; // 失败的镜像源排在最后
    }
    
    /**
     * 检查缓存是否有效
     *
     * @return bool
     */
    private function isCacheValid()
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $cacheTime = filemtime($this->cacheFile);
        return (time() - $cacheTime) < $this->cacheTtl;
    }
    
    /**
     * 从缓存加载结果
     *
     * @return array|null
     */
    private function loadFromCache()
    {
        if (!file_exists($this->cacheFile)) {
            return null;
        }
        
        $content = file_get_contents($this->cacheFile);
        if ($content === false) {
            return null;
        }
        
        $data = json_decode($content, true);
        return $data && isset($data['results']) ? $data['results'] : null;
    }
    
    /**
     * 保存结果到缓存
     *
     * @param array $results 测速结果
     */
    private function saveToCache($results)
    {
        $data = [
            'timestamp' => time(),
            'results' => $results,
            'mirrors' => array_column($results, 'url')
        ];
        
        $content = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($this->cacheFile, $content);
        
        FileLogger::info("镜像源测速结果已缓存到: " . $this->cacheFile, 'MIRROR_SPEED');
    }
    
    /**
     * 检查缓存结果是否对当前镜像源列表有效
     *
     * @param array $cachedResults 缓存的结果
     * @param array $currentMirrors 当前镜像源列表
     * @return bool
     */
    private function isCacheResultValid($cachedResults, $currentMirrors)
    {
        $cachedMirrors = array_column($cachedResults, 'url');
        
        // 检查镜像源列表是否一致
        sort($cachedMirrors);
        sort($currentMirrors);
        
        return $cachedMirrors === $currentMirrors;
    }
    
    /**
     * 清除缓存
     *
     * @return bool
     */
    public function clearCache()
    {
        if (file_exists($this->cacheFile)) {
            $result = unlink($this->cacheFile);
            if ($result) {
                FileLogger::info("镜像源测速缓存已清除", 'MIRROR_SPEED');
            }
            return $result;
        }
        
        return true;
    }
    
    /**
     * 获取缓存信息
     *
     * @return array
     */
    public function getCacheInfo()
    {
        if (!file_exists($this->cacheFile)) {
            return [
                'exists' => false,
                'file' => $this->cacheFile
            ];
        }
        
        $cacheTime = filemtime($this->cacheFile);
        $isValid = $this->isCacheValid();
        
        return [
            'exists' => true,
            'file' => $this->cacheFile,
            'created_at' => date('Y-m-d H:i:s', $cacheTime),
            'age_seconds' => time() - $cacheTime,
            'ttl_seconds' => $this->cacheTtl,
            'is_valid' => $isValid,
            'expires_at' => date('Y-m-d H:i:s', $cacheTime + $this->cacheTtl)
        ];
    }
}
