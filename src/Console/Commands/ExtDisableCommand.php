<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ExtensionManager;

/**
 * 扩展禁用命令类
 */
class ExtDisableCommand implements CommandInterface
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
            echo "错误: 请指定要禁用的扩展" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $extension = array_shift($args);
        
        try {
            echo "正在禁用扩展 {$extension}..." . PHP_EOL;
            $this->manager->disableExtension($extension);
            echo "扩展 {$extension} 禁用成功" . PHP_EOL;
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
        return '禁用PHP扩展';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm ext-disable <扩展>

禁用PHP扩展。

示例:
  pvm ext-disable mysqli
  pvm ext-disable gd
  pvm ext-disable xdebug
USAGE;
    }
}
