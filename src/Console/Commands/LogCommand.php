<?php

namespace VersionManager\Console\Commands;

use VersionManager\Core\Logger\FileLogger;

/**
 * 日志管理命令
 */
class LogCommand
{
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
            return 1;
        }

        $action = $args[0];

        switch ($action) {
            case 'show':
            case 'view':
                return $this->showLog(array_slice($args, 1));
            case 'list':
                return $this->listLogs();
            case 'path':
                return $this->showLogPath();
            case 'clear':
                return $this->clearLogs(array_slice($args, 1));
            case 'tail':
                return $this->tailLog(array_slice($args, 1));
            default:
                echo "未知操作: {$action}" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }

    /**
     * 显示日志内容
     *
     * @param array $args 参数
     * @return int 返回状态码
     */
    private function showLog(array $args)
    {
        $lines = 50; // 默认显示最后50行
        $logFile = null;

        // 解析参数
        foreach ($args as $arg) {
            if (strpos($arg, '--lines=') === 0) {
                $lines = (int) substr($arg, 8);
            } elseif (strpos($arg, '-n') === 0) {
                $lines = (int) substr($arg, 2);
            } elseif (!$logFile && strpos($arg, '-') !== 0) {
                $logFile = $arg;
            }
        }

        // 如果没有指定日志文件，使用当前日志文件
        if (!$logFile) {
            $logFile = FileLogger::getCurrentLogFile();
            if (!$logFile) {
                echo "错误: 没有找到当前日志文件" . PHP_EOL;
                return 1;
            }
        } else {
            // 如果指定了相对路径，转换为绝对路径
            if (strpos($logFile, '/') !== 0) {
                $logDir = $this->getLogRootDir();
                $logFile = $logDir . '/' . $logFile;
            }
        }

        if (!file_exists($logFile)) {
            echo "错误: 日志文件不存在: {$logFile}" . PHP_EOL;
            return 1;
        }

        // 显示日志内容
        $this->displayLogFile($logFile, $lines);
        return 0;
    }

    /**
     * 列出所有日志文件
     *
     * @return int 返回状态码
     */
    private function listLogs()
    {
        $logDir = $this->getLogRootDir();
        
        if (!is_dir($logDir)) {
            echo "日志目录不存在: {$logDir}" . PHP_EOL;
            return 1;
        }

        echo "日志文件列表:" . PHP_EOL;
        echo "日志目录: {$logDir}" . PHP_EOL;
        echo str_repeat("=", 60) . PHP_EOL;

        $this->listLogFiles($logDir, $logDir);
        return 0;
    }

    /**
     * 显示当前日志文件路径
     *
     * @return int 返回状态码
     */
    private function showLogPath()
    {
        $currentLogFile = FileLogger::getCurrentLogFile();
        
        if ($currentLogFile) {
            echo "当前日志文件: {$currentLogFile}" . PHP_EOL;
        } else {
            echo "当前没有活动的日志文件" . PHP_EOL;
        }

        $logDir = $this->getLogRootDir();
        echo "日志根目录: {$logDir}" . PHP_EOL;

        return 0;
    }

    /**
     * 清理日志文件
     *
     * @param array $args 参数
     * @return int 返回状态码
     */
    private function clearLogs(array $args)
    {
        $days = 30; // 默认保留30天
        $force = false;

        // 解析参数
        foreach ($args as $arg) {
            if (strpos($arg, '--days=') === 0) {
                $days = (int) substr($arg, 7);
            } elseif ($arg === '--force' || $arg === '-f') {
                $force = true;
            }
        }

        $logDir = $this->getLogRootDir();
        
        if (!is_dir($logDir)) {
            echo "日志目录不存在: {$logDir}" . PHP_EOL;
            return 1;
        }

        if (!$force) {
            echo "将删除 {$days} 天前的日志文件，是否继续？(y/n) ";
            $handle = fopen("php://stdin", "r");
            $line = trim(fgets($handle));
            fclose($handle);

            if (strtolower($line) !== 'y') {
                echo "操作已取消" . PHP_EOL;
                return 0;
            }
        }

        $deletedCount = $this->deleteOldLogs($logDir, $days);
        echo "已删除 {$deletedCount} 个过期日志文件" . PHP_EOL;

        return 0;
    }

    /**
     * 实时查看日志
     *
     * @param array $args 参数
     * @return int 返回状态码
     */
    private function tailLog(array $args)
    {
        $logFile = FileLogger::getCurrentLogFile();
        $lines = 10; // 默认显示最后10行

        // 解析参数
        foreach ($args as $arg) {
            if (strpos($arg, '--lines=') === 0) {
                $lines = (int) substr($arg, 8);
            } elseif (strpos($arg, '-n') === 0) {
                $lines = (int) substr($arg, 2);
            } elseif (strpos($arg, '-') !== 0) {
                $logFile = $arg;
            }
        }

        if (!$logFile) {
            echo "错误: 没有找到当前日志文件" . PHP_EOL;
            return 1;
        }

        if (!file_exists($logFile)) {
            echo "错误: 日志文件不存在: {$logFile}" . PHP_EOL;
            return 1;
        }

        // 显示最后几行
        $this->displayLogFile($logFile, $lines);

        echo PHP_EOL . "正在监控日志文件 (按 Ctrl+C 退出)..." . PHP_EOL;

        // 实时监控文件变化
        $lastSize = filesize($logFile);
        while (true) {
            clearstatcache();
            $currentSize = filesize($logFile);
            
            if ($currentSize > $lastSize) {
                $handle = fopen($logFile, 'r');
                fseek($handle, $lastSize);
                while (($line = fgets($handle)) !== false) {
                    echo $line;
                }
                fclose($handle);
                $lastSize = $currentSize;
            }
            
            usleep(500000); // 0.5秒检查一次
        }

        return 0;
    }

    /**
     * 显示日志文件内容
     *
     * @param string $logFile 日志文件路径
     * @param int $lines 显示行数
     */
    private function displayLogFile($logFile, $lines)
    {
        $command = "tail -n {$lines} " . escapeshellarg($logFile);
        system($command);
    }

    /**
     * 递归列出日志文件
     *
     * @param string $dir 目录路径
     * @param string $baseDir 基础目录
     * @param string $prefix 前缀
     */
    private function listLogFiles($dir, $baseDir, $prefix = '')
    {
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $dir . '/' . $item;
            $relativePath = $prefix . $item;
            
            if (is_dir($fullPath)) {
                echo "[目录] {$relativePath}/" . PHP_EOL;
                $this->listLogFiles($fullPath, $baseDir, $relativePath . '/');
            } else {
                $size = filesize($fullPath);
                $sizeStr = $this->formatFileSize($size);
                $mtime = date('Y-m-d H:i:s', filemtime($fullPath));
                echo "[文件] {$relativePath} ({$sizeStr}, {$mtime})" . PHP_EOL;
            }
        }
    }

    /**
     * 删除过期日志文件
     *
     * @param string $dir 日志目录
     * @param int $days 保留天数
     * @return int 删除的文件数量
     */
    private function deleteOldLogs($dir, $days)
    {
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $deletedCount = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'log') {
                if ($file->getMTime() < $cutoffTime) {
                    unlink($file->getPathname());
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }

    /**
     * 格式化文件大小
     *
     * @param int $size 文件大小（字节）
     * @return string 格式化后的大小
     */
    private function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * 获取日志根目录
     *
     * @return string
     */
    private function getLogRootDir()
    {
        // 检测是否在开发模式（项目目录中运行）
        $projectRoot = dirname(dirname(dirname(__DIR__)));
        $isDevMode = $this->isDevMode($projectRoot);

        // 开发模式：优先使用项目的 logs 目录
        if ($isDevMode) {
            $projectLogDir = $projectRoot . '/logs';
            return $projectLogDir;
        }

        // 生产模式：使用 PVM 目录下的 log 文件夹
        $homeDir = getenv('HOME');
        $pvmLogDir = $homeDir . '/.pvm/log';

        // 如果 PVM 目录存在，使用它
        if (is_dir($homeDir . '/.pvm')) {
            return $pvmLogDir;
        }

        // 最后备选：使用项目根目录下的 log 文件夹（向后兼容）
        return $projectRoot . '/log';
    }

    /**
     * 检测是否为开发模式
     *
     * @param string $projectRoot 项目根目录
     * @return bool 是否为开发模式
     */
    private function isDevMode($projectRoot)
    {
        // 检查当前工作目录是否在项目目录内
        $currentDir = getcwd();
        $realProjectRoot = realpath($projectRoot);
        $realCurrentDir = realpath($currentDir);

        // 如果无法获取真实路径，使用原始路径比较
        if ($realProjectRoot === false) {
            $realProjectRoot = $projectRoot;
        }
        if ($realCurrentDir === false) {
            $realCurrentDir = $currentDir;
        }

        // 检查是否在项目目录或其子目录中
        $isInProjectDir = strpos($realCurrentDir, $realProjectRoot) === 0;

        // 检查项目标识文件是否存在（composer.json, bin/pvm等）
        $hasProjectFiles = file_exists($projectRoot . '/composer.json') &&
                          file_exists($projectRoot . '/bin/pvm') &&
                          is_dir($projectRoot . '/src');

        // 检查是否有开发环境标识
        $hasDevIndicators = is_dir($projectRoot . '/docker') ||
                           is_dir($projectRoot . '/tests') ||
                           file_exists($projectRoot . '/docker-compose.yml');

        return $isInProjectDir && $hasProjectFiles && $hasDevIndicators;
    }

    /**
     * 获取使用说明
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm log <操作> [选项]

日志管理命令，用于查看和管理PVM的日志文件。

操作:
  show, view          显示日志内容
  list               列出所有日志文件
  path               显示日志文件路径
  clear              清理过期日志文件
  tail               实时查看日志

选项:
  --lines=<数量>      显示的行数，默认50行
  -n<数量>           显示的行数（简写形式）
  --days=<天数>       清理时保留的天数，默认30天
  --force, -f        强制执行，不询问确认

示例:
  pvm log show                    # 显示当前日志文件的最后50行
  pvm log show --lines=100        # 显示最后100行
  pvm log show 2024/01/15/10-30-45.log  # 显示指定日志文件
  pvm log list                    # 列出所有日志文件
  pvm log path                    # 显示日志文件路径
  pvm log clear --days=7          # 清理7天前的日志
  pvm log tail                    # 实时查看当前日志
USAGE;
    }
}
