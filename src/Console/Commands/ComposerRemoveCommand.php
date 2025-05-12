<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\VersionSwitcher;

/**
 * Composer删除命令类
 */
class ComposerRemoveCommand implements CommandInterface
{
    /**
     * Composer管理器
     *
     * @var ComposerManager
     */
    private $manager;
    
    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $versionSwitcher;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->manager = new ComposerManager();
        $this->versionSwitcher = new VersionSwitcher();
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 获取Composer版本
        if (!isset($options['version'])) {
            echo "错误: 请指定要删除的Composer版本" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $composerVersion = $options['version'];
        
        try {
            echo "正在删除PHP {$phpVersion} 的Composer {$composerVersion}..." . PHP_EOL;
            $this->manager->remove($phpVersion, $composerVersion);
            echo "Composer {$composerVersion} 删除成功" . PHP_EOL;
            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 解析命令选项
     *
     * @param array $args 命令参数
     * @return array 选项数组
     */
    private function parseOptions(array $args)
    {
        $options = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                
                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;
                }
            } elseif (strpos($arg, '-') === 0) {
                $option = substr($arg, 1);
                $options[$option] = true;
            } elseif (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $options[$key] = $value;
            }
        }
        
        return $options;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '删除Composer';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm composer-remove [选项]

删除Composer。

选项:
  --php=<版本>            指定PHP版本，默认为当前版本
  --version=<版本>        指定要删除的Composer版本

示例:
  pvm composer-remove --version=1
  pvm composer-remove --php=7.4.30 --version=2
USAGE;
    }
}
