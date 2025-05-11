<?php

namespace VersionManager\Core;

use Exception;

/**
 * PHP版本删除类
 * 
 * 负责删除已安装的PHP版本
 */
class VersionRemover
{
    /**
     * 版本检测器
     *
     * @var VersionDetector
     */
    private $detector;
    
    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $switcher;
    
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmDir;
    
    /**
     * 版本目录
     *
     * @var string
     */
    private $versionsDir;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->detector = new VersionDetector();
        $this->switcher = new VersionSwitcher();
        $this->pvmDir = getenv('HOME') . '/.pvm';
        $this->versionsDir = $this->pvmDir . '/versions';
    }
    
    /**
     * 删除PHP版本
     *
     * @param string $version PHP版本
     * @param array $options 删除选项
     * @return bool 是否删除成功
     * @throws Exception 删除失败时抛出异常
     */
    public function remove($version, array $options = [])
    {
        // 检查版本是否已安装
        if (!$this->isVersionInstalled($version)) {
            throw new Exception("PHP版本 {$version} 未安装");
        }
        
        // 检查版本是否是当前版本
        $currentVersion = $this->switcher->getCurrentVersion();
        if ($currentVersion === $version && !isset($options['force'])) {
            throw new Exception("PHP版本 {$version} 是当前版本，无法删除。使用 --force 选项强制删除");
        }
        
        // 检查版本是否是全局版本
        $globalVersion = $this->switcher->getGlobalVersion();
        if ($globalVersion === $version && !isset($options['force'])) {
            throw new Exception("PHP版本 {$version} 是全局版本，无法删除。使用 --force 选项强制删除");
        }
        
        // 检查依赖关系
        if (!isset($options['skip_deps_check'])) {
            $this->checkDependencies($version);
        }
        
        // 删除版本目录
        $versionDir = $this->versionsDir . '/' . $version;
        $this->removeDirectory($versionDir);
        
        // 如果删除的是当前版本或全局版本，则切换到其他版本
        if ($currentVersion === $version || $globalVersion === $version) {
            $this->switchToOtherVersion($version);
        }
        
        return true;
    }
    
    /**
     * 批量删除PHP版本
     *
     * @param array $versions PHP版本列表
     * @param array $options 删除选项
     * @return array 删除结果，键为版本，值为是否删除成功
     */
    public function batchRemove(array $versions, array $options = [])
    {
        $results = [];
        
        foreach ($versions as $version) {
            try {
                $results[$version] = $this->remove($version, $options);
            } catch (Exception $e) {
                $results[$version] = false;
            }
        }
        
        return $results;
    }
    
    /**
     * 检查版本是否已安装
     *
     * @param string $version PHP版本
     * @return bool 是否已安装
     */
    public function isVersionInstalled($version)
    {
        $versionDir = $this->versionsDir . '/' . $version;
        return is_dir($versionDir) && file_exists($versionDir . '/bin/php');
    }
    
    /**
     * 检查依赖关系
     *
     * @param string $version PHP版本
     * @throws Exception 如果有依赖关系，则抛出异常
     */
    private function checkDependencies($version)
    {
        // 这里可以检查是否有其他应用依赖于此PHP版本
        // 目前简单实现，不做实际检查
    }
    
    /**
     * 切换到其他版本
     *
     * @param string $version 要避免的PHP版本
     * @return bool 是否切换成功
     */
    private function switchToOtherVersion($version)
    {
        // 获取已安装的PHP版本
        $installedVersions = $this->detector->getInstalledVersions();
        
        // 过滤掉要删除的版本
        $otherVersions = array_filter($installedVersions, function ($v) use ($version) {
            return $v !== $version;
        });
        
        // 如果没有其他版本，则不做任何操作
        if (empty($otherVersions)) {
            return false;
        }
        
        // 切换到第一个可用的版本
        $newVersion = reset($otherVersions);
        $this->switcher->switchVersion($newVersion, true);
        
        return true;
    }
    
    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     * @return bool 是否删除成功
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            
            $path = $dir . '/' . $object;
            
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
}
