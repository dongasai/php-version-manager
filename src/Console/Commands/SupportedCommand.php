<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\SupportedVersions;

/**
 * 支持的版本命令类
 * 
 * 用于显示支持的PHP版本列表
 */
class SupportedCommand implements CommandInterface
{
    /**
     * 支持的版本管理器
     *
     * @var SupportedVersions
     */
    private $supportedVersions;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->supportedVersions = new SupportedVersions();
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 获取系统信息
        $arch = $this->supportedVersions->getSystemArchitecture();
        list($distro, $version) = $this->supportedVersions->getSystemDistribution();
        
        echo "系统信息:" . PHP_EOL;
        echo "  架构: {$arch}" . PHP_EOL;
        echo "  发行版: {$distro}" . PHP_EOL;
        echo "  版本: {$version}" . PHP_EOL;
        echo PHP_EOL;
        
        // 获取当前系统支持的PHP版本
        $supportedVersions = $this->supportedVersions->getSupportedVersionsForCurrentSystem();
        
        echo "当前系统支持的PHP版本:" . PHP_EOL;
        
        foreach ($supportedVersions as $phpVersion => $supportLevel) {
            $levelDesc = $this->supportedVersions->getSupportLevelDescription($supportLevel);
            $recommended = $this->supportedVersions->isRecommended($phpVersion) ? '(推荐)' : '';
            
            echo "  PHP {$phpVersion}.x: {$levelDesc} {$recommended}" . PHP_EOL;
            
            // 显示已知问题
            $knownIssues = $this->supportedVersions->getKnownIssues($phpVersion);
            if (!empty($knownIssues)) {
                echo "    已知问题:" . PHP_EOL;
                foreach ($knownIssues as $issue) {
                    echo "    - {$issue}" . PHP_EOL;
                }
            }
        }
        
        echo PHP_EOL;
        echo "支持级别说明:" . PHP_EOL;
        echo "  完全支持: 经过测试，所有功能正常工作" . PHP_EOL;
        echo "  部分支持: 大部分功能正常工作，但可能存在一些已知问题" . PHP_EOL;
        echo "  不支持: 已知存在严重问题，不建议使用" . PHP_EOL;
        echo "  待测试: 尚未经过完整测试" . PHP_EOL;
        
        return 0;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '显示支持的PHP版本列表';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm supported

显示当前系统支持的PHP版本列表。

示例:
  pvm supported
USAGE;
    }
}
