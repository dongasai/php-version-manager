<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionDetector;

/**
 * 版本命令类
 * 
 * 用于处理与PHP版本相关的命令
 */
class VersionCommand implements CommandInterface
{
    /**
     * 版本检测器
     *
     * @var VersionDetector
     */
    private $detector;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->detector = new VersionDetector();
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
            return $this->showCurrentVersion();
        }
        
        $subCommand = $args[0];
        $subArgs = array_slice($args, 1);
        
        switch ($subCommand) {
            case 'list':
                return $this->listVersions();
            case 'available':
                return $this->listAvailableVersions();
            case 'check':
                return $this->checkVersion($subArgs);
            case 'deps':
                return $this->checkDependencies($subArgs);
            default:
                echo "未知的子命令: {$subCommand}" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 显示当前PHP版本
     *
     * @return int 返回状态码
     */
    private function showCurrentVersion()
    {
        $currentVersion = $this->detector->getCurrentVersion();
        
        if ($currentVersion) {
            echo "当前PHP版本: {$currentVersion}" . PHP_EOL;
        } else {
            echo "未检测到PHP" . PHP_EOL;
        }
        
        return 0;
    }
    
    /**
     * 列出已安装的PHP版本
     *
     * @return int 返回状态码
     */
    private function listVersions()
    {
        $installedVersions = $this->detector->getInstalledVersions();
        $currentVersion = $this->detector->getCurrentVersion();
        
        echo "已安装的PHP版本:" . PHP_EOL;
        
        if (empty($installedVersions)) {
            echo "  没有通过PVM安装的PHP版本" . PHP_EOL;
        } else {
            foreach ($installedVersions as $version) {
                $marker = ($version === $currentVersion) ? '* ' : '  ';
                echo "{$marker}{$version}" . PHP_EOL;
            }
        }
        
        echo PHP_EOL;
        echo "系统PHP版本: {$currentVersion}" . PHP_EOL;
        
        return 0;
    }
    
    /**
     * 列出可用的PHP版本
     *
     * @return int 返回状态码
     */
    private function listAvailableVersions()
    {
        $availableVersions = $this->detector->getAvailableVersions();
        $installedVersions = $this->detector->getInstalledVersions();
        
        echo "可用的PHP版本:" . PHP_EOL;
        
        foreach ($availableVersions as $version) {
            $status = in_array($version, $installedVersions) ? '[已安装]' : '[可安装]';
            echo "  {$version} {$status}" . PHP_EOL;
        }
        
        return 0;
    }
    
    /**
     * 检查指定PHP版本的兼容性
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function checkVersion(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定PHP版本" . PHP_EOL;
            return 1;
        }
        
        $version = $args[0];
        $isCompatible = $this->detector->isVersionCompatible($version);
        
        echo "PHP {$version} 兼容性检查:" . PHP_EOL;
        
        if ($isCompatible) {
            echo "  兼容: PHP {$version} 可以在当前系统上安装" . PHP_EOL;
        } else {
            echo "  不兼容: PHP {$version} 不能在当前系统上安装" . PHP_EOL;
        }
        
        return $isCompatible ? 0 : 1;
    }
    
    /**
     * 检查指定PHP版本的依赖
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function checkDependencies(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定PHP版本" . PHP_EOL;
            return 1;
        }
        
        $version = $args[0];
        $missingDependencies = $this->detector->checkDependencies($version);
        
        echo "PHP {$version} 依赖检查:" . PHP_EOL;
        
        if (empty($missingDependencies)) {
            echo "  所有依赖已满足" . PHP_EOL;
            return 0;
        } else {
            echo "  缺少以下依赖:" . PHP_EOL;
            foreach ($missingDependencies as $dependency) {
                echo "    - {$dependency}" . PHP_EOL;
            }
            return 1;
        }
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '管理PHP版本';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm version [子命令] [参数]

子命令:
  (无)       显示当前PHP版本
  list       列出已安装的PHP版本
  available  列出可用的PHP版本
  check      检查指定PHP版本的兼容性
  deps       检查指定PHP版本的依赖

示例:
  pvm version
  pvm version list
  pvm version available
  pvm version check 8.1.0
  pvm version deps 8.1.0
USAGE;
    }
}
