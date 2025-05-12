<?php

namespace VersionManager\Core\Extension;

/**
 * 扩展类型枚举
 * 
 * 定义PHP扩展的类型
 */
class ExtensionType
{
    /**
     * 内置扩展
     * 
     * PHP内置的扩展，通常随PHP一起编译
     */
    const BUILTIN = 'builtin';
    
    /**
     * PECL扩展
     * 
     * 从PECL安装的扩展
     */
    const PECL = 'pecl';
    
    /**
     * 源码扩展
     * 
     * 从源码编译安装的扩展
     */
    const SOURCE = 'source';
    
    /**
     * 系统扩展
     * 
     * 从系统包管理器安装的扩展
     */
    const SYSTEM = 'system';
    
    /**
     * 自定义扩展
     * 
     * 自定义安装的扩展
     */
    const CUSTOM = 'custom';
    
    /**
     * 获取所有扩展类型
     *
     * @return array
     */
    public static function getAll()
    {
        return [
            self::BUILTIN,
            self::PECL,
            self::SOURCE,
            self::SYSTEM,
            self::CUSTOM,
        ];
    }
    
    /**
     * 检查扩展类型是否有效
     *
     * @param string $type 扩展类型
     * @return bool
     */
    public static function isValid($type)
    {
        return in_array($type, self::getAll());
    }
    
    /**
     * 获取扩展类型描述
     *
     * @param string $type 扩展类型
     * @return string
     */
    public static function getDescription($type)
    {
        switch ($type) {
            case self::BUILTIN:
                return '内置扩展';
            case self::PECL:
                return 'PECL扩展';
            case self::SOURCE:
                return '源码扩展';
            case self::SYSTEM:
                return '系统扩展';
            case self::CUSTOM:
                return '自定义扩展';
            default:
                return '未知扩展';
        }
    }
}
