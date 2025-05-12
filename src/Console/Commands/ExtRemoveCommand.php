<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ExtensionManager;

/**
 * 扩展删除命令类
 */
class ExtRemoveCommand implements CommandInterface
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
            echo "错误: 请指定要删除的扩展" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $extension = array_shift($args);
        $options = $this->parseOptions($args);
        
        try {
            echo "正在删除扩展 {$extension}..." . PHP_EOL;
            $this->manager->removeExtension($extension, $options);
            echo "扩展 {$extension} 删除成功" . PHP_EOL;
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
        return '删除PHP扩展';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm ext-remove <扩展> [选项]

删除PHP扩展。

选项:
  --force                 强制删除
  --disable               仅禁用扩展（对于内置扩展）

示例:
  pvm ext-remove mysqli
  pvm ext-remove redis --force
  pvm ext-remove gd --disable
USAGE;
    }
}
