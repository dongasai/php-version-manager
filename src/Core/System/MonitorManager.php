<?php

namespace VersionManager\Core\System;

use VersionManager\Core\Config\PhpConfig;
use VersionManager\Core\Config\FpmConfig;

/**
 * 监控管理类
 * 
 * 负责监控PHP进程和PHP-FPM状态
 */
class MonitorManager
{
    /**
     * PHP版本
     *
     * @var string
     */
    private $phpVersion;
    
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmDir;
    
    /**
     * 版本目录
     *
     * @var string
     */
    private $versionDir;
    
    /**
     * PHP配置管理器
     *
     * @var PhpConfig
     */
    private $phpConfig;
    
    /**
     * FPM配置管理器
     *
     * @var FpmConfig
     */
    private $fpmConfig;
    
    /**
     * 服务管理器
     *
     * @var ServiceManager
     */
    private $serviceManager;
    
    /**
     * 构造函数
     *
     * @param string $phpVersion PHP版本
     */
    public function __construct($phpVersion = null)
    {
        if ($phpVersion === null) {
            // 获取当前PHP版本
            $switcher = new \VersionManager\Core\VersionSwitcher();
            $phpVersion = $switcher->getCurrentVersion();
        }
        
        $this->phpVersion = $phpVersion;
        $this->pvmDir = getenv('HOME') . '/.pvm';
        $this->versionDir = $this->pvmDir . '/versions/' . $phpVersion;
        $this->phpConfig = new PhpConfig($phpVersion);
        $this->fpmConfig = new FpmConfig($phpVersion);
        $this->serviceManager = new ServiceManager($phpVersion);
    }
    
    /**
     * 获取PHP进程列表
     *
     * @return array PHP进程列表
     */
    public function getPhpProcesses()
    {
        // 获取PHP二进制文件路径
        $phpBin = $this->versionDir . '/bin/php';
        
        // 获取PHP进程
        $command = "ps -eo pid,ppid,user,%cpu,%mem,vsz,rss,stat,start,time,command | grep {$phpBin} | grep -v grep";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析进程信息
        $processes = [];
        if (!empty($output)) {
            foreach ($output as $line) {
                $parts = preg_split('/\s+/', trim($line), 11);
                
                if (count($parts) >= 11) {
                    $processes[] = [
                        'pid' => $parts[0],
                        'ppid' => $parts[1],
                        'user' => $parts[2],
                        'cpu' => $parts[3],
                        'mem' => $parts[4],
                        'vsz' => $parts[5],
                        'rss' => $parts[6],
                        'stat' => $parts[7],
                        'start' => $parts[8],
                        'time' => $parts[9],
                        'command' => $parts[10],
                    ];
                }
            }
        }
        
        return $processes;
    }
    
    /**
     * 获取PHP-FPM进程列表
     *
     * @return array PHP-FPM进程列表
     */
    public function getFpmProcesses()
    {
        // 获取PHP-FPM二进制文件路径
        $fpmBin = $this->versionDir . '/sbin/php-fpm';
        
        // 获取PHP-FPM进程
        $command = "ps -eo pid,ppid,user,%cpu,%mem,vsz,rss,stat,start,time,command | grep {$fpmBin} | grep -v grep";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析进程信息
        $processes = [];
        if (!empty($output)) {
            foreach ($output as $line) {
                $parts = preg_split('/\s+/', trim($line), 11);
                
                if (count($parts) >= 11) {
                    $processes[] = [
                        'pid' => $parts[0],
                        'ppid' => $parts[1],
                        'user' => $parts[2],
                        'cpu' => $parts[3],
                        'mem' => $parts[4],
                        'vsz' => $parts[5],
                        'rss' => $parts[6],
                        'stat' => $parts[7],
                        'start' => $parts[8],
                        'time' => $parts[9],
                        'command' => $parts[10],
                    ];
                }
            }
        }
        
        return $processes;
    }
    
    /**
     * 获取PHP-FPM状态
     *
     * @return array PHP-FPM状态信息
     */
    public function getFpmStatus()
    {
        return $this->serviceManager->getFpmStatus();
    }
    
    /**
     * 获取PHP错误日志
     *
     * @param int $lines 行数
     * @return array 错误日志
     */
    public function getPhpErrorLog($lines = 100)
    {
        // 获取错误日志路径
        $errorLog = $this->phpConfig->getPhpIniValue('error_log');
        
        // 如果没有配置错误日志，则使用默认路径
        if (empty($errorLog)) {
            $errorLog = '/tmp/php_errors.log';
        }
        
        // 检查错误日志文件是否存在
        if (!file_exists($errorLog)) {
            return [
                'file' => $errorLog,
                'exists' => false,
                'lines' => [],
            ];
        }
        
        // 读取错误日志
        $command = "tail -n {$lines} {$errorLog}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        return [
            'file' => $errorLog,
            'exists' => true,
            'lines' => $output,
        ];
    }
    
    /**
     * 获取PHP-FPM错误日志
     *
     * @param int $lines 行数
     * @return array 错误日志
     */
    public function getFpmErrorLog($lines = 100)
    {
        // 获取错误日志路径
        $errorLog = $this->fpmConfig->getFpmValue('global', 'error_log', 'fpm');
        
        // 如果没有配置错误日志，则使用默认路径
        if (empty($errorLog)) {
            $errorLog = '/tmp/php-fpm-error.log';
        }
        
        // 检查错误日志文件是否存在
        if (!file_exists($errorLog)) {
            return [
                'file' => $errorLog,
                'exists' => false,
                'lines' => [],
            ];
        }
        
        // 读取错误日志
        $command = "tail -n {$lines} {$errorLog}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        return [
            'file' => $errorLog,
            'exists' => true,
            'lines' => $output,
        ];
    }
    
    /**
     * 获取PHP-FPM访问日志
     *
     * @param int $lines 行数
     * @return array 访问日志
     */
    public function getFpmAccessLog($lines = 100)
    {
        // 获取访问日志路径
        $accessLog = $this->fpmConfig->getFpmValue('www', 'access.log', 'www');
        
        // 如果没有配置访问日志，则返回空
        if (empty($accessLog)) {
            return [
                'file' => null,
                'exists' => false,
                'lines' => [],
            ];
        }
        
        // 检查访问日志文件是否存在
        if (!file_exists($accessLog)) {
            return [
                'file' => $accessLog,
                'exists' => false,
                'lines' => [],
            ];
        }
        
        // 读取访问日志
        $command = "tail -n {$lines} {$accessLog}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        return [
            'file' => $accessLog,
            'exists' => true,
            'lines' => $output,
        ];
    }
    
    /**
     * 获取PHP-FPM慢日志
     *
     * @param int $lines 行数
     * @return array 慢日志
     */
    public function getFpmSlowLog($lines = 100)
    {
        // 获取慢日志路径
        $slowLog = $this->fpmConfig->getFpmValue('www', 'slowlog', 'www');
        
        // 如果没有配置慢日志，则返回空
        if (empty($slowLog)) {
            return [
                'file' => null,
                'exists' => false,
                'lines' => [],
            ];
        }
        
        // 检查慢日志文件是否存在
        if (!file_exists($slowLog)) {
            return [
                'file' => $slowLog,
                'exists' => false,
                'lines' => [],
            ];
        }
        
        // 读取慢日志
        $command = "tail -n {$lines} {$slowLog}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        return [
            'file' => $slowLog,
            'exists' => true,
            'lines' => $output,
        ];
    }
    
    /**
     * 获取PHP内存使用情况
     *
     * @return array 内存使用情况
     */
    public function getMemoryUsage()
    {
        // 获取PHP进程
        $phpProcesses = $this->getPhpProcesses();
        
        // 计算内存使用情况
        $totalRss = 0;
        $totalVsz = 0;
        
        foreach ($phpProcesses as $process) {
            $totalRss += (int) $process['rss'];
            $totalVsz += (int) $process['vsz'];
        }
        
        // 获取PHP-FPM进程
        $fpmProcesses = $this->getFpmProcesses();
        
        // 计算内存使用情况
        $fpmTotalRss = 0;
        $fpmTotalVsz = 0;
        
        foreach ($fpmProcesses as $process) {
            $fpmTotalRss += (int) $process['rss'];
            $fpmTotalVsz += (int) $process['vsz'];
        }
        
        return [
            'php' => [
                'processes' => count($phpProcesses),
                'rss' => $totalRss,
                'vsz' => $totalVsz,
                'rss_mb' => round($totalRss / 1024, 2),
                'vsz_mb' => round($totalVsz / 1024, 2),
            ],
            'fpm' => [
                'processes' => count($fpmProcesses),
                'rss' => $fpmTotalRss,
                'vsz' => $fpmTotalVsz,
                'rss_mb' => round($fpmTotalRss / 1024, 2),
                'vsz_mb' => round($fpmTotalVsz / 1024, 2),
            ],
            'total' => [
                'processes' => count($phpProcesses) + count($fpmProcesses),
                'rss' => $totalRss + $fpmTotalRss,
                'vsz' => $totalVsz + $fpmTotalVsz,
                'rss_mb' => round(($totalRss + $fpmTotalRss) / 1024, 2),
                'vsz_mb' => round(($totalVsz + $fpmTotalVsz) / 1024, 2),
            ],
        ];
    }
    
    /**
     * 获取PHP CPU使用情况
     *
     * @return array CPU使用情况
     */
    public function getCpuUsage()
    {
        // 获取PHP进程
        $phpProcesses = $this->getPhpProcesses();
        
        // 计算CPU使用情况
        $totalCpu = 0;
        
        foreach ($phpProcesses as $process) {
            $totalCpu += (float) $process['cpu'];
        }
        
        // 获取PHP-FPM进程
        $fpmProcesses = $this->getFpmProcesses();
        
        // 计算CPU使用情况
        $fpmTotalCpu = 0;
        
        foreach ($fpmProcesses as $process) {
            $fpmTotalCpu += (float) $process['cpu'];
        }
        
        return [
            'php' => [
                'processes' => count($phpProcesses),
                'cpu' => $totalCpu,
            ],
            'fpm' => [
                'processes' => count($fpmProcesses),
                'cpu' => $fpmTotalCpu,
            ],
            'total' => [
                'processes' => count($phpProcesses) + count($fpmProcesses),
                'cpu' => $totalCpu + $fpmTotalCpu,
            ],
        ];
    }
    
    /**
     * 获取PHP配置信息
     *
     * @return array PHP配置信息
     */
    public function getPhpConfig()
    {
        // 获取PHP二进制文件路径
        $phpBin = $this->versionDir . '/bin/php';
        
        // 获取PHP配置信息
        $command = "{$phpBin} -i";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析配置信息
        $config = [];
        $section = null;
        
        foreach ($output as $line) {
            $line = trim($line);
            
            // 跳过空行
            if (empty($line)) {
                continue;
            }
            
            // 检查是否是节标题
            if (preg_match('/^(\w[\w\s]+)( =>|--->)$/', $line, $matches)) {
                $section = $matches[1];
                $config[$section] = [];
                continue;
            }
            
            // 检查是否是配置项
            if (strpos($line, '=>') !== false) {
                list($key, $value) = explode('=>', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                if ($section !== null) {
                    $config[$section][$key] = $value;
                } else {
                    $config[$key] = $value;
                }
            }
        }
        
        return $config;
    }
    
    /**
     * 获取PHP扩展信息
     *
     * @return array PHP扩展信息
     */
    public function getPhpExtensions()
    {
        // 获取PHP二进制文件路径
        $phpBin = $this->versionDir . '/bin/php';
        
        // 获取PHP扩展信息
        $command = "{$phpBin} -m";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析扩展信息
        $extensions = [];
        $section = null;
        
        foreach ($output as $line) {
            $line = trim($line);
            
            // 跳过空行
            if (empty($line)) {
                continue;
            }
            
            // 检查是否是节标题
            if ($line === '[PHP Modules]') {
                $section = 'modules';
                continue;
            } elseif ($line === '[Zend Modules]') {
                $section = 'zend_modules';
                continue;
            }
            
            // 添加扩展
            if ($section !== null) {
                $extensions[$section][] = $line;
            }
        }
        
        return $extensions;
    }
    
    /**
     * 获取系统信息
     *
     * @return array 系统信息
     */
    public function getSystemInfo()
    {
        // 获取系统信息
        $info = [
            'os' => php_uname(),
            'php_version' => $this->phpVersion,
            'php_binary' => $this->versionDir . '/bin/php',
            'php_fpm_binary' => $this->versionDir . '/sbin/php-fpm',
            'php_config_dir' => $this->versionDir . '/etc',
            'php_extension_dir' => $this->versionDir . '/lib/php/extensions',
            'php_ini' => $this->phpConfig->getPhpIniPath(),
            'php_fpm_conf' => $this->fpmConfig->getFpmConfPath(),
            'php_fpm_www_conf' => $this->fpmConfig->getFpmWwwConfPath(),
            'php_fpm_running' => $this->serviceManager->isFpmRunning(),
        ];
        
        // 获取系统负载
        $load = sys_getloadavg();
        $info['load'] = [
            '1min' => $load[0],
            '5min' => $load[1],
            '15min' => $load[2],
        ];
        
        // 获取内存信息
        $memInfo = $this->getMemoryInfo();
        $info['memory'] = $memInfo;
        
        // 获取磁盘信息
        $diskInfo = $this->getDiskInfo();
        $info['disk'] = $diskInfo;
        
        return $info;
    }
    
    /**
     * 获取内存信息
     *
     * @return array 内存信息
     */
    private function getMemoryInfo()
    {
        // 获取内存信息
        $command = "free -b";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析内存信息
        $memInfo = [];
        
        if (count($output) >= 2) {
            $parts = preg_split('/\s+/', trim($output[1]));
            
            if (count($parts) >= 7) {
                $memInfo = [
                    'total' => (int) $parts[1],
                    'used' => (int) $parts[2],
                    'free' => (int) $parts[3],
                    'shared' => (int) $parts[4],
                    'buffers' => (int) $parts[5],
                    'cached' => (int) $parts[6],
                    'total_mb' => round((int) $parts[1] / 1024 / 1024, 2),
                    'used_mb' => round((int) $parts[2] / 1024 / 1024, 2),
                    'free_mb' => round((int) $parts[3] / 1024 / 1024, 2),
                    'shared_mb' => round((int) $parts[4] / 1024 / 1024, 2),
                    'buffers_mb' => round((int) $parts[5] / 1024 / 1024, 2),
                    'cached_mb' => round((int) $parts[6] / 1024 / 1024, 2),
                ];
            }
        }
        
        return $memInfo;
    }
    
    /**
     * 获取磁盘信息
     *
     * @return array 磁盘信息
     */
    private function getDiskInfo()
    {
        // 获取磁盘信息
        $command = "df -B1 " . $this->pvmDir;
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析磁盘信息
        $diskInfo = [];
        
        if (count($output) >= 2) {
            $parts = preg_split('/\s+/', trim($output[1]));
            
            if (count($parts) >= 6) {
                $diskInfo = [
                    'filesystem' => $parts[0],
                    'total' => (int) $parts[1],
                    'used' => (int) $parts[2],
                    'available' => (int) $parts[3],
                    'percent' => (int) rtrim($parts[4], '%'),
                    'mounted' => $parts[5],
                    'total_gb' => round((int) $parts[1] / 1024 / 1024 / 1024, 2),
                    'used_gb' => round((int) $parts[2] / 1024 / 1024 / 1024, 2),
                    'available_gb' => round((int) $parts[3] / 1024 / 1024 / 1024, 2),
                ];
            }
        }
        
        return $diskInfo;
    }
}
