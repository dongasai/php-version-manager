<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionDetector;
use VersionManager\Core\VersionSwitcher;

/**
 * 列表命令类
 * 
 * 用于列出已安装的PHP版本
 */
class ListCommand implements CommandInterface
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
     * 构造函数
     */
    public function __construct()
    {
        $this->detector = new VersionDetector();
        $this->switcher = new VersionSwitcher();
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        $currentVersion = $this->switcher->getCurrentVersion();
        $globalVersion = $this->switcher->getGlobalVersion();
        $installedVersions = $this->detector->getInstalledVersions();
        
        echo "已安装的PHP版本:" . PHP_EOL;
        
        if (empty($installedVersions)) {
            echo "  没有通过PVM安装的PHP版本" . PHP_EOL;
        } else {
            foreach ($installedVersions as $version) {
                $markers = [];
                
                if ($version === $currentVersion) {
                    $markers[] = '当前';
                }
                
                if ($version === $globalVersion) {
                    $markers[] = '全局';
                }
                
                $marker = empty($markers) ? '' : ' (' . implode(', ', $markers) . ')';
                echo "  * {$version}{$marker}" . PHP_EOL;
            }
        }
        
        echo PHP_EOL;
        
        // 显示系统PHP版本
        $systemVersion = $this->detector->getCurrentVersion();
        echo "系统PHP版本: {$systemVersion}" . PHP_EOL;
        
        return 0;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '列出已安装的PHP版本';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm list

列出已安装的PHP版本。

示例:
  pvm list
USAGE;
    }
}
