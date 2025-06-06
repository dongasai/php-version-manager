<?php

namespace VersionManager\Core\Logger;

/**
 * 日志管理器
 */
class Logger
{
    /**
     * 当前日志级别
     *
     * @var int
     */
    private static $level = LogLevel::NORMAL;

    /**
     * 是否启用文件日志
     *
     * @var bool
     */
    private static $fileLoggingEnabled = true;
    
    /**
     * 设置日志级别
     *
     * @param int $level 日志级别
     */
    public static function setLevel($level)
    {
        self::$level = $level;
    }

    /**
     * 启用或禁用文件日志
     *
     * @param bool $enabled 是否启用文件日志
     */
    public static function setFileLoggingEnabled($enabled)
    {
        self::$fileLoggingEnabled = $enabled;
        FileLogger::setEnabled($enabled);
    }

    /**
     * 检查文件日志是否启用
     *
     * @return bool
     */
    public static function isFileLoggingEnabled()
    {
        return self::$fileLoggingEnabled;
    }
    
    /**
     * 获取当前日志级别
     *
     * @return int
     */
    public static function getLevel()
    {
        return self::$level;
    }
    
    /**
     * 静默模式输出（只有错误和最重要的信息）
     *
     * @param string $message 消息
     * @param string $color 颜色代码
     */
    public static function silent($message, $color = '')
    {
        if (self::$level >= LogLevel::SILENT) {
            self::output($message, $color);
        }
    }
    
    /**
     * 普通模式输出（默认级别）
     *
     * @param string $message 消息
     * @param string $color 颜色代码
     */
    public static function info($message, $color = '')
    {
        if (self::$level >= LogLevel::NORMAL) {
            self::output($message, $color);
        }

        // 写入文件日志
        if (self::$fileLoggingEnabled) {
            FileLogger::info($message);
        }
    }
    
    /**
     * 详细模式输出
     *
     * @param string $message 消息
     * @param string $color 颜色代码
     */
    public static function verbose($message, $color = '')
    {
        if (self::$level >= LogLevel::VERBOSE) {
            self::output($message, $color);
        }

        // 写入文件日志
        if (self::$fileLoggingEnabled) {
            FileLogger::debug($message, 'VERBOSE');
        }
    }
    
    /**
     * 调试模式输出
     *
     * @param string $message 消息
     * @param string $color 颜色代码
     */
    public static function debug($message, $color = '')
    {
        if (self::$level >= LogLevel::DEBUG) {
            self::output($message, $color);
        }

        // 写入文件日志
        if (self::$fileLoggingEnabled) {
            FileLogger::debug($message);
        }
    }

    /**
     * 成功消息（总是显示）
     *
     * @param string $message 消息
     */
    public static function success($message)
    {
        self::output($message, "\033[32m");

        // 写入文件日志
        if (self::$fileLoggingEnabled) {
            FileLogger::info($message, 'SUCCESS');
        }
    }

    /**
     * 警告消息（总是显示）
     *
     * @param string $message 消息
     */
    public static function warning($message)
    {
        self::output($message, "\033[33m");

        // 写入文件日志
        if (self::$fileLoggingEnabled) {
            FileLogger::warning($message);
        }
    }

    /**
     * 错误消息（总是显示）
     *
     * @param string $message 消息
     */
    public static function error($message)
    {
        self::output($message, "\033[31m");

        // 写入文件日志
        if (self::$fileLoggingEnabled) {
            FileLogger::error($message);
        }
    }
    
    /**
     * 输出消息
     *
     * @param string $message 消息
     * @param string $color 颜色代码
     */
    private static function output($message, $color = '')
    {
        if ($color) {
            echo $color . $message . "\033[0m\n";
        } else {
            echo $message . "\n";
        }
    }
    
    /**
     * 检查是否应该显示详细信息
     *
     * @return bool
     */
    public static function isVerbose()
    {
        return self::$level >= LogLevel::VERBOSE;
    }
    
    /**
     * 检查是否为静默模式
     *
     * @return bool
     */
    public static function isSilent()
    {
        return self::$level <= LogLevel::SILENT;
    }
}
