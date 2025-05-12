<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\ExtensionManager;

/**
 * 扩展信息命令类
 */
class ExtInfoCommand implements CommandInterface
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
            echo "错误: 请指定要查看的扩展" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $extension = array_shift($args);
        
        try {
            $info = $this->manager->getExtensionInfo($extension);
            
            if (!$info) {
                echo "错误: 扩展 {$extension} 未安装或未配置" . PHP_EOL;
                return 1;
            }
            
            echo "扩展信息: {$extension}" . PHP_EOL;
            echo "----------------------------------------" . PHP_EOL;
            
            // 显示基本信息
            echo "名称: " . (isset($info['name']) ? $info['name'] : $extension) . PHP_EOL;
            echo "描述: " . (isset($info['description']) ? $info['description'] : '') . PHP_EOL;
            echo "版本: " . (isset($info['version']) ? $info['version'] : '') . PHP_EOL;
            echo "类型: " . (isset($info['type']) ? $info['type'] : '') . PHP_EOL;
            echo "状态: " . (isset($info['enabled']) && $info['enabled'] ? '已启用' : '已禁用') . PHP_EOL;
            
            // 显示依赖
            if (isset($info['dependencies']) && !empty($info['dependencies'])) {
                echo "依赖:" . PHP_EOL;
                foreach ($info['dependencies'] as $dependency) {
                    echo "  - {$dependency}" . PHP_EOL;
                }
            }
            
            // 显示配置
            if (isset($info['config']) && !empty($info['config'])) {
                echo "配置:" . PHP_EOL;
                foreach ($info['config'] as $key => $value) {
                    echo "  {$key} = {$value}" . PHP_EOL;
                }
            }
            
            // 显示PHP信息
            echo PHP_EOL;
            echo "PHP信息:" . PHP_EOL;
            echo "----------------------------------------" . PHP_EOL;
            
            // 使用PHP命令行获取扩展信息
            $output = [];
            $phpBin = $this->manager->getPhpBinary();
            exec($phpBin . ' -i | grep -A 20 "^' . $extension . '$"', $output);
            
            if (!empty($output)) {
                foreach ($output as $line) {
                    echo $line . PHP_EOL;
                }
            } else {
                echo "没有找到扩展 {$extension} 的PHP信息" . PHP_EOL;
            }
            
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
        return '显示PHP扩展信息';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm ext-info <扩展>

显示PHP扩展的详细信息。

示例:
  pvm ext-info mysqli
  pvm ext-info gd
  pvm ext-info xdebug
USAGE;
    }
}
