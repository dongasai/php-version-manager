<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\Cache\CacheManager;

/**
 * 缓存命令类
 */
class CacheCommand implements CommandInterface
{
    /**
     * 缓存管理器
     *
     * @var CacheManager
     */
    private $cacheManager;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->cacheManager = new CacheManager();
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        if (empty($args)) {
            echo $this->getUsage() . PHP_EOL;
            return 0;
        }
        
        $subcommand = array_shift($args);
        
        switch ($subcommand) {
            case 'list':
                return $this->listCache();
            case 'clear':
                return $this->clearCache($args);
            default:
                echo "错误: 未知的子命令 '{$subcommand}'" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 列出缓存
     *
     * @return int 返回状态码
     */
    private function listCache()
    {
        $stats = $this->cacheManager->getCacheStats();
        
        echo "缓存统计信息:" . PHP_EOL;
        echo "  版本缓存: {$stats['version']['count']} 个文件, " . $this->formatSize($stats['version']['size']) . PHP_EOL;
        echo "  扩展缓存: {$stats['extension']['count']} 个文件, " . $this->formatSize($stats['extension']['size']) . PHP_EOL;
        echo "  下载缓存: {$stats['download']['count']} 个文件, " . $this->formatSize($stats['download']['size']) . PHP_EOL;
        echo "  总计: {$stats['total']['count']} 个文件, " . $this->formatSize($stats['total']['size']) . PHP_EOL;
        
        return 0;
    }
    
    /**
     * 清除缓存
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function clearCache(array $args)
    {
        if (empty($args)) {
            // 清除所有缓存
            echo "清除所有缓存..." . PHP_EOL;
            $this->cacheManager->clearAllCache();
            echo "所有缓存已清除" . PHP_EOL;
            return 0;
        }
        
        $type = $args[0];
        
        switch ($type) {
            case 'version':
                echo "清除版本缓存..." . PHP_EOL;
                $this->cacheManager->clearVersionCache();
                echo "版本缓存已清除" . PHP_EOL;
                break;
            case 'extension':
                echo "清除扩展缓存..." . PHP_EOL;
                $this->cacheManager->clearExtensionCache();
                echo "扩展缓存已清除" . PHP_EOL;
                break;
            case 'download':
                echo "清除下载缓存..." . PHP_EOL;
                $this->cacheManager->clearDownloadCache();
                echo "下载缓存已清除" . PHP_EOL;
                break;
            default:
                echo "错误: 未知的缓存类型 '{$type}'" . PHP_EOL;
                echo "可用的缓存类型: version, extension, download" . PHP_EOL;
                return 1;
        }
        
        return 0;
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
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '管理缓存';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm cache <子命令> [参数]...

管理缓存。

子命令:
  list                    列出缓存统计信息
  clear [类型]            清除缓存，如果不指定类型则清除所有缓存

缓存类型:
  version                 版本缓存
  extension               扩展缓存
  download                下载缓存

示例:
  pvm cache list
  pvm cache clear
  pvm cache clear version
  pvm cache clear download
USAGE;
    }
}
