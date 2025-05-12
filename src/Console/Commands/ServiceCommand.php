<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\System\ServiceManager;
use VersionManager\Core\VersionSwitcher;

/**
 * 服务命令类
 */
class ServiceCommand implements CommandInterface
{
    /**
     * 服务管理器
     *
     * @var ServiceManager
     */
    private $serviceManager;
    
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
        $this->serviceManager = new ServiceManager($phpVersion);
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
            case 'fpm':
                return $this->manageFpm($args);
            case 'nginx':
                return $this->manageNginx($args);
            case 'apache':
                return $this->manageApache($args);
            case 'status':
                return $this->showStatus($args);
            default:
                echo "错误: 未知的子命令 '{$subcommand}'" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 管理PHP-FPM服务
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function manageFpm(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定PHP-FPM操作" . PHP_EOL;
            echo "可用操作: start, stop, restart, status" . PHP_EOL;
            return 1;
        }
        
        $action = array_shift($args);
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化服务管理器
        $this->serviceManager = new ServiceManager($phpVersion);
        
        switch ($action) {
            case 'start':
                return $this->startFpm();
            case 'stop':
                return $this->stopFpm();
            case 'restart':
                return $this->restartFpm();
            case 'status':
                return $this->fpmStatus();
            default:
                echo "错误: 未知的PHP-FPM操作 '{$action}'" . PHP_EOL;
                echo "可用操作: start, stop, restart, status" . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 启动PHP-FPM服务
     *
     * @return int 返回状态码
     */
    private function startFpm()
    {
        $success = $this->serviceManager->startFpm();
        return $success ? 0 : 1;
    }
    
    /**
     * 停止PHP-FPM服务
     *
     * @return int 返回状态码
     */
    private function stopFpm()
    {
        $success = $this->serviceManager->stopFpm();
        return $success ? 0 : 1;
    }
    
    /**
     * 重启PHP-FPM服务
     *
     * @return int 返回状态码
     */
    private function restartFpm()
    {
        $success = $this->serviceManager->restartFpm();
        return $success ? 0 : 1;
    }
    
    /**
     * 显示PHP-FPM状态
     *
     * @return int 返回状态码
     */
    private function fpmStatus()
    {
        $status = $this->serviceManager->getFpmStatus();
        
        if (!$status['running']) {
            echo "PHP-FPM未运行" . PHP_EOL;
            return 1;
        }
        
        echo "PHP-FPM状态:" . PHP_EOL;
        echo "  PID: {$status['pid']}" . PHP_EOL;
        
        if (isset($status['uptime'])) {
            $uptime = $status['uptime'];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            $seconds = $uptime % 60;
            
            $uptimeStr = '';
            if ($days > 0) {
                $uptimeStr .= "{$days}天 ";
            }
            if ($hours > 0) {
                $uptimeStr .= "{$hours}小时 ";
            }
            if ($minutes > 0) {
                $uptimeStr .= "{$minutes}分钟 ";
            }
            $uptimeStr .= "{$seconds}秒";
            
            echo "  运行时间: {$uptimeStr}" . PHP_EOL;
        }
        
        if (isset($status['process'])) {
            $process = $status['process'];
            echo "  主进程:" . PHP_EOL;
            echo "    PID: {$process['PID']}" . PHP_EOL;
            echo "    用户: {$process['USER']}" . PHP_EOL;
            echo "    CPU: {$process['%CPU']}%" . PHP_EOL;
            echo "    内存: {$process['%MEM']}%" . PHP_EOL;
        }
        
        if (isset($status['children']) && !empty($status['children'])) {
            echo "  子进程:" . PHP_EOL;
            
            foreach ($status['children'] as $child) {
                echo "    PID: {$child['PID']}" . PHP_EOL;
                echo "    用户: {$child['USER']}" . PHP_EOL;
                echo "    CPU: {$child['%CPU']}%" . PHP_EOL;
                echo "    内存: {$child['%MEM']}%" . PHP_EOL;
                echo PHP_EOL;
            }
        }
        
        return 0;
    }
    
    /**
     * 管理Nginx集成
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function manageNginx(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定Nginx操作" . PHP_EOL;
            echo "可用操作: install, uninstall" . PHP_EOL;
            return 1;
        }
        
        $action = array_shift($args);
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化服务管理器
        $this->serviceManager = new ServiceManager($phpVersion);
        
        switch ($action) {
            case 'install':
                return $this->installNginx($args, $options);
            case 'uninstall':
                return $this->uninstallNginx($args, $options);
            default:
                echo "错误: 未知的Nginx操作 '{$action}'" . PHP_EOL;
                echo "可用操作: install, uninstall" . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 安装Nginx配置
     *
     * @param array $args 命令参数
     * @param array $options 选项
     * @return int 返回状态码
     */
    private function installNginx(array $args, array $options)
    {
        if (count($args) < 2) {
            echo "错误: 请指定服务器名称和文档根目录" . PHP_EOL;
            echo "用法: pvm service nginx install <服务器名称> <文档根目录> [--port=<端口号>]" . PHP_EOL;
            return 1;
        }
        
        $serverName = $args[0];
        $documentRoot = $args[1];
        $port = isset($options['port']) ? (int) $options['port'] : 80;
        
        $success = $this->serviceManager->installNginxConfig($serverName, $documentRoot, $port);
        return $success ? 0 : 1;
    }
    
    /**
     * 卸载Nginx配置
     *
     * @param array $args 命令参数
     * @param array $options 选项
     * @return int 返回状态码
     */
    private function uninstallNginx(array $args, array $options)
    {
        if (empty($args)) {
            echo "错误: 请指定服务器名称" . PHP_EOL;
            echo "用法: pvm service nginx uninstall <服务器名称>" . PHP_EOL;
            return 1;
        }
        
        $serverName = $args[0];
        
        $success = $this->serviceManager->uninstallNginxConfig($serverName);
        return $success ? 0 : 1;
    }
    
    /**
     * 管理Apache集成
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function manageApache(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定Apache操作" . PHP_EOL;
            echo "可用操作: install, uninstall" . PHP_EOL;
            return 1;
        }
        
        $action = array_shift($args);
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化服务管理器
        $this->serviceManager = new ServiceManager($phpVersion);
        
        switch ($action) {
            case 'install':
                return $this->installApache($args, $options);
            case 'uninstall':
                return $this->uninstallApache($args, $options);
            default:
                echo "错误: 未知的Apache操作 '{$action}'" . PHP_EOL;
                echo "可用操作: install, uninstall" . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 安装Apache配置
     *
     * @param array $args 命令参数
     * @param array $options 选项
     * @return int 返回状态码
     */
    private function installApache(array $args, array $options)
    {
        if (count($args) < 2) {
            echo "错误: 请指定服务器名称和文档根目录" . PHP_EOL;
            echo "用法: pvm service apache install <服务器名称> <文档根目录> [--port=<端口号>]" . PHP_EOL;
            return 1;
        }
        
        $serverName = $args[0];
        $documentRoot = $args[1];
        $port = isset($options['port']) ? (int) $options['port'] : 80;
        
        $success = $this->serviceManager->installApacheConfig($serverName, $documentRoot, $port);
        return $success ? 0 : 1;
    }
    
    /**
     * 卸载Apache配置
     *
     * @param array $args 命令参数
     * @param array $options 选项
     * @return int 返回状态码
     */
    private function uninstallApache(array $args, array $options)
    {
        if (empty($args)) {
            echo "错误: 请指定服务器名称" . PHP_EOL;
            echo "用法: pvm service apache uninstall <服务器名称>" . PHP_EOL;
            return 1;
        }
        
        $serverName = $args[0];
        
        $success = $this->serviceManager->uninstallApacheConfig($serverName);
        return $success ? 0 : 1;
    }
    
    /**
     * 显示服务状态
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function showStatus(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化服务管理器
        $this->serviceManager = new ServiceManager($phpVersion);
        
        // 显示PHP-FPM状态
        $status = $this->serviceManager->getFpmStatus();
        
        echo "PHP {$phpVersion} 服务状态:" . PHP_EOL;
        echo "  PHP-FPM: " . ($status['running'] ? "运行中" : "未运行") . PHP_EOL;
        
        if ($status['running']) {
            echo "  PID: {$status['pid']}" . PHP_EOL;
            
            if (isset($status['uptime'])) {
                $uptime = $status['uptime'];
                $days = floor($uptime / 86400);
                $hours = floor(($uptime % 86400) / 3600);
                $minutes = floor(($uptime % 3600) / 60);
                $seconds = $uptime % 60;
                
                $uptimeStr = '';
                if ($days > 0) {
                    $uptimeStr .= "{$days}天 ";
                }
                if ($hours > 0) {
                    $uptimeStr .= "{$hours}小时 ";
                }
                if ($minutes > 0) {
                    $uptimeStr .= "{$minutes}分钟 ";
                }
                $uptimeStr .= "{$seconds}秒";
                
                echo "  运行时间: {$uptimeStr}" . PHP_EOL;
            }
            
            if (isset($status['children'])) {
                echo "  子进程数: " . count($status['children']) . PHP_EOL;
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
        return '管理PHP服务';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm service <子命令> [选项] [参数]...

管理PHP服务。

子命令:
  fpm <操作>              管理PHP-FPM服务
  nginx <操作>            管理Nginx集成
  apache <操作>           管理Apache集成
  status                  显示服务状态

PHP-FPM操作:
  start                   启动PHP-FPM服务
  stop                    停止PHP-FPM服务
  restart                 重启PHP-FPM服务
  status                  显示PHP-FPM状态

Nginx操作:
  install <服务器名称> <文档根目录>  安装Nginx配置
  uninstall <服务器名称>            卸载Nginx配置

Apache操作:
  install <服务器名称> <文档根目录>  安装Apache配置
  uninstall <服务器名称>            卸载Apache配置

选项:
  --php=<版本>             指定PHP版本，默认为当前版本
  --port=<端口号>          指定端口号，默认为80

示例:
  pvm service fpm start
  pvm service fpm stop
  pvm service fpm restart
  pvm service fpm status
  pvm service nginx install example.com /var/www/html
  pvm service nginx install example.com /var/www/html --port=8080
  pvm service nginx uninstall example.com
  pvm service apache install example.com /var/www/html
  pvm service apache uninstall example.com
  pvm service status
  pvm service status --php=7.4.33
USAGE;
    }
}
