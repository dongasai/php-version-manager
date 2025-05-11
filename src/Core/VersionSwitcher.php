<?php

namespace VersionManager\Core;

use Exception;

/**
 * PHP版本切换类
 * 
 * 负责在不同的PHP版本之间切换
 */
class VersionSwitcher
{
    /**
     * 版本检测器
     *
     * @var VersionDetector
     */
    private $detector;
    
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
     * 符号链接目录
     *
     * @var string
     */
    private $shimsDir;
    
    /**
     * 当前版本文件
     *
     * @var string
     */
    private $currentVersionFile;
    
    /**
     * 全局版本文件
     *
     * @var string
     */
    private $globalVersionFile;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->detector = new VersionDetector();
        $this->pvmDir = getenv('HOME') . '/.pvm';
        $this->versionsDir = $this->pvmDir . '/versions';
        $this->shimsDir = $this->pvmDir . '/shims';
        $this->currentVersionFile = $this->pvmDir . '/current';
        $this->globalVersionFile = $this->pvmDir . '/version';
        
        // 确保目录存在
        $this->ensureDirectoriesExist();
    }
    
    /**
     * 确保必要的目录存在
     */
    private function ensureDirectoriesExist()
    {
        $dirs = [$this->pvmDir, $this->versionsDir, $this->shimsDir];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * 切换到指定的PHP版本
     *
     * @param string $version PHP版本
     * @param bool $global 是否设置为全局版本
     * @return bool 是否切换成功
     * @throws Exception 切换失败时抛出异常
     */
    public function switchVersion($version, $global = false)
    {
        // 检查版本是否已安装
        if (!$this->isVersionInstalled($version)) {
            throw new Exception("PHP版本 {$version} 未安装");
        }
        
        // 获取版本目录
        $versionDir = $this->versionsDir . '/' . $version;
        
        // 更新当前版本符号链接
        if (file_exists($this->currentVersionFile) || is_link($this->currentVersionFile)) {
            unlink($this->currentVersionFile);
        }
        
        symlink($versionDir, $this->currentVersionFile);
        
        // 如果设置为全局版本，则更新全局版本文件
        if ($global) {
            file_put_contents($this->globalVersionFile, $version);
        }
        
        // 更新符号链接
        $this->updateShims($version);
        
        return true;
    }
    
    /**
     * 设置项目级别的PHP版本
     *
     * @param string $version PHP版本
     * @param string $projectDir 项目目录
     * @return bool 是否设置成功
     * @throws Exception 设置失败时抛出异常
     */
    public function setProjectVersion($version, $projectDir)
    {
        // 检查版本是否已安装
        if (!$this->isVersionInstalled($version)) {
            throw new Exception("PHP版本 {$version} 未安装");
        }
        
        // 检查项目目录是否存在
        if (!is_dir($projectDir)) {
            throw new Exception("项目目录 {$projectDir} 不存在");
        }
        
        // 创建或更新项目版本文件
        $versionFile = $projectDir . '/.php-version';
        file_put_contents($versionFile, $version);
        
        return true;
    }
    
    /**
     * 获取当前PHP版本
     *
     * @return string|null 当前PHP版本，如果未设置则返回null
     */
    public function getCurrentVersion()
    {
        // 检查项目版本
        $projectVersion = $this->getProjectVersion();
        if ($projectVersion) {
            return $projectVersion;
        }
        
        // 检查当前版本符号链接
        if (is_link($this->currentVersionFile)) {
            $target = readlink($this->currentVersionFile);
            $version = basename($target);
            return $version;
        }
        
        // 检查全局版本文件
        if (file_exists($this->globalVersionFile)) {
            return trim(file_get_contents($this->globalVersionFile));
        }
        
        return null;
    }
    
    /**
     * 获取项目PHP版本
     *
     * @param string|null $dir 项目目录，如果为null则使用当前目录
     * @return string|null 项目PHP版本，如果未设置则返回null
     */
    public function getProjectVersion($dir = null)
    {
        if ($dir === null) {
            $dir = getcwd();
        }
        
        // 向上递归查找.php-version文件
        while ($dir !== '/' && $dir !== '') {
            $versionFile = $dir . '/.php-version';
            if (file_exists($versionFile)) {
                return trim(file_get_contents($versionFile));
            }
            $dir = dirname($dir);
        }
        
        return null;
    }
    
    /**
     * 获取全局PHP版本
     *
     * @return string|null 全局PHP版本，如果未设置则返回null
     */
    public function getGlobalVersion()
    {
        if (file_exists($this->globalVersionFile)) {
            return trim(file_get_contents($this->globalVersionFile));
        }
        
        return null;
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
     * 更新符号链接
     *
     * @param string $version PHP版本
     * @return bool 是否更新成功
     */
    private function updateShims($version)
    {
        // 获取版本目录
        $versionDir = $this->versionsDir . '/' . $version;
        $binDir = $versionDir . '/bin';
        
        // 检查bin目录是否存在
        if (!is_dir($binDir)) {
            return false;
        }
        
        // 清空符号链接目录
        $this->clearShimsDir();
        
        // 创建符号链接
        $binFiles = scandir($binDir);
        foreach ($binFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $source = $binDir . '/' . $file;
            $target = $this->shimsDir . '/' . $file;
            
            if (is_file($source) && is_executable($source)) {
                symlink($source, $target);
            }
        }
        
        return true;
    }
    
    /**
     * 清空符号链接目录
     *
     * @return bool 是否清空成功
     */
    private function clearShimsDir()
    {
        if (!is_dir($this->shimsDir)) {
            return false;
        }
        
        $files = scandir($this->shimsDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $path = $this->shimsDir . '/' . $file;
            
            if (is_link($path) || is_file($path)) {
                unlink($path);
            }
        }
        
        return true;
    }
}
