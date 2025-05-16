<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;

/**
 * 更新命令类
 * 
 * 用于更新PVM自身
 */
class UpdateCommand implements CommandInterface
{
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmRoot;
    
    /**
     * PVM仓库目录
     *
     * @var string
     */
    private $repoDir;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->pvmRoot = getenv('HOME') . '/.pvm';
        $this->repoDir = $this->pvmRoot . '/repo';
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
        
        echo "正在更新PHP版本管理器(PVM)...\n\n";
        
        // 检查PVM仓库目录是否存在
        if (!is_dir($this->repoDir)) {
            echo "错误: PVM仓库目录不存在，无法更新\n";
            echo "请尝试重新安装PVM\n";
            return 1;
        }
        
        // 检查是否是Git仓库
        if (!$this->isGitRepository()) {
            echo "错误: PVM仓库目录不是有效的Git仓库，无法更新\n";
            echo "请尝试重新安装PVM\n";
            return 1;
        }
        
        // 检查是否有未提交的更改
        if ($this->hasUncommittedChanges() && !isset($options['force'])) {
            echo "警告: PVM仓库有未提交的更改\n";
            echo "使用 --force 选项强制更新\n";
            return 1;
        }
        
        // 更新仓库
        if (!$this->updateRepository()) {
            echo "错误: 更新PVM仓库失败\n";
            return 1;
        }
        
        // 更新依赖
        if (!$this->updateDependencies()) {
            echo "错误: 更新PVM依赖失败\n";
            return 1;
        }
        
        echo "\n✅ PVM更新成功\n";
        
        return 0;
    }
    
    /**
     * 检查是否是Git仓库
     *
     * @return bool
     */
    private function isGitRepository()
    {
        $gitDir = $this->repoDir . '/.git';
        return is_dir($gitDir);
    }
    
    /**
     * 检查是否有未提交的更改
     *
     * @return bool
     */
    private function hasUncommittedChanges()
    {
        $currentDir = getcwd();
        chdir($this->repoDir);
        
        $output = [];
        exec('git status --porcelain', $output);
        
        chdir($currentDir);
        
        return !empty($output);
    }
    
    /**
     * 更新仓库
     *
     * @return bool
     */
    private function updateRepository()
    {
        $currentDir = getcwd();
        chdir($this->repoDir);
        
        echo "正在从远程仓库拉取最新代码...\n";
        
        // 获取当前分支
        $output = [];
        exec('git rev-parse --abbrev-ref HEAD', $output);
        $currentBranch = $output[0] ?? 'main';
        
        // 拉取最新代码
        $command = "git pull origin {$currentBranch}";
        echo "执行: {$command}\n";
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        echo implode("\n", $output) . "\n";
        
        chdir($currentDir);
        
        return $returnCode === 0;
    }
    
    /**
     * 更新依赖
     *
     * @return bool
     */
    private function updateDependencies()
    {
        $currentDir = getcwd();
        chdir($this->repoDir);
        
        echo "正在更新Composer依赖...\n";
        
        // 检查composer.json是否存在
        if (!file_exists('composer.json')) {
            echo "错误: composer.json不存在\n";
            chdir($currentDir);
            return false;
        }
        
        // 更新依赖
        $command = 'composer install --no-dev';
        echo "执行: {$command}\n";
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        echo implode("\n", $output) . "\n";
        
        chdir($currentDir);
        
        return $returnCode === 0;
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
        return '更新PVM自身';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm update [选项]

更新PVM自身到最新版本。

选项:
  --force    强制更新，即使有未提交的更改

示例:
  pvm update
  pvm update --force
USAGE;
    }
}
