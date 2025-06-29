<?php

namespace VersionManager\Core\Version\Util;

/**
 * 版本助手类
 *
 * 提供版本相关的通用功能
 */
class VersionHelper
{
    /**
     * 检查版本格式是否有效
     *
     * @param string $version 版本号
     * @return bool
     */
    public static function isValidVersionFormat($version)
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version);
    }

    /**
     * 检查版本是否在支持范围内
     *
     * @param string $version 版本号
     * @param string $minVersion 最小支持版本（默认7.1.0）
     * @param string $maxVersion 最大支持版本（可选）
     * @return bool
     */
    public static function isSupportedVersion($version, $minVersion = '7.1.0', $maxVersion = null)
    {
        if (!self::isValidVersionFormat($version)) {
            return false;
        }

        // 检查最小版本
        if (version_compare($version, $minVersion, '<')) {
            return false;
        }

        // 检查最大版本（如果指定）
        if ($maxVersion !== null && version_compare($version, $maxVersion, '>')) {
            return false;
        }

        return true;
    }

    /**
     * 解析版本号为数组
     *
     * @param string $version 版本号
     * @return array [major, minor, patch]
     */
    public static function parseVersion($version)
    {
        if (!self::isValidVersionFormat($version)) {
            throw new \InvalidArgumentException("无效的版本格式: {$version}");
        }

        list($major, $minor, $patch) = explode('.', $version);
        
        return [
            'major' => (int)$major,
            'minor' => (int)$minor,
            'patch' => (int)$patch,
            'major_minor' => $major . '.' . $minor
        ];
    }

    /**
     * 获取版本的主要.次要版本号
     *
     * @param string $version 版本号
     * @return string 主要.次要版本号（如 "7.4"）
     */
    public static function getMajorMinorVersion($version)
    {
        $parsed = self::parseVersion($version);
        return $parsed['major_minor'];
    }

    /**
     * 比较两个版本号
     *
     * @param string $version1 版本1
     * @param string $version2 版本2
     * @param string $operator 比较操作符（<, <=, >, >=, ==, !=）
     * @return bool
     */
    public static function compareVersions($version1, $version2, $operator = '==')
    {
        return version_compare($version1, $version2, $operator);
    }

    /**
     * 检查版本是否为PHP 8.0及以上
     *
     * @param string $version 版本号
     * @return bool
     */
    public static function isPhp8OrHigher($version)
    {
        return self::compareVersions($version, '8.0.0', '>=');
    }

    /**
     * 检查版本是否为PHP 7.4及以上
     *
     * @param string $version 版本号
     * @return bool
     */
    public static function isPhp74OrHigher($version)
    {
        return self::compareVersions($version, '7.4.0', '>=');
    }

    /**
     * 检查版本是否为PHP 7.1-7.3
     *
     * @param string $version 版本号
     * @return bool
     */
    public static function isPhp71To73($version)
    {
        return self::compareVersions($version, '7.1.0', '>=') && 
               self::compareVersions($version, '7.4.0', '<');
    }

    /**
     * 获取版本对应的PHP版本键（用于驱动查找）
     *
     * @param string $version 版本号
     * @return string PHP版本键（如 "PHP71", "PHP80"）
     */
    public static function getPhpVersionKey($version)
    {
        $parsed = self::parseVersion($version);
        return "PHP{$parsed['major']}{$parsed['minor']}";
    }

    /**
     * 获取支持的PHP版本列表
     *
     * @return array 支持的版本列表
     */
    public static function getSupportedVersions()
    {
        return [
            '7.1.0', '7.1.33',
            '7.2.0', '7.2.34',
            '7.3.0', '7.3.33',
            '7.4.0', '7.4.33',
            '8.0.0', '8.0.30',
            '8.1.0', '8.1.29',
            '8.2.0', '8.2.20',
            '8.3.0', '8.3.8'
        ];
    }

    /**
     * 检查版本是否在支持列表中
     *
     * @param string $version 版本号
     * @return bool
     */
    public static function isVersionSupported($version)
    {
        $supportedVersions = self::getSupportedVersions();
        return in_array($version, $supportedVersions);
    }

    /**
     * 获取最新的稳定版本
     *
     * @param string $majorMinor 主要.次要版本号（可选）
     * @return string|null 最新版本号
     */
    public static function getLatestVersion($majorMinor = null)
    {
        $supportedVersions = self::getSupportedVersions();
        
        if ($majorMinor === null) {
            // 返回所有版本中的最新版本
            return end($supportedVersions);
        }
        
        // 返回指定主要.次要版本的最新版本
        $filteredVersions = array_filter($supportedVersions, function($version) use ($majorMinor) {
            return self::getMajorMinorVersion($version) === $majorMinor;
        });
        
        return empty($filteredVersions) ? null : end($filteredVersions);
    }

    /**
     * 获取可用的主要.次要版本列表
     *
     * @return array 主要.次要版本列表
     */
    public static function getAvailableMajorMinorVersions()
    {
        $supportedVersions = self::getSupportedVersions();
        $majorMinorVersions = [];
        
        foreach ($supportedVersions as $version) {
            $majorMinor = self::getMajorMinorVersion($version);
            if (!in_array($majorMinor, $majorMinorVersions)) {
                $majorMinorVersions[] = $majorMinor;
            }
        }
        
        return $majorMinorVersions;
    }

    /**
     * 规范化版本号（确保为x.y.z格式）
     *
     * @param string $version 版本号
     * @return string 规范化的版本号
     */
    public static function normalizeVersion($version)
    {
        // 如果是x.y格式，补充.0
        if (preg_match('/^\d+\.\d+$/', $version)) {
            return $version . '.0';
        }
        
        // 如果已经是x.y.z格式，直接返回
        if (self::isValidVersionFormat($version)) {
            return $version;
        }
        
        throw new \InvalidArgumentException("无法规范化版本号: {$version}");
    }
}
