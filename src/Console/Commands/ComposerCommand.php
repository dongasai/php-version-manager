<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\VersionSwitcher;

/**
 * Composer命令类
 * 
 * 用于管理Composer的安装、卸载和版本切换
 */
class ComposerCommand implements CommandInterface
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
        // 如果没有参数，显示帮助信息
        if (empty($args)) {
            return $this->showHelp();
        }
        
        // 获取子命令
        $subCommand = array_shift($args);
        
        // 执行子命令
        switch ($subCommand) {
            case 'install':
                return $this->installComposer($args);
                
            case 'remove':
                return $this->removeComposer($args);
                
            case 'use':
                return $this->useComposer($args);
                
            case 'list':
                return $this->listComposers($args);
                
            case 'config':
                return $this->configComposer($args);
                
            case 'info':
                return $this->showComposerInfo($args);
                
            case 'help':
                return $this->showHelp();
                
            default:
                echo "错误: 未知的子命令 '{$subCommand}'" . PHP_EOL;
                return $this->showHelp();
        }
    }
    
    /**
     * 安装Composer
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function installComposer(array $args)
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
     * 卸载Composer
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function removeComposer(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 获取Composer版本
        if (!isset($options['version'])) {
            echo "错误: 请指定要删除的Composer版本" . PHP_EOL;
            echo "用法: pvm composer remove --version=<版本> [--php=<版本>]" . PHP_EOL;
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
     * 切换Composer版本
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function useComposer(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 获取Composer版本
        if (empty($args) && !isset($options['version'])) {
            echo "错误: 请指定要使用的Composer版本" . PHP_EOL;
            echo "用法: pvm composer use <版本> [--php=<版本>]" . PHP_EOL;
            return 1;
        }
        
        $composerVersion = isset($options['version']) ? $options['version'] : $args[0];
        
        try {
            echo "正在将PHP {$phpVersion} 的Composer {$composerVersion} 设置为默认版本..." . PHP_EOL;
            $this->manager->setDefaultComposer($phpVersion, $composerVersion);
            echo "Composer {$composerVersion} 已设置为默认版本" . PHP_EOL;
            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 列出已安装的Composer
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function listComposers(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : null;
        
        // 获取已安装的Composer列表
        $composerList = $this->manager->getInstalledComposers();
        
        // 获取默认Composer配置
        $defaultConfig = $this->manager->getDefaultComposerConfig();
        
        if (empty($composerList)) {
            echo "没有安装任何Composer" . PHP_EOL;
            return 0;
        }
        
        echo "已安装的Composer列表:" . PHP_EOL;
        
        foreach ($composerList as $version => $composers) {
            // 如果指定了PHP版本，则只显示该版本的Composer
            if ($phpVersion !== null && $phpVersion !== $version) {
                continue;
            }
            
            echo "PHP {$version}:" . PHP_EOL;
            
            foreach ($composers as $composer) {
                $isDefault = ($defaultConfig && $defaultConfig['php_version'] === $version && $defaultConfig['composer_version'] === $composer);
                echo "  " . ($isDefault ? "* " : "  ") . $composer . PHP_EOL;
            }
        }
        
        if ($defaultConfig) {
            echo "\n默认Composer: PHP {$defaultConfig['php_version']} 的 Composer {$defaultConfig['composer_version']}" . PHP_EOL;
        }
        
        return 0;
    }
    
    /**
     * 配置Composer
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function configComposer(array $args)
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
            return $this->showComposerConfig($phpVersion, $composerVersion);
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
            echo "用法: pvm composer config [--php=<版本>] [--version=<版本>] <key>=<value> [<key>=<value> ...]" . PHP_EOL;
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
     * 显示Composer配置
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return int 返回状态码
     */
    private function showComposerConfig($phpVersion, $composerVersion)
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
     * 显示Composer信息
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function showComposerInfo(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 获取Composer版本
        $defaultConfig = $this->manager->getDefaultComposerConfig();
        $composerVersion = isset($options['version']) ? $options['version'] : ($defaultConfig ? $defaultConfig['composer_version'] : '2');
        
        try {
            $composerInfo = $this->manager->getComposerInfo($phpVersion, $composerVersion);
            
            if (!$composerInfo) {
                echo "错误: Composer {$composerVersion} 未安装于PHP {$phpVersion}" . PHP_EOL;
                return 1;
            }
            
            echo "PHP {$phpVersion} 的Composer {$composerVersion} 信息:" . PHP_EOL;
            
            if (isset($composerInfo['full_version'])) {
                echo "版本: " . $composerInfo['full_version'] . PHP_EOL;
            }
            
            // 显示是否为默认版本
            $isDefault = ($defaultConfig && $defaultConfig['php_version'] === $phpVersion && $defaultConfig['composer_version'] === $composerVersion);
            echo "默认版本: " . ($isDefault ? "是" : "否") . PHP_EOL;
            
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
     * 显示帮助信息
     *
     * @return int 返回状态码
     */
    private function showHelp()
    {
        echo $this->getUsage() . PHP_EOL;
        return 0;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '管理Composer';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm composer <子命令> [选项] [参数]

管理Composer的安装、卸载和版本切换。

子命令:
  install                 安装Composer
  remove                  卸载Composer
  use                     切换Composer版本
  list                    列出已安装的Composer
  config                  配置Composer
  info                    显示Composer信息
  help                    显示此帮助信息

选项:
  --php=<版本>            指定PHP版本，默认为当前版本
  --version=<版本>        指定Composer版本，默认为'2'
  --default               设置为默认Composer（仅适用于install命令）
  --mirror=<镜像名称>      使用指定的镜像下载（仅适用于install命令）

示例:
  pvm composer install                        # 安装最新的Composer 2.x
  pvm composer install --version=1            # 安装最新的Composer 1.x
  pvm composer install --version=2.3.10       # 安装指定版本的Composer
  pvm composer install --php=7.4.30           # 为指定PHP版本安装Composer
  pvm composer install --default              # 安装并设置为默认版本
  pvm composer remove --version=1             # 卸载Composer 1.x
  pvm composer use 2                          # 切换到Composer 2.x
  pvm composer list                           # 列出所有已安装的Composer
  pvm composer list --php=7.4.30              # 列出指定PHP版本的Composer
  pvm composer config repo.packagist.org.url=https://mirrors.aliyun.com/composer  # 配置Composer镜像
  pvm composer info                           # 显示当前Composer信息
USAGE;
    }
}
