<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ComposerManager;

/**
 * Composer列表命令类
 */
class ComposerListCommand implements CommandInterface
{
    /**
     * Composer管理器
     *
     * @var ComposerManager
     */
    private $manager;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->manager = new ComposerManager();
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 获取已安装的Composer列表
        $installedComposers = $this->manager->getInstalledComposers();
        
        // 获取默认Composer配置
        $defaultConfig = $this->manager->getDefaultComposerConfig();
        
        if (empty($installedComposers)) {
            echo "没有已安装的Composer" . PHP_EOL;
            return 0;
        }
        
        echo "已安装的Composer:" . PHP_EOL;
        
        foreach ($installedComposers as $phpVersion => $composerVersions) {
            echo "  PHP {$phpVersion}:" . PHP_EOL;
            
            foreach ($composerVersions as $composerVersion) {
                // 检查是否为默认Composer
                $isDefault = ($defaultConfig && $defaultConfig['php_version'] === $phpVersion && $defaultConfig['composer_version'] === $composerVersion);
                $defaultMark = $isDefault ? ' [默认]' : '';
                
                // 获取Composer版本信息
                $composerInfo = $this->manager->getComposerInfo($phpVersion, $composerVersion);
                $versionInfo = isset($composerInfo['version']) ? $composerInfo['version'] : $composerVersion;
                
                echo "    * Composer {$composerVersion} ({$versionInfo}){$defaultMark}" . PHP_EOL;
            }
        }
        
        return 0;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '列出已安装的Composer';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm composer-list

列出已安装的Composer。

示例:
  pvm composer-list
USAGE;
    }
}
