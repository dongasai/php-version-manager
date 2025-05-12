<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\VersionSwitcher;

/**
 * Composer配置命令类
 */
class ComposerConfigCommand implements CommandInterface
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
        $defaultConfig = $this->manager->getDefaultComposerConfig();
        $composerVersion = isset($options['version']) ? $options['version'] : ($defaultConfig ? $defaultConfig['composer_version'] : '2');
        
        // 如果没有配置项，则显示当前配置
        if (empty($args) || (count($args) === 1 && $args[0] === 'list')) {
            return $this->showConfig($phpVersion, $composerVersion);
        }
        
        // 获取配置项
        $config = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0 || strpos($arg, '-') === 0 || $arg === 'list') {
                continue;
            }
            
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $config[$key] = $value;
            }
        }
        
        if (empty($config)) {
            echo "错误: 请指定要设置的配置项" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        try {
            echo "正在配置PHP {$phpVersion} 的Composer {$composerVersion}..." . PHP_EOL;
            $this->manager->configure($phpVersion, $composerVersion, $config);
            echo "Composer {$composerVersion} 配置成功" . PHP_EOL;
            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 显示当前配置
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return int
     */
    private function showConfig($phpVersion, $composerVersion)
    {
        try {
            $composerInfo = $this->manager->getComposerInfo($phpVersion, $composerVersion);
            
            if (!$composerInfo) {
                echo "错误: Composer {$composerVersion} 未安装于PHP {$phpVersion}" . PHP_EOL;
                return 1;
            }
            
            echo "PHP {$phpVersion} 的Composer {$composerVersion} 配置:" . PHP_EOL;
            
            if (isset($composerInfo['full_version'])) {
                echo "版本: " . $composerInfo['full_version'] . PHP_EOL;
            }
            
            if (isset($composerInfo['config']) && !empty($composerInfo['config'])) {
                echo "配置项:" . PHP_EOL;
                
                foreach ($composerInfo['config'] as $key => $value) {
                    echo "  {$key} = {$value}" . PHP_EOL;
                }
            } else {
                echo "没有配置项" . PHP_EOL;
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
        return '配置Composer';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm composer-config [选项] [配置项]...

配置Composer。

选项:
  --php=<版本>            指定PHP版本，默认为当前版本
  --version=<版本>        指定Composer版本，默认为默认版本
  list                   显示当前配置（默认操作）

配置项格式为 key=value，可以指定多个配置项。

示例:
  pvm composer-config list
  pvm composer-config --php=7.4.30 list
  pvm composer-config repo.packagist.org.url=https://mirrors.aliyun.com/composer
  pvm composer-config process-timeout=600
USAGE;
    }
}
