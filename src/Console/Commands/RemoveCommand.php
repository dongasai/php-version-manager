<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionRemover;
use VersionManager\Core\VersionDetector;
use Exception;

/**
 * 删除命令类
 * 
 * 用于处理PHP版本删除命令
 */
class RemoveCommand implements CommandInterface
{
    /**
     * 版本删除器
     *
     * @var VersionRemover
     */
    private $remover;
    
    /**
     * 版本检测器
     *
     * @var VersionDetector
     */
    private $detector;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->remover = new VersionRemover();
        $this->detector = new VersionDetector();
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
            echo "错误: 请指定PHP版本" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $versions = [];
        $options = [];
        
        // 解析参数
        foreach ($args as $arg) {
            if ($arg[0] === '-') {
                // 选项
                if ($arg === '--force' || $arg === '-f') {
                    $options['force'] = true;
                } elseif ($arg === '--skip-deps-check' || $arg === '-s') {
                    $options['skip_deps_check'] = true;
                } elseif ($arg === '--all' || $arg === '-a') {
                    $options['all'] = true;
                }
            } else {
                // 版本
                $versions[] = $arg;
            }
        }
        
        // 如果指定了--all选项，则删除所有版本
        if (isset($options['all'])) {
            $versions = $this->detector->getInstalledVersions();
            
            if (empty($versions)) {
                echo "没有已安装的PHP版本" . PHP_EOL;
                return 0;
            }
        }
        
        // 批量删除
        if (count($versions) > 1) {
            return $this->batchRemove($versions, $options);
        }
        
        // 单个删除
        $version = $versions[0];
        
        try {
            // 删除PHP版本
            $this->remover->remove($version, $options);
            echo "PHP版本 {$version} 删除成功" . PHP_EOL;
            return 0;
        } catch (Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 批量删除PHP版本
     *
     * @param array $versions PHP版本列表
     * @param array $options 删除选项
     * @return int 返回状态码
     */
    private function batchRemove(array $versions, array $options)
    {
        echo "批量删除PHP版本:" . PHP_EOL;
        
        $results = $this->remover->batchRemove($versions, $options);
        $success = 0;
        $failed = 0;
        
        foreach ($results as $version => $result) {
            if ($result) {
                echo "  - {$version}: 删除成功" . PHP_EOL;
                $success++;
            } else {
                echo "  - {$version}: 删除失败" . PHP_EOL;
                $failed++;
            }
        }
        
        echo PHP_EOL;
        echo "删除结果: {$success} 成功, {$failed} 失败" . PHP_EOL;
        
        return $failed > 0 ? 1 : 0;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '删除已安装的PHP版本';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm remove <版本> [选项]

删除已安装的PHP版本。

参数:
  <版本>  要删除的PHP版本，例如 7.4.33, 8.1.27
         可以指定多个版本，用空格分隔

选项:
  --force, -f           强制删除，即使是当前版本或全局版本
  --skip-deps-check, -s 跳过依赖检查
  --all, -a             删除所有已安装的PHP版本

示例:
  pvm remove 7.4.33
  pvm remove 8.1.27 --force
  pvm remove 7.4.33 8.1.27
  pvm remove --all
USAGE;
    }
}
