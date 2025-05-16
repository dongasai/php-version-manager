<?php

namespace Mirror\Server;

/**
 * 服务器管理器类
 */
class ServerManager
{
    /**
     * 服务器PID文件
     *
     * @var string
     */
    private $pidFile;

    /**
     * 日志文件
     *
     * @var string
     */
    private $logFile;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->pidFile = ROOT_DIR . '/.server.pid';
        
        // 确保日志目录存在
        $logDir = ROOT_DIR . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->logFile = $logDir . '/server.log';
    }

    /**
     * 启动服务器
     *
     * @param int $port 端口号
     * @return bool 是否成功
     */
    public function start($port)
    {
        // 检查服务器是否已经在运行
        if (file_exists($this->pidFile) && $this->isRunning()) {
            $pid = file_get_contents($this->pidFile);
            echo "服务器已经在运行 (PID: $pid)\n";
            return false;
        }
        
        // 启动服务器
        echo "启动镜像服务器 (端口: $port)...\n";
        
        // 构建命令
        $command = sprintf(
            'nohup php -S 0.0.0.0:%d -t %s/public/ > %s 2>&1 & echo $!',
            $port,
            ROOT_DIR,
            $this->logFile
        );
        
        // 执行命令
        $pid = exec($command);
        
        // 保存PID
        file_put_contents($this->pidFile, $pid);
        
        echo "服务器已启动 (PID: $pid)\n";
        echo "日志文件: {$this->logFile}\n";
        echo "访问地址: http://localhost:$port/\n";
        
        return true;
    }

    /**
     * 停止服务器
     *
     * @return bool 是否成功
     */
    public function stop()
    {
        // 检查服务器是否在运行
        if (!file_exists($this->pidFile)) {
            echo "服务器未运行\n";
            return false;
        }
        
        $pid = file_get_contents($this->pidFile);
        
        // 检查进程是否存在
        if (!$this->isRunning()) {
            echo "服务器未运行 (PID文件可能已过期)\n";
            unlink($this->pidFile);
            return false;
        }
        
        // 停止服务器
        echo "停止镜像服务器 (PID: $pid)...\n";
        exec("kill $pid");
        
        // 删除PID文件
        unlink($this->pidFile);
        
        echo "服务器已停止\n";
        
        return true;
    }

    /**
     * 重启服务器
     *
     * @param int $port 端口号
     * @return bool 是否成功
     */
    public function restart($port)
    {
        // 先停止服务器
        $this->stop();
        
        // 等待一会儿
        sleep(2);
        
        // 再启动服务器
        return $this->start($port);
    }

    /**
     * 显示服务器状态
     *
     * @return bool 是否在运行
     */
    public function status()
    {
        // 检查服务器是否在运行
        if (!file_exists($this->pidFile)) {
            echo "服务器未运行\n";
            return false;
        }
        
        $pid = file_get_contents($this->pidFile);
        
        // 检查进程是否存在
        if (!$this->isRunning()) {
            echo "服务器未运行 (PID文件可能已过期)\n";
            unlink($this->pidFile);
            return false;
        }
        
        // 获取服务器信息
        $port = $this->getPort();
        
        echo "服务器正在运行\n";
        echo "PID: $pid\n";
        
        if ($port) {
            echo "端口: $port\n";
            echo "访问地址: http://localhost:$port/\n";
        }
        
        echo "日志文件: {$this->logFile}\n";
        
        return true;
    }

    /**
     * 检查服务器是否在运行
     *
     * @return bool
     */
    public function isRunning()
    {
        if (!file_exists($this->pidFile)) {
            return false;
        }
        
        $pid = file_get_contents($this->pidFile);
        
        // 在Linux/Unix系统上检查进程是否存在
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        }
        
        // 在Windows系统上检查进程是否存在
        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec("tasklist /FI \"PID eq $pid\" 2>NUL", $output);
            return count($output) > 1;
        }
        
        // 通用方法
        $output = [];
        exec("ps -p $pid", $output);
        return count($output) > 1;
    }

    /**
     * 获取服务器端口
     *
     * @return int|null
     */
    private function getPort()
    {
        if (!file_exists($this->pidFile)) {
            return null;
        }
        
        $pid = file_get_contents($this->pidFile);
        
        // 在Linux/Unix系统上获取端口
        $output = [];
        exec("netstat -tlnp 2>/dev/null | grep $pid", $output);
        
        foreach ($output as $line) {
            if (preg_match('/:\s*(\d+)\s+/', $line, $matches)) {
                return (int)$matches[1];
            }
        }
        
        return null;
    }
}
