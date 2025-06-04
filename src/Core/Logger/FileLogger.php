<?php

namespace VersionManager\Core\Logger;

/**
 * 文件日志记录器
 */
class FileLogger
{
    /**
     * 日志根目录
     *
     * @var string
     */
    private static $logDir = null;

    /**
     * 当前日志文件路径
     *
     * @var string
     */
    private static $currentLogFile = null;

    /**
     * 命令开始时间
     *
     * @var string
     */
    private static $commandStartTime = null;

    /**
     * 是否启用文件日志
     *
     * @var bool
     */
    private static $enabled = true;

    /**
     * 初始化文件日志系统
     *
     * @param string $command 当前执行的命令
     * @param array $args 命令参数
     */
    public static function init($command = '', $args = [])
    {
        // 设置日志根目录
        self::$logDir = self::getLogRootDir();

        // 记录命令开始时间
        self::$commandStartTime = date('Y-m-d H:i:s');

        // 创建日志文件路径
        self::$currentLogFile = self::createLogFilePath();

        // 确保日志目录存在
        self::ensureLogDirectoryExists();

        // 记录命令开始
        self::logCommandStart($command, $args);
    }

    /**
     * 获取日志根目录
     *
     * @return string
     */
    private static function getLogRootDir()
    {
        // 检测是否在开发模式（项目目录中运行）
        $projectRoot = dirname(dirname(dirname(__DIR__)));
        $isDevMode = self::isDevMode($projectRoot);

        // 开发模式：优先使用项目的 logs 目录
        if ($isDevMode) {
            $projectLogDir = $projectRoot . '/logs';
            // 确保项目logs目录存在
            if (!is_dir($projectLogDir)) {
                mkdir($projectLogDir, 0755, true);
            }
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
    private static function isDevMode($projectRoot)
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
     * 创建日志文件路径
     *
     * @return string
     */
    private static function createLogFilePath()
    {
        $now = new \DateTime();
        
        // 格式：年/月/日/时-分-秒.log
        $year = $now->format('Y');
        $month = $now->format('m');
        $day = $now->format('d');
        $time = $now->format('H-i-s');

        return self::$logDir . "/{$year}/{$month}/{$day}/{$time}.log";
    }

    /**
     * 确保日志目录存在
     */
    private static function ensureLogDirectoryExists()
    {
        if (self::$currentLogFile) {
            $logFileDir = dirname(self::$currentLogFile);
            if (!is_dir($logFileDir)) {
                mkdir($logFileDir, 0755, true);
            }
        }
    }

    /**
     * 记录命令开始
     *
     * @param string $command 命令名称
     * @param array $args 命令参数
     */
    private static function logCommandStart($command, $args)
    {
        $argsStr = implode(' ', $args);
        $message = "=== 命令开始 ===";
        $details = [
            "命令: {$command}",
            "参数: {$argsStr}",
            "开始时间: " . self::$commandStartTime,
            "PID: " . getmypid(),
            "用户: " . get_current_user(),
            "工作目录: " . getcwd()
        ];

        self::writeToFile($message);
        foreach ($details as $detail) {
            self::writeToFile($detail);
        }
        self::writeToFile(""); // 空行分隔
    }

    /**
     * 记录命令结束
     *
     * @param int $exitCode 退出代码
     */
    public static function logCommandEnd($exitCode = 0)
    {
        $endTime = date('Y-m-d H:i:s');
        $duration = self::calculateDuration(self::$commandStartTime, $endTime);

        $message = "=== 命令结束 ===";
        $details = [
            "结束时间: {$endTime}",
            "执行时长: {$duration}",
            "退出代码: {$exitCode}",
            "状态: " . ($exitCode === 0 ? '成功' : '失败')
        ];

        self::writeToFile(""); // 空行分隔
        self::writeToFile($message);
        foreach ($details as $detail) {
            self::writeToFile($detail);
        }
    }

    /**
     * 记录信息级别日志
     *
     * @param string $message 日志消息
     * @param string $category 日志分类
     */
    public static function info($message, $category = 'INFO')
    {
        self::writeLog('INFO', $message, $category);
    }

    /**
     * 记录警告级别日志
     *
     * @param string $message 日志消息
     * @param string $category 日志分类
     */
    public static function warning($message, $category = 'WARNING')
    {
        self::writeLog('WARNING', $message, $category);
    }

    /**
     * 记录错误级别日志
     *
     * @param string $message 日志消息
     * @param string $category 日志分类
     */
    public static function error($message, $category = 'ERROR')
    {
        self::writeLog('ERROR', $message, $category);
    }

    /**
     * 记录调试级别日志
     *
     * @param string $message 日志消息
     * @param string $category 日志分类
     */
    public static function debug($message, $category = 'DEBUG')
    {
        self::writeLog('DEBUG', $message, $category);
    }

    /**
     * 记录下载开始日志
     *
     * @param string $url 下载URL
     * @param string $destination 目标路径
     * @param int $fileSize 文件大小（如果已知）
     */
    public static function logDownloadStart($url, $destination, $fileSize = 0)
    {
        $message = "开始下载文件";
        $details = [
            "URL: {$url}",
            "目标路径: {$destination}"
        ];

        if ($fileSize > 0) {
            $details[] = "文件大小: " . self::formatSize($fileSize);
        }

        self::writeLog('INFO', $message, 'DOWNLOAD');
        foreach ($details as $detail) {
            self::writeLog('INFO', $detail, 'DOWNLOAD');
        }
    }

    /**
     * 记录下载完成日志
     *
     * @param string $url 下载URL
     * @param string $destination 目标路径
     * @param int $fileSize 文件大小
     * @param float $duration 下载耗时（秒）
     * @param bool $fromCache 是否来自缓存
     */
    public static function logDownloadComplete($url, $destination, $fileSize, $duration, $fromCache = false)
    {
        $source = $fromCache ? "缓存" : "网络";
        $message = "下载完成 (来源: {$source})";
        $details = [
            "URL: {$url}",
            "目标路径: {$destination}",
            "文件大小: " . self::formatSize($fileSize),
            "耗时: " . self::formatDuration($duration)
        ];

        if (!$fromCache && $duration > 0) {
            $speed = $fileSize / $duration;
            $details[] = "平均速度: " . self::formatSize($speed) . "/s";
        }

        self::writeLog('INFO', $message, 'DOWNLOAD');
        foreach ($details as $detail) {
            self::writeLog('INFO', $detail, 'DOWNLOAD');
        }
    }

    /**
     * 记录下载失败日志
     *
     * @param string $url 下载URL
     * @param string $error 错误信息
     * @param int $attemptNumber 尝试次数
     */
    public static function logDownloadError($url, $error, $attemptNumber = 1)
    {
        $message = "下载失败 (尝试 #{$attemptNumber})";
        $details = [
            "URL: {$url}",
            "错误: {$error}"
        ];

        self::writeLog('ERROR', $message, 'DOWNLOAD');
        foreach ($details as $detail) {
            self::writeLog('ERROR', $detail, 'DOWNLOAD');
        }
    }

    /**
     * 记录缓存操作日志
     *
     * @param string $operation 操作类型 (HIT/MISS/SET/DELETE/CLEAR)
     * @param string $key 缓存键
     * @param string $details 详细信息
     */
    public static function logCacheOperation($operation, $key, $details = '')
    {
        $message = "缓存操作: {$operation}";
        $logDetails = ["键: {$key}"];

        if (!empty($details)) {
            $logDetails[] = "详情: {$details}";
        }

        self::writeLog('DEBUG', $message, 'CACHE');
        foreach ($logDetails as $detail) {
            self::writeLog('DEBUG', $detail, 'CACHE');
        }
    }

    /**
     * 记录文件完整性校验日志
     *
     * @param string $filePath 文件路径
     * @param string $algorithm 校验算法
     * @param string $expectedHash 期望的哈希值
     * @param string $actualHash 实际的哈希值
     * @param bool $passed 是否通过校验
     */
    public static function logIntegrityCheck($filePath, $algorithm, $expectedHash, $actualHash, $passed)
    {
        $status = $passed ? "通过" : "失败";
        $message = "文件完整性校验: {$status}";
        $details = [
            "文件: {$filePath}",
            "算法: {$algorithm}",
            "期望值: {$expectedHash}",
            "实际值: {$actualHash}"
        ];

        $level = $passed ? 'INFO' : 'ERROR';
        self::writeLog($level, $message, 'INTEGRITY');
        foreach ($details as $detail) {
            self::writeLog($level, $detail, 'INTEGRITY');
        }
    }

    /**
     * 写入日志
     *
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param string $category 日志分类
     */
    private static function writeLog($level, $message, $category)
    {
        if (!self::$enabled || !self::$currentLogFile) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[{$timestamp}] [{$level}] [{$category}] {$message}";
        
        self::writeToFile($logLine);
    }

    /**
     * 写入文件
     *
     * @param string $content 内容
     */
    private static function writeToFile($content)
    {
        if (!self::$enabled || !self::$currentLogFile) {
            return;
        }

        file_put_contents(self::$currentLogFile, $content . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * 计算执行时长
     *
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return string 格式化的时长
     */
    private static function calculateDuration($startTime, $endTime)
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $interval = $start->diff($end);

        $parts = [];
        if ($interval->h > 0) {
            $parts[] = $interval->h . '小时';
        }
        if ($interval->i > 0) {
            $parts[] = $interval->i . '分钟';
        }
        $parts[] = $interval->s . '秒';

        return implode('', $parts);
    }

    /**
     * 获取当前日志文件路径
     *
     * @return string|null
     */
    public static function getCurrentLogFile()
    {
        return self::$currentLogFile;
    }

    /**
     * 启用或禁用文件日志
     *
     * @param bool $enabled 是否启用
     */
    public static function setEnabled($enabled)
    {
        self::$enabled = $enabled;
    }

    /**
     * 检查文件日志是否启用
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return self::$enabled;
    }

    /**
     * 格式化文件大小
     *
     * @param int $bytes 字节数
     * @return string 格式化后的大小
     */
    private static function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 格式化时长
     *
     * @param float $seconds 秒数
     * @return string 格式化后的时长
     */
    private static function formatDuration($seconds)
    {
        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        } elseif ($seconds < 60) {
            return round($seconds, 2) . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm' . round($remainingSeconds) . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $remainingSeconds = $seconds % 60;
            return $hours . 'h' . $minutes . 'm' . round($remainingSeconds) . 's';
        }
    }
}
