<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ExtensionManager;

/**
 * 扩展列表命令类
 */
class ExtListCommand implements CommandInterface
{
    /**
     * 扩展管理器
     *
     * @var ExtensionManager
     */
    private $manager;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->manager = new ExtensionManager();
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        $options = $this->parseOptions($args);
        
        // 获取已安装的扩展列表
        $installedExtensions = $this->manager->getInstalledExtensions();
        
        // 获取可用的扩展列表
        $availableExtensions = $this->manager->getAvailableExtensions();
        
        // 显示已安装的扩展列表
        echo "已安装的扩展:" . PHP_EOL;
        if (empty($installedExtensions)) {
            echo "  没有已安装的扩展" . PHP_EOL;
        } else {
            foreach ($installedExtensions as $name => $info) {
                $status = isset($info['enabled']) && $info['enabled'] ? '已启用' : '已禁用';
                $type = isset($info['type']) ? $info['type'] : '';
                $version = isset($info['version']) ? $info['version'] : '';
                
                echo "  * {$name}";
                if (!empty($version)) {
                    echo " ({$version})";
                }
                echo " [{$status}]";
                if (!empty($type)) {
                    echo " [{$type}]";
                }
                echo PHP_EOL;
                
                // 如果指定了详细模式，则显示扩展配置
                if (isset($options['verbose']) && $options['verbose']) {
                    if (isset($info['config']) && !empty($info['config'])) {
                        echo "    配置:" . PHP_EOL;
                        foreach ($info['config'] as $key => $value) {
                            echo "      {$key} = {$value}" . PHP_EOL;
                        }
                    }
                }
            }
        }
        
        echo PHP_EOL;
        
        // 显示可用的扩展列表
        echo "可用的扩展:" . PHP_EOL;
        if (empty($availableExtensions)) {
            echo "  没有可用的扩展" . PHP_EOL;
        } else {
            foreach ($availableExtensions as $name => $info) {
                $description = isset($info['description']) ? $info['description'] : '';
                $version = isset($info['version']) ? $info['version'] : '';
                
                echo "  * {$name}";
                if (!empty($version)) {
                    echo " ({$version})";
                }
                if (!empty($description)) {
                    echo " - {$description}";
                }
                echo PHP_EOL;
            }
        }
        
        return 0;
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
            if ($arg === '-v' || $arg === '--verbose') {
                $options['verbose'] = true;
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
        return '列出PHP扩展';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm ext-list [选项]

列出已安装和可用的PHP扩展。

选项:
  -v, --verbose           显示详细信息

示例:
  pvm ext-list
  pvm ext-list --verbose
USAGE;
    }
}
