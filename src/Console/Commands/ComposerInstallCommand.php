<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\VersionSwitcher;

/**
 * Composer安装命令类
 */
class ComposerInstallCommand implements CommandInterface
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
        $composerVersion = isset($options['version']) ? $options['version'] : '2';
        
        try {
            echo "正在为PHP {$phpVersion} 安装Composer {$composerVersion}..." . PHP_EOL;
            $this->manager->install($phpVersion, $composerVersion, $options);
            echo "Composer {$composerVersion} 安装成功" . PHP_EOL;
            
            // 如果设置了默认选项，则显示提示
            if (isset($options['default']) && $options['default']) {
                echo "已将Composer {$composerVersion} 设置为默认版本" . PHP_EOL;
            }
            
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
        return '安装Composer';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm composer-install [选项]

安装Composer。

选项:
  --php=<版本>            指定PHP版本，默认为当前版本
  --version=<版本>        指定Composer版本，可以是'1'、'2'或具体版本号，默认为'2'
  --default               设置为默认Composer
  --mirror=<镜像名称>      使用指定的镜像下载

示例:
  pvm composer-install
  pvm composer-install --php=7.4.30
  pvm composer-install --version=1
  pvm composer-install --version=2.3.10
  pvm composer-install --default
  pvm composer-install --mirror=aliyun
USAGE;
    }
}
