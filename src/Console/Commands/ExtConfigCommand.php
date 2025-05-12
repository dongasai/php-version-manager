<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ExtensionManager;

/**
 * 扩展配置命令类
 */
class ExtConfigCommand implements CommandInterface
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
        if (count($args) < 1) {
            echo "错误: 请指定要配置的扩展" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $extension = array_shift($args);
        
        // 如果没有其他参数，则显示当前配置
        if (empty($args)) {
            $config = $this->manager->getExtensionInfo($extension);
            
            if (!$config) {
                echo "错误: 扩展 {$extension} 未安装或未配置" . PHP_EOL;
                return 1;
            }
            
            echo "扩展 {$extension} 的当前配置:" . PHP_EOL;
            foreach ($config as $key => $value) {
                echo "  {$key} = {$value}" . PHP_EOL;
            }
            
            return 0;
        }
        
        // 解析配置项
        $config = [];
        foreach ($args as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $config[$key] = $value;
            } elseif (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                
                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    $config[$key] = $value;
                } else {
                    $config[$option] = true;
                }
            }
        }
        
        if (empty($config)) {
            echo "错误: 请指定至少一个配置项" . PHP_EOL;
            return 1;
        }
        
        try {
            echo "正在配置扩展 {$extension}..." . PHP_EOL;
            $this->manager->configureExtension($extension, $config);
            echo "扩展 {$extension} 配置成功" . PHP_EOL;
            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
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
        return '配置PHP扩展';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm ext-config <扩展> [配置项]...

配置PHP扩展。

如果不指定配置项，则显示当前配置。
配置项格式为 key=value，可以指定多个配置项。

选项:
  --zend                  指定为Zend扩展

示例:
  pvm ext-config mysqli                           # 显示mysqli扩展的当前配置
  pvm ext-config mysqli default_host=localhost    # 设置mysqli扩展的默认主机
  pvm ext-config gd jpeg_ignore_warning=1         # 设置gd扩展的JPEG警告忽略
  pvm ext-config xdebug --zend                    # 将xdebug设置为Zend扩展
USAGE;
    }
}
