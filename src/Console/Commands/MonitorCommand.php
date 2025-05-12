<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\System\MonitorManager;
use VersionManager\Core\VersionSwitcher;

/**
 * 监控命令类
 */
class MonitorCommand implements CommandInterface
{
    /**
     * 监控管理器
     *
     * @var MonitorManager
     */
    private $monitorManager;
    
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
        $this->monitorManager = new MonitorManager($phpVersion);
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
            case 'process':
                return $this->monitorProcess($args);
            case 'fpm':
                return $this->monitorFpm($args);
            case 'log':
                return $this->monitorLog($args);
            case 'system':
                return $this->monitorSystem($args);
            default:
                echo "错误: 未知的子命令 '{$subcommand}'" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 监控PHP进程
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function monitorProcess(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化监控管理器
        $this->monitorManager = new MonitorManager($phpVersion);
        
        // 获取PHP进程
        $processes = $this->monitorManager->getPhpProcesses();
        
        if (empty($processes)) {
            echo "没有PHP进程" . PHP_EOL;
            return 0;
        }
        
        echo "PHP进程:" . PHP_EOL;
        echo str_pad('PID', 8) . str_pad('PPID', 8) . str_pad('用户', 10) . str_pad('CPU', 6) . str_pad('内存', 6) . str_pad('状态', 6) . str_pad('启动时间', 10) . str_pad('运行时间', 10) . '命令' . PHP_EOL;
        
        foreach ($processes as $process) {
            echo str_pad($process['pid'], 8) . str_pad($process['ppid'], 8) . str_pad($process['user'], 10) . str_pad($process['cpu'], 6) . str_pad($process['mem'], 6) . str_pad($process['stat'], 6) . str_pad($process['start'], 10) . str_pad($process['time'], 10) . $process['command'] . PHP_EOL;
        }
        
        return 0;
    }
    
    /**
     * 监控PHP-FPM进程
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function monitorFpm(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化监控管理器
        $this->monitorManager = new MonitorManager($phpVersion);
        
        // 获取PHP-FPM进程
        $processes = $this->monitorManager->getFpmProcesses();
        
        if (empty($processes)) {
            echo "没有PHP-FPM进程" . PHP_EOL;
            return 0;
        }
        
        echo "PHP-FPM进程:" . PHP_EOL;
        echo str_pad('PID', 8) . str_pad('PPID', 8) . str_pad('用户', 10) . str_pad('CPU', 6) . str_pad('内存', 6) . str_pad('状态', 6) . str_pad('启动时间', 10) . str_pad('运行时间', 10) . '命令' . PHP_EOL;
        
        foreach ($processes as $process) {
            echo str_pad($process['pid'], 8) . str_pad($process['ppid'], 8) . str_pad($process['user'], 10) . str_pad($process['cpu'], 6) . str_pad($process['mem'], 6) . str_pad($process['stat'], 6) . str_pad($process['start'], 10) . str_pad($process['time'], 10) . $process['command'] . PHP_EOL;
        }
        
        // 获取PHP-FPM状态
        $status = $this->monitorManager->getFpmStatus();
        
        if ($status['running']) {
            echo PHP_EOL . "PHP-FPM状态:" . PHP_EOL;
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
     * 监控日志
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function monitorLog(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定要监控的日志类型" . PHP_EOL;
            echo "可用类型: php, fpm, access, slow" . PHP_EOL;
            return 1;
        }
        
        $type = array_shift($args);
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 获取行数
        $lines = isset($options['lines']) ? (int) $options['lines'] : 100;
        
        // 获取是否监控
        $watch = isset($options['watch']) && $options['watch'];
        
        // 重新初始化监控管理器
        $this->monitorManager = new MonitorManager($phpVersion);
        
        switch ($type) {
            case 'php':
                return $this->monitorPhpLog($lines, $watch);
            case 'fpm':
                return $this->monitorFpmLog($lines, $watch);
            case 'access':
                return $this->monitorAccessLog($lines, $watch);
            case 'slow':
                return $this->monitorSlowLog($lines, $watch);
            default:
                echo "错误: 未知的日志类型 '{$type}'" . PHP_EOL;
                echo "可用类型: php, fpm, access, slow" . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 监控PHP错误日志
     *
     * @param int $lines 行数
     * @param bool $watch 是否监控
     * @return int 返回状态码
     */
    private function monitorPhpLog($lines, $watch)
    {
        // 获取PHP错误日志
        $log = $this->monitorManager->getPhpErrorLog($lines);
        
        if (!$log['exists']) {
            echo "PHP错误日志文件不存在: {$log['file']}" . PHP_EOL;
            return 1;
        }
        
        if (empty($log['lines'])) {
            echo "PHP错误日志为空" . PHP_EOL;
            return 0;
        }
        
        echo "PHP错误日志 ({$log['file']}):" . PHP_EOL;
        
        foreach ($log['lines'] as $line) {
            echo $line . PHP_EOL;
        }
        
        // 如果需要监控，则使用tail -f命令
        if ($watch) {
            echo PHP_EOL . "监控PHP错误日志 (按Ctrl+C退出)..." . PHP_EOL;
            system("tail -f {$log['file']}");
        }
        
        return 0;
    }
    
    /**
     * 监控PHP-FPM错误日志
     *
     * @param int $lines 行数
     * @param bool $watch 是否监控
     * @return int 返回状态码
     */
    private function monitorFpmLog($lines, $watch)
    {
        // 获取PHP-FPM错误日志
        $log = $this->monitorManager->getFpmErrorLog($lines);
        
        if (!$log['exists']) {
            echo "PHP-FPM错误日志文件不存在: {$log['file']}" . PHP_EOL;
            return 1;
        }
        
        if (empty($log['lines'])) {
            echo "PHP-FPM错误日志为空" . PHP_EOL;
            return 0;
        }
        
        echo "PHP-FPM错误日志 ({$log['file']}):" . PHP_EOL;
        
        foreach ($log['lines'] as $line) {
            echo $line . PHP_EOL;
        }
        
        // 如果需要监控，则使用tail -f命令
        if ($watch) {
            echo PHP_EOL . "监控PHP-FPM错误日志 (按Ctrl+C退出)..." . PHP_EOL;
            system("tail -f {$log['file']}");
        }
        
        return 0;
    }
    
    /**
     * 监控PHP-FPM访问日志
     *
     * @param int $lines 行数
     * @param bool $watch 是否监控
     * @return int 返回状态码
     */
    private function monitorAccessLog($lines, $watch)
    {
        // 获取PHP-FPM访问日志
        $log = $this->monitorManager->getFpmAccessLog($lines);
        
        if (!$log['file']) {
            echo "PHP-FPM访问日志未配置" . PHP_EOL;
            return 1;
        }
        
        if (!$log['exists']) {
            echo "PHP-FPM访问日志文件不存在: {$log['file']}" . PHP_EOL;
            return 1;
        }
        
        if (empty($log['lines'])) {
            echo "PHP-FPM访问日志为空" . PHP_EOL;
            return 0;
        }
        
        echo "PHP-FPM访问日志 ({$log['file']}):" . PHP_EOL;
        
        foreach ($log['lines'] as $line) {
            echo $line . PHP_EOL;
        }
        
        // 如果需要监控，则使用tail -f命令
        if ($watch) {
            echo PHP_EOL . "监控PHP-FPM访问日志 (按Ctrl+C退出)..." . PHP_EOL;
            system("tail -f {$log['file']}");
        }
        
        return 0;
    }
    
    /**
     * 监控PHP-FPM慢日志
     *
     * @param int $lines 行数
     * @param bool $watch 是否监控
     * @return int 返回状态码
     */
    private function monitorSlowLog($lines, $watch)
    {
        // 获取PHP-FPM慢日志
        $log = $this->monitorManager->getFpmSlowLog($lines);
        
        if (!$log['file']) {
            echo "PHP-FPM慢日志未配置" . PHP_EOL;
            return 1;
        }
        
        if (!$log['exists']) {
            echo "PHP-FPM慢日志文件不存在: {$log['file']}" . PHP_EOL;
            return 1;
        }
        
        if (empty($log['lines'])) {
            echo "PHP-FPM慢日志为空" . PHP_EOL;
            return 0;
        }
        
        echo "PHP-FPM慢日志 ({$log['file']}):" . PHP_EOL;
        
        foreach ($log['lines'] as $line) {
            echo $line . PHP_EOL;
        }
        
        // 如果需要监控，则使用tail -f命令
        if ($watch) {
            echo PHP_EOL . "监控PHP-FPM慢日志 (按Ctrl+C退出)..." . PHP_EOL;
            system("tail -f {$log['file']}");
        }
        
        return 0;
    }
    
    /**
     * 监控系统
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function monitorSystem(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        // 重新初始化监控管理器
        $this->monitorManager = new MonitorManager($phpVersion);
        
        // 获取系统信息
        $info = $this->monitorManager->getSystemInfo();
        
        echo "系统信息:" . PHP_EOL;
        echo "  操作系统: {$info['os']}" . PHP_EOL;
        echo "  PHP版本: {$info['php_version']}" . PHP_EOL;
        echo "  PHP二进制文件: {$info['php_binary']}" . PHP_EOL;
        echo "  PHP-FPM二进制文件: {$info['php_fpm_binary']}" . PHP_EOL;
        echo "  PHP配置目录: {$info['php_config_dir']}" . PHP_EOL;
        echo "  PHP扩展目录: {$info['php_extension_dir']}" . PHP_EOL;
        echo "  PHP配置文件: {$info['php_ini']}" . PHP_EOL;
        echo "  PHP-FPM配置文件: {$info['php_fpm_conf']}" . PHP_EOL;
        echo "  PHP-FPM www配置文件: {$info['php_fpm_www_conf']}" . PHP_EOL;
        echo "  PHP-FPM状态: " . ($info['php_fpm_running'] ? "运行中" : "未运行") . PHP_EOL;
        
        echo PHP_EOL . "系统负载:" . PHP_EOL;
        echo "  1分钟: {$info['load']['1min']}" . PHP_EOL;
        echo "  5分钟: {$info['load']['5min']}" . PHP_EOL;
        echo "  15分钟: {$info['load']['15min']}" . PHP_EOL;
        
        if (isset($info['memory'])) {
            echo PHP_EOL . "内存信息:" . PHP_EOL;
            echo "  总内存: {$info['memory']['total_mb']} MB" . PHP_EOL;
            echo "  已用内存: {$info['memory']['used_mb']} MB" . PHP_EOL;
            echo "  空闲内存: {$info['memory']['free_mb']} MB" . PHP_EOL;
            echo "  共享内存: {$info['memory']['shared_mb']} MB" . PHP_EOL;
            echo "  缓冲区: {$info['memory']['buffers_mb']} MB" . PHP_EOL;
            echo "  缓存: {$info['memory']['cached_mb']} MB" . PHP_EOL;
        }
        
        if (isset($info['disk'])) {
            echo PHP_EOL . "磁盘信息:" . PHP_EOL;
            echo "  文件系统: {$info['disk']['filesystem']}" . PHP_EOL;
            echo "  总空间: {$info['disk']['total_gb']} GB" . PHP_EOL;
            echo "  已用空间: {$info['disk']['used_gb']} GB" . PHP_EOL;
            echo "  可用空间: {$info['disk']['available_gb']} GB" . PHP_EOL;
            echo "  使用率: {$info['disk']['percent']}%" . PHP_EOL;
            echo "  挂载点: {$info['disk']['mounted']}" . PHP_EOL;
        }
        
        // 获取内存使用情况
        $memoryUsage = $this->monitorManager->getMemoryUsage();
        
        echo PHP_EOL . "PHP内存使用情况:" . PHP_EOL;
        echo "  PHP进程数: {$memoryUsage['php']['processes']}" . PHP_EOL;
        echo "  PHP内存使用: {$memoryUsage['php']['rss_mb']} MB" . PHP_EOL;
        echo "  PHP-FPM进程数: {$memoryUsage['fpm']['processes']}" . PHP_EOL;
        echo "  PHP-FPM内存使用: {$memoryUsage['fpm']['rss_mb']} MB" . PHP_EOL;
        echo "  总进程数: {$memoryUsage['total']['processes']}" . PHP_EOL;
        echo "  总内存使用: {$memoryUsage['total']['rss_mb']} MB" . PHP_EOL;
        
        // 获取CPU使用情况
        $cpuUsage = $this->monitorManager->getCpuUsage();
        
        echo PHP_EOL . "PHP CPU使用情况:" . PHP_EOL;
        echo "  PHP进程数: {$cpuUsage['php']['processes']}" . PHP_EOL;
        echo "  PHP CPU使用: {$cpuUsage['php']['cpu']}%" . PHP_EOL;
        echo "  PHP-FPM进程数: {$cpuUsage['fpm']['processes']}" . PHP_EOL;
        echo "  PHP-FPM CPU使用: {$cpuUsage['fpm']['cpu']}%" . PHP_EOL;
        echo "  总进程数: {$cpuUsage['total']['processes']}" . PHP_EOL;
        echo "  总CPU使用: {$cpuUsage['total']['cpu']}%" . PHP_EOL;
        
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
        return '监控PHP进程和PHP-FPM状态';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm monitor <子命令> [选项] [参数]...

监控PHP进程和PHP-FPM状态。

子命令:
  process                 监控PHP进程
  fpm                     监控PHP-FPM进程
  log <类型>              监控日志
  system                  监控系统

日志类型:
  php                     PHP错误日志
  fpm                     PHP-FPM错误日志
  access                  PHP-FPM访问日志
  slow                    PHP-FPM慢日志

选项:
  --php=<版本>             指定PHP版本，默认为当前版本
  --lines=<行数>           指定日志行数，默认为100
  --watch                 监控日志文件变化

示例:
  pvm monitor process
  pvm monitor fpm
  pvm monitor log php
  pvm monitor log fpm
  pvm monitor log access
  pvm monitor log slow
  pvm monitor log php --lines=50
  pvm monitor log php --watch
  pvm monitor system
  pvm monitor system --php=7.4.33
USAGE;
    }
}
