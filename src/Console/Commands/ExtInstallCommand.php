<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ExtensionManager;

/**
 * 扩展安装命令类
 */
class ExtInstallCommand implements CommandInterface
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
        if (empty($args)) {
            echo "错误: 请指定要安装的扩展" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $extension = array_shift($args);
        $options = $this->parseOptions($args);
        
        try {
            echo "正在安装扩展 {$extension}..." . PHP_EOL;
            $this->manager->installExtension($extension, $options);
            echo "扩展 {$extension} 安装成功" . PHP_EOL;
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
        return '安装PHP扩展';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm ext-install <扩展> [选项]

安装PHP扩展。

选项:
  --version=<版本>         指定扩展版本
  --force                 强制安装
  --zend                  指定为Zend扩展
  --source=<源码URL>        从源码安装
  --pecl                  从 PECL 安装
  --config=<配置项>         扩展配置项，格式为 key=value

示例:
  pvm ext-install mysqli
  pvm ext-install redis --version=5.3.7
  pvm ext-install gd --config=jpeg_ignore_warning=1
  pvm ext-install xdebug --zend
USAGE;
    }
}
