<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\Config\PhpConfig;
use VersionManager\Core\Config\FpmConfig;
use VersionManager\Core\VersionSwitcher;

/**
 * 配置命令类
 */
class ConfigCommand implements CommandInterface
{
    /**
     * PHP配置管理器
     *
     * @var PhpConfig
     */
    private $phpConfig;
    
    /**
     * FPM配置管理器
     *
     * @var FpmConfig
     */
    private $fpmConfig;
    
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
        $this->versionSwitcher = new VersionSwitcher();
        $phpVersion = $this->versionSwitcher->getCurrentVersion();
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
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
            echo $this->getUsage() . PHP_EOL;
            return 0;
        }
        
        $subcommand = array_shift($args);
        
        switch ($subcommand) {
            case 'list':
                return $this->listConfig($args);
            case 'set':
                return $this->setConfig($args);
            case 'get':
                return $this->getConfig($args);
            case 'apply':
                return $this->applyConfig($args);
            case 'backup':
                return $this->backupConfig($args);
            case 'restore':
                return $this->restoreConfig($args);
            default:
                echo "错误: 未知的子命令 '{$subcommand}'" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 列出配置
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function listConfig(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化配置管理器
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
        
        // 确定配置类型
        $type = isset($options['type']) ? $options['type'] : 'php';
        
        if ($type === 'php') {
            // 列出PHP配置
            $values = $this->phpConfig->getAllPhpIniValues();
            
            echo "PHP {$phpVersion} 配置:" . PHP_EOL;
            foreach ($values as $key => $value) {
                echo "  {$key} = {$value}" . PHP_EOL;
            }
        } elseif ($type === 'fpm') {
            // 列出FPM配置
            $section = isset($options['section']) ? $options['section'] : 'www';
            $file = isset($options['file']) ? $options['file'] : 'www';
            
            $values = $this->fpmConfig->getSectionValues($section, $file);
            
            echo "PHP-FPM {$phpVersion} [{$section}] 配置:" . PHP_EOL;
            foreach ($values as $key => $value) {
                echo "  {$key} = {$value}" . PHP_EOL;
            }
        } elseif ($type === 'backups') {
            // 列出配置备份
            $backups = $this->phpConfig->getConfigBackups();
            
            echo "PHP {$phpVersion} 配置备份:" . PHP_EOL;
            foreach ($backups as $backup) {
                echo "  {$backup['date']} - {$backup['file']}" . PHP_EOL;
            }
            
            $fpmBackups = $this->fpmConfig->getConfigBackups();
            
            if (!empty($fpmBackups['fpm'])) {
                echo PHP_EOL . "PHP-FPM {$phpVersion} 全局配置备份:" . PHP_EOL;
                foreach ($fpmBackups['fpm'] as $backup) {
                    echo "  {$backup['date']} - {$backup['file']}" . PHP_EOL;
                }
            }
            
            if (!empty($fpmBackups['www'])) {
                echo PHP_EOL . "PHP-FPM {$phpVersion} www配置备份:" . PHP_EOL;
                foreach ($fpmBackups['www'] as $backup) {
                    echo "  {$backup['date']} - {$backup['file']}" . PHP_EOL;
                }
            }
        } else {
            echo "错误: 未知的配置类型 '{$type}'" . PHP_EOL;
            return 1;
        }
        
        return 0;
    }
    
    /**
     * 设置配置
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function setConfig(array $args)
    {
        if (count($args) < 1) {
            echo "错误: 请指定要设置的配置项" . PHP_EOL;
            return 1;
        }
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化配置管理器
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
        
        // 确定配置类型
        $type = isset($options['type']) ? $options['type'] : 'php';
        
        // 解析配置项
        $config = [];
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                continue;
            }
            
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $config[$key] = $value;
            }
        }
        
        if (empty($config)) {
            echo "错误: 请指定要设置的配置项" . PHP_EOL;
            return 1;
        }
        
        if ($type === 'php') {
            // 设置PHP配置
            echo "设置PHP {$phpVersion} 配置..." . PHP_EOL;
            
            foreach ($config as $key => $value) {
                if ($this->phpConfig->setPhpIniValue($key, $value)) {
                    echo "  {$key} = {$value}" . PHP_EOL;
                } else {
                    echo "  错误: 无法设置 {$key}" . PHP_EOL;
                    return 1;
                }
            }
        } elseif ($type === 'fpm') {
            // 设置FPM配置
            $section = isset($options['section']) ? $options['section'] : 'www';
            $file = isset($options['file']) ? $options['file'] : 'www';
            
            echo "设置PHP-FPM {$phpVersion} [{$section}] 配置..." . PHP_EOL;
            
            foreach ($config as $key => $value) {
                if ($this->fpmConfig->setFpmValue($section, $key, $value, $file)) {
                    echo "  {$key} = {$value}" . PHP_EOL;
                } else {
                    echo "  错误: 无法设置 {$key}" . PHP_EOL;
                    return 1;
                }
            }
        } else {
            echo "错误: 未知的配置类型 '{$type}'" . PHP_EOL;
            return 1;
        }
        
        return 0;
    }
    
    /**
     * 获取配置
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function getConfig(array $args)
    {
        if (count($args) < 1) {
            echo "错误: 请指定要获取的配置项" . PHP_EOL;
            return 1;
        }
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化配置管理器
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
        
        // 确定配置类型
        $type = isset($options['type']) ? $options['type'] : 'php';
        
        // 获取配置项
        $key = $args[0];
        
        if ($type === 'php') {
            // 获取PHP配置
            $value = $this->phpConfig->getPhpIniValue($key);
            
            if ($value !== null) {
                echo "{$key} = {$value}" . PHP_EOL;
            } else {
                echo "错误: 未找到配置项 '{$key}'" . PHP_EOL;
                return 1;
            }
        } elseif ($type === 'fpm') {
            // 获取FPM配置
            $section = isset($options['section']) ? $options['section'] : 'www';
            $file = isset($options['file']) ? $options['file'] : 'www';
            
            $value = $this->fpmConfig->getFpmValue($section, $key, $file);
            
            if ($value !== null) {
                echo "{$key} = {$value}" . PHP_EOL;
            } else {
                echo "错误: 未找到配置项 '{$key}'" . PHP_EOL;
                return 1;
            }
        } else {
            echo "错误: 未知的配置类型 '{$type}'" . PHP_EOL;
            return 1;
        }
        
        return 0;
    }
    
    /**
     * 应用配置
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function applyConfig(array $args)
    {
        if (count($args) < 1) {
            echo "错误: 请指定要应用的配置模式" . PHP_EOL;
            return 1;
        }
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化配置管理器
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
        
        // 确定配置类型
        $type = isset($options['type']) ? $options['type'] : 'both';
        
        // 获取配置模式
        $mode = $args[0];
        
        if ($mode === 'development' || $mode === 'dev') {
            // 应用开发环境配置
            if ($type === 'both' || $type === 'php') {
                echo "应用PHP {$phpVersion} 开发环境配置..." . PHP_EOL;
                if ($this->phpConfig->applyDevelopmentConfig()) {
                    echo "  PHP配置应用成功" . PHP_EOL;
                } else {
                    echo "  错误: 无法应用PHP配置" . PHP_EOL;
                    return 1;
                }
            }
            
            if ($type === 'both' || $type === 'fpm') {
                echo "应用PHP-FPM {$phpVersion} 开发环境配置..." . PHP_EOL;
                if ($this->fpmConfig->applyDevelopmentConfig()) {
                    echo "  PHP-FPM配置应用成功" . PHP_EOL;
                } else {
                    echo "  错误: 无法应用PHP-FPM配置" . PHP_EOL;
                    return 1;
                }
            }
        } elseif ($mode === 'production' || $mode === 'prod') {
            // 应用生产环境配置
            if ($type === 'both' || $type === 'php') {
                echo "应用PHP {$phpVersion} 生产环境配置..." . PHP_EOL;
                if ($this->phpConfig->applyProductionConfig()) {
                    echo "  PHP配置应用成功" . PHP_EOL;
                } else {
                    echo "  错误: 无法应用PHP配置" . PHP_EOL;
                    return 1;
                }
            }
            
            if ($type === 'both' || $type === 'fpm') {
                echo "应用PHP-FPM {$phpVersion} 生产环境配置..." . PHP_EOL;
                if ($this->fpmConfig->applyProductionConfig()) {
                    echo "  PHP-FPM配置应用成功" . PHP_EOL;
                } else {
                    echo "  错误: 无法应用PHP-FPM配置" . PHP_EOL;
                    return 1;
                }
            }
        } else {
            echo "错误: 未知的配置模式 '{$mode}'" . PHP_EOL;
            return 1;
        }
        
        return 0;
    }
    
    /**
     * 备份配置
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function backupConfig(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化配置管理器
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
        
        // 确定配置类型
        $type = isset($options['type']) ? $options['type'] : 'both';
        
        if ($type === 'both' || $type === 'php') {
            echo "备份PHP {$phpVersion} 配置..." . PHP_EOL;
            if ($this->phpConfig->backupConfig()) {
                echo "  PHP配置备份成功" . PHP_EOL;
            } else {
                echo "  错误: 无法备份PHP配置" . PHP_EOL;
                return 1;
            }
        }
        
        if ($type === 'both' || $type === 'fpm') {
            echo "备份PHP-FPM {$phpVersion} 配置..." . PHP_EOL;
            if ($this->fpmConfig->backupConfig()) {
                echo "  PHP-FPM配置备份成功" . PHP_EOL;
            } else {
                echo "  错误: 无法备份PHP-FPM配置" . PHP_EOL;
                return 1;
            }
        }
        
        return 0;
    }
    
    /**
     * 恢复配置
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function restoreConfig(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化配置管理器
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
        
        // 确定配置类型
        $type = isset($options['type']) ? $options['type'] : 'both';
        
        // 获取备份文件
        $backupFile = isset($args[0]) ? $args[0] : null;
        
        if ($type === 'both' || $type === 'php') {
            echo "恢复PHP {$phpVersion} 配置..." . PHP_EOL;
            if ($this->phpConfig->restoreConfig($backupFile)) {
                echo "  PHP配置恢复成功" . PHP_EOL;
            } else {
                echo "  错误: 无法恢复PHP配置" . PHP_EOL;
                return 1;
            }
        }
        
        if ($type === 'both' || $type === 'fpm') {
            echo "恢复PHP-FPM {$phpVersion} 配置..." . PHP_EOL;
            if ($this->fpmConfig->restoreConfig($backupFile)) {
                echo "  PHP-FPM配置恢复成功" . PHP_EOL;
            } else {
                echo "  错误: 无法恢复PHP-FPM配置" . PHP_EOL;
                return 1;
            }
        }
        
        return 0;
    }
    
    /**
     * 解析选项
     *
     * @param array $args 命令参数
     * @return array
     */
    private function parseOptions(array &$args)
    {
        $options = [];
        $newArgs = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                
                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;
                }
            } else {
                $newArgs[] = $arg;
            }
        }
        
        $args = $newArgs;
        return $options;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '管理PHP配置';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm config <子命令> [选项] [参数]...

管理PHP配置。

子命令:
  list                    列出配置
  set <配置项>...          设置配置
  get <配置项>             获取配置
  apply <模式>             应用配置模式
  backup                  备份配置
  restore [备份文件]        恢复配置

选项:
  --php=<版本>             指定PHP版本，默认为当前版本
  --type=<类型>            指定配置类型，可以是php、fpm或both，默认为php
  --section=<节>           指定FPM配置节，默认为www
  --file=<文件>            指定FPM配置文件，可以是fpm或www，默认为www

模式:
  development, dev        开发环境配置
  production, prod        生产环境配置

示例:
  pvm config list
  pvm config list --type=fpm
  pvm config list --type=backups
  pvm config set display_errors=On
  pvm config set --type=fpm pm.max_children=10
  pvm config get memory_limit
  pvm config apply development
  pvm config apply production --type=fpm
  pvm config backup
  pvm config restore
USAGE;
    }
}
