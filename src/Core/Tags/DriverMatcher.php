<?php

namespace VersionManager\Core\Tags;

/**
 * 驱动匹配器类
 * 
 * 用于根据标签匹配最合适的驱动
 */
class DriverMatcher
{
    /**
     * 匹配最合适的驱动
     * 
     * @param array $drivers 驱动实例数组
     * @param array $requiredTags 必选标签数组
     * @param array $optionalTags 可选标签数组
     * @return object|null 最匹配的驱动或null
     */
    public static function match(array $drivers, array $requiredTags, array $optionalTags = [])
    {
        // 1. 通过必选标签过滤驱动
        $candidates = array_filter($drivers, function($driver) use ($requiredTags) {
            if (!$driver instanceof TaggableInterface) {
                return false;
            }
            
            $driverTags = $driver->getTags();
            foreach ($requiredTags as $tag) {
                if (!in_array($tag, $driverTags)) {
                    return false;
                }
            }
            
            return true;
        });
        
        if (empty($candidates)) {
            return null;
        }
        
        // 2. 如果没有可选标签，返回第一个候选驱动
        if (empty($optionalTags)) {
            return reset($candidates);
        }
        
        // 3. 计算每个候选驱动的匹配分数
        $scores = [];
        foreach ($candidates as $key => $driver) {
            $driverTags = $driver->getTags();
            $score = 0;
            
            foreach ($optionalTags as $tag) {
                if (in_array($tag, $driverTags)) {
                    $score++;
                }
            }
            
            $scores[$key] = $score;
        }
        
        // 4. 返回得分最高的驱动
        arsort($scores);
        $bestKey = key($scores);
        
        return $candidates[$bestKey];
    }
    
    /**
     * 匹配最合适的驱动类
     * 
     * @param array $driverClasses 驱动类名数组
     * @param array $requiredTags 必选标签数组
     * @param array $optionalTags 可选标签数组
     * @return string|null 最匹配的驱动类名或null
     */
    public static function matchClass(array $driverClasses, array $requiredTags, array $optionalTags = [])
    {
        // 创建驱动实例
        $drivers = [];
        foreach ($driverClasses as $key => $class) {
            if (class_exists($class)) {
                $drivers[$key] = new $class();
            }
        }
        
        // 匹配最合适的驱动
        $driver = self::match($drivers, $requiredTags, $optionalTags);
        
        if ($driver === null) {
            return null;
        }
        
        // 返回驱动类名
        return get_class($driver);
    }
}
