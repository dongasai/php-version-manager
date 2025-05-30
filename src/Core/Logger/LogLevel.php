<?php

namespace VersionManager\Core\Logger;

/**
 * 日志级别常量
 */
class LogLevel
{
    const SILENT = 0;   // 静默模式
    const NORMAL = 1;   // 默认模式
    const VERBOSE = 2;  // 详细模式
    const DEBUG = 3;    // 调试模式
    
    /**
     * 获取所有可用的日志级别
     *
     * @return array
     */
    public static function getAvailableLevels()
    {
        return [
            self::SILENT => 'silent',
            self::NORMAL => 'normal', 
            self::VERBOSE => 'verbose',
            self::DEBUG => 'debug'
        ];
    }
    
    /**
     * 根据名称获取日志级别
     *
     * @param string $name 级别名称
     * @return int
     */
    public static function fromName($name)
    {
        $levels = array_flip(self::getAvailableLevels());
        return isset($levels[$name]) ? $levels[$name] : self::NORMAL;
    }
    
    /**
     * 根据级别获取名称
     *
     * @param int $level 日志级别
     * @return string
     */
    public static function getName($level)
    {
        $levels = self::getAvailableLevels();
        return isset($levels[$level]) ? $levels[$level] : 'normal';
    }
}
