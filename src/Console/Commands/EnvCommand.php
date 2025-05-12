<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\System\EnvironmentManager;

/**
 * 环境变量命令类
 */
class EnvCommand implements CommandInterface
{
    /**
     * 环境变量管理器
     *
     * @var EnvironmentManager
     */
    private $envManager;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->envManager = new EnvironmentManager();
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
                return $this->listEnv($args);
            case 'get':
                return $this->getEnv($args);
            case 'set':
                return $this->setEnv($args);
            case 'delete':
                return $this->deleteEnv($args);
            case 'install':
                return $this->installEnv($args);
            case 'uninstall':
                return $this->uninstallEnv($args);
            default:
                echo "错误: 未知的子命令 '{$subcommand}'" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 列出环境变量
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function listEnv(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取环境变量
        $includeSystem = isset($options['all']) && $options['all'];
        $env = $this->envManager->getAll($includeSystem);
        
        if (empty($env)) {
            echo "没有设置环境变量" . PHP_EOL;
            return 0;
        }
        
        echo "环境变量:" . PHP_EOL;
        
        foreach ($env as $key => $value) {
            echo "  {$key}={$value}" . PHP_EOL;
        }
        
        return 0;
    }
    
    /**
     * 获取环境变量
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function getEnv(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定要获取的环境变量名" . PHP_EOL;
            return 1;
        }
        
        $key = $args[0];
        $value = $this->envManager->get($key);
        
        if (empty($value)) {
            echo "环境变量 {$key} 未设置" . PHP_EOL;
            return 1;
        }
        
        echo "{$key}={$value}" . PHP_EOL;
        return 0;
    }
    
    /**
     * 设置环境变量
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function setEnv(array $args)
    {
        if (count($args) < 1) {
            echo "错误: 请指定要设置的环境变量" . PHP_EOL;
            return 1;
        }
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取是否持久化
        $persist = !isset($options['no-persist']) || !$options['no-persist'];
        
        // 解析环境变量
        $env = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $env[$key] = $value;
            }
        }
        
        if (empty($env)) {
            echo "错误: 请指定要设置的环境变量" . PHP_EOL;
            return 1;
        }
        
        // 设置环境变量
        $success = $this->envManager->setMultiple($env, $persist);
        
        if (!$success) {
            echo "错误: 无法设置环境变量" . PHP_EOL;
            return 1;
        }
        
        echo "环境变量已设置" . PHP_EOL;
        return 0;
    }
    
    /**
     * 删除环境变量
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function deleteEnv(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定要删除的环境变量名" . PHP_EOL;
            return 1;
        }
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取是否持久化
        $persist = !isset($options['no-persist']) || !$options['no-persist'];
        
        // 删除环境变量
        $success = $this->envManager->deleteMultiple($args, $persist);
        
        if (!$success) {
            echo "错误: 无法删除环境变量" . PHP_EOL;
            return 1;
        }
        
        echo "环境变量已删除" . PHP_EOL;
        return 0;
    }
    
    /**
     * 安装环境变量加载脚本
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function installEnv(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取Shell类型
        $shell = isset($options['shell']) ? $options['shell'] : 'bash';
        
        // 安装环境变量加载脚本
        $success = $this->envManager->installEnvScript($shell);
        
        if (!$success) {
            echo "错误: 无法安装环境变量加载脚本" . PHP_EOL;
            return 1;
        }
        
        echo "环境变量加载脚本已安装到 ~/.{$shell}rc" . PHP_EOL;
        echo "请运行 'source ~/.{$shell}rc' 或重新打开终端以使环境变量生效" . PHP_EOL;
        return 0;
    }
    
    /**
     * 卸载环境变量加载脚本
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function uninstallEnv(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取Shell类型
        $shell = isset($options['shell']) ? $options['shell'] : 'bash';
        
        // 卸载环境变量加载脚本
        $success = $this->envManager->uninstallEnvScript($shell);
        
        if (!$success) {
            echo "错误: 无法卸载环境变量加载脚本" . PHP_EOL;
            return 1;
        }
        
        echo "环境变量加载脚本已从 ~/.{$shell}rc 卸载" . PHP_EOL;
        echo "请运行 'source ~/.{$shell}rc' 或重新打开终端以使更改生效" . PHP_EOL;
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
        return '管理环境变量';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm env <子命令> [选项] [参数]...

管理环境变量。

子命令:
  list                    列出环境变量
  get <变量名>             获取环境变量
  set <变量名=值>...       设置环境变量
  delete <变量名>...       删除环境变量
  install                 安装环境变量加载脚本
  uninstall               卸载环境变量加载脚本

选项:
  --all                   列出所有环境变量，包括系统环境变量
  --no-persist            不持久化环境变量
  --shell=<shell>         指定Shell类型，可以是bash或zsh，默认为bash

示例:
  pvm env list
  pvm env list --all
  pvm env get PATH
  pvm env set FOO=bar BAZ=qux
  pvm env set FOO=bar --no-persist
  pvm env delete FOO BAZ
  pvm env install
  pvm env install --shell=zsh
  pvm env uninstall
USAGE;
    }
}
