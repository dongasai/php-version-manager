<?php

namespace VersionManager\Core\Process;

/**
 * Process 类
 * 
 * 用于执行外部命令，替代 Symfony Process 组件
 */
class Process
{
    /**
     * 命令
     *
     * @var array|string
     */
    private $command;
    
    /**
     * 工作目录
     *
     * @var string|null
     */
    private $cwd;
    
    /**
     * 环境变量
     *
     * @var array|null
     */
    private $env;
    
    /**
     * 超时时间（秒）
     *
     * @var int
     */
    private $timeout = 60;
    
    /**
     * 进程输出
     *
     * @var string
     */
    private $output = '';
    
    /**
     * 进程错误输出
     *
     * @var string
     */
    private $errorOutput = '';
    
    /**
     * 退出码
     *
     * @var int|null
     */
    private $exitCode = null;
    
    /**
     * 构造函数
     *
     * @param array|string $command 命令
     * @param string|null $cwd 工作目录
     * @param array|null $env 环境变量
     */
    public function __construct($command, $cwd = null, array $env = null)
    {
        $this->command = $command;
        $this->cwd = $cwd;
        $this->env = $env;
    }
    
    /**
     * 设置超时时间
     *
     * @param int $timeout 超时时间（秒）
     * @return self
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
        return $this;
    }
    
    /**
     * 运行命令
     *
     * @param callable|null $callback 回调函数，用于处理实时输出
     * @return int 退出码
     */
    public function run(callable $callback = null)
    {
        // 准备命令
        $command = $this->prepareCommand();
        
        // 准备描述符
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];
        
        // 准备环境变量
        $env = $this->env;
        
        // 打开进程
        $process = proc_open($command, $descriptorspec, $pipes, $this->cwd, $env);
        
        if (!is_resource($process)) {
            throw new \RuntimeException('无法启动进程');
        }
        
        // 关闭stdin
        fclose($pipes[0]);
        
        // 设置非阻塞模式
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        
        $this->output = '';
        $this->errorOutput = '';
        
        // 设置超时时间
        $startTime = time();
        
        // 循环读取输出，直到进程结束或超时
        while (true) {
            $status = proc_get_status($process);
            
            // 读取stdout
            $stdout = fread($pipes[1], 4096);
            if ($stdout !== false && $stdout !== '') {
                $this->output .= $stdout;
                
                if ($callback !== null) {
                    call_user_func($callback, 'out', $stdout);
                }
            }
            
            // 读取stderr
            $stderr = fread($pipes[2], 4096);
            if ($stderr !== false && $stderr !== '') {
                $this->errorOutput .= $stderr;
                
                if ($callback !== null) {
                    call_user_func($callback, 'err', $stderr);
                }
            }
            
            // 检查进程是否已结束
            if (!$status['running']) {
                // 读取剩余输出
                $stdout = stream_get_contents($pipes[1]);
                if ($stdout !== false && $stdout !== '') {
                    $this->output .= $stdout;
                    
                    if ($callback !== null) {
                        call_user_func($callback, 'out', $stdout);
                    }
                }
                
                $stderr = stream_get_contents($pipes[2]);
                if ($stderr !== false && $stderr !== '') {
                    $this->errorOutput .= $stderr;
                    
                    if ($callback !== null) {
                        call_user_func($callback, 'err', $stderr);
                    }
                }
                
                break;
            }
            
            // 检查是否超时
            if ($this->timeout > 0 && (time() - $startTime) > $this->timeout) {
                proc_terminate($process);
                throw new \RuntimeException('进程执行超时');
            }
            
            // 避免 CPU 占用过高
            usleep(10000); // 10ms
        }
        
        // 关闭管道
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        // 获取退出码
        $this->exitCode = proc_close($process);
        
        return $this->exitCode;
    }
    
    /**
     * 准备命令
     *
     * @return string 准备好的命令
     */
    private function prepareCommand()
    {
        if (is_array($this->command)) {
            return implode(' ', array_map(function ($arg) {
                return escapeshellarg($arg);
            }, $this->command));
        }
        
        return $this->command;
    }
    
    /**
     * 获取输出
     *
     * @return string 输出
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * 获取错误输出
     *
     * @return string 错误输出
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }
    
    /**
     * 获取退出码
     *
     * @return int|null 退出码
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
