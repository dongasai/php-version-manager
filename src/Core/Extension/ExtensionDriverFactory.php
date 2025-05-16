<?php

namespace VersionManager\Core\Extension;

use VersionManager\Core\System\OsDriverFactory;

/**
 * 扩展驱动工厂类
 *
 * 用于创建和管理扩展驱动
 */
class ExtensionDriverFactory
{
    /**
     * 驱动实例缓存
     *
     * @var array
     */
    private static $instances = [];

    /**
     * 驱动类映射
     *
     * @var array
     */
    private static $driverMap = [];

    /**
     * 获取扩展驱动实例
     *
     * @param string $extension 扩展名称
     * @param string $phpVersion PHP版本（可选）
     * @param string $distro 发行版名称（可选）
     * @param string $distroVersion 发行版版本（可选）
     * @param string $arch 架构名称（可选）
     * @return ExtensionDriverInterface
     * @throws \Exception 如果找不到驱动则抛出异常
     */
    public static function getDriver($extension, $phpVersion = null, $distro = null, $distroVersion = null, $arch = null)
    {
        // 生成驱动键
        $driverKey = $extension;

        // 添加PHP版本、发行版和架构信息到驱动键
        $parts = [];

        if ($phpVersion) {
            $parts[] = "php{$phpVersion}";
        }

        if ($distro) {
            $parts[] = $distro;

            if ($distroVersion) {
                $parts[] = $distroVersion;
            }
        }

        if ($arch) {
            $parts[] = $arch;
        }

        if (!empty($parts)) {
            $driverKey = implode(':', $parts) . ':' . $extension;
        }

        // 如果已经有实例，则直接返回
        if (isset(self::$instances[$driverKey])) {
            return self::$instances[$driverKey];
        }

        // 尝试加载驱动类映射
        self::loadDriverMap();

        // 查找驱动类
        $driverClass = null;

        // 获取操作系统驱动实例，用于获取系统信息
        $osDriver = OsDriverFactory::getInstance();

        // 如果没有指定发行版信息，则使用操作系统驱动提供的信息
        if ($distro === null) {
            $distro = $osDriver->getName();
        }

        if ($distroVersion === null) {
            $distroVersion = $osDriver->getVersion();
        }

        if ($arch === null) {
            $arch = $osDriver->getArch();
        }

        // 先尝试查找完全匹配的驱动（PHP版本 + 发行版 + 发行版版本 + 架构）
        if ($phpVersion && $distro && $distroVersion && $arch) {
            $driverClass = self::findDriverClass($driverKey, $phpVersion, $distro, $distroVersion, $arch);
        }

        // 如果没有找到，则尝试查找特定发行版和架构的驱动
        if (!$driverClass && $distro && $arch) {
            $driverClass = self::findDriverClass($distro . ':' . $arch . ':' . $extension, $phpVersion, $distro, $distroVersion, $arch);
        }

        // 如果没有找到，则尝试查找特定发行版的驱动
        if (!$driverClass && $distro) {
            $driverClass = self::findDriverClass($distro . ':' . $extension, $phpVersion, $distro, $distroVersion, $arch);
        }

        // 如果还是没有找到，则尝试查找通用驱动
        if (!$driverClass) {
            $driverClass = self::findDriverClass($extension, $phpVersion, $distro, $distroVersion, $arch);
        }

        // 如果找不到驱动类，则使用通用驱动
        if (!$driverClass) {
            $driverClass = GenericExtensionDriver::class;
        }

        // 创建驱动实例
        if ($driverClass === GenericExtensionDriver::class) {
            $driver = new $driverClass($extension);
        } else {
            $driver = new $driverClass();
        }

        // 缓存实例
        self::$instances[$driverKey] = $driver;

        return $driver;
    }

    /**
     * 加载驱动类映射
     */
    private static function loadDriverMap()
    {
        // 如果已经加载，则直接返回
        if (!empty(self::$driverMap)) {
            return;
        }

        // 加载驱动类映射配置
        $configFile = __DIR__ . '/../../../config/extensions/driver_map.php';
        if (file_exists($configFile)) {
            self::$driverMap = require $configFile;
        }
    }

    /**
     * 查找驱动类
     *
     * @param string $extension 扩展名称
     * @param string $phpVersion PHP版本
     * @param string $distro 发行版名称
     * @param string $distroVersion 发行版版本
     * @param string $arch 架构名称
     * @return string|null 驱动类名，如果找不到则返回null
     */
    private static function findDriverClass($extension, $phpVersion = null, $distro = null, $distroVersion = null, $arch = null)
    {
        // 直接查找驱动映射
        if (isset(self::$driverMap[$extension])) {
            return self::$driverMap[$extension];
        }

        // 如果是特定发行版或架构的扩展，则提取扩展名称
        $extensionName = $extension;
        if (strpos($extension, ':') !== false) {
            $parts = explode(':', $extension);
            $extensionName = end($parts);
        }

        // 尝试查找特定驱动类
        $driverClass = __NAMESPACE__ . '\\Drivers\\' . ucfirst($extensionName);
        if (class_exists($driverClass)) {
            return $driverClass;
        }

        // 尝试查找特定驱动类（小写）
        $driverClass = __NAMESPACE__ . '\\Drivers\\' . strtolower($extensionName);
        if (class_exists($driverClass)) {
            return $driverClass;
        }

        // 尝试从扩展目录中查找最匹配的驱动
        return self::findBestMatchDriver($extension, $extensionName, $phpVersion, $distro, $distroVersion, $arch);
    }

    /**
     * 从扩展目录中查找最匹配的驱动
     *
     * @param string $extension 完整的扩展名称（可能包含发行版和架构信息）
     * @param string $extensionName 纯扩展名称
     * @param string $phpVersion PHP版本
     * @param string $distro 发行版名称
     * @param string $distroVersion 发行版版本
     * @param string $arch 架构名称
     * @return string|null 驱动类名，如果找不到则返回null
     */
    private static function findBestMatchDriver($extension, $extensionName, $phpVersion = null, $distro = null, $distroVersion = null, $arch = null)
    {
        // 扩展目录路径
        $extensionDir = __DIR__ . '/Drivers/' . ucfirst($extensionName);
        if (!is_dir($extensionDir)) {
            $extensionDir = __DIR__ . '/Drivers/' . strtolower($extensionName);
            if (!is_dir($extensionDir)) {
                return null;
            }
        }

        // 获取扩展目录中的所有PHP文件
        $files = glob($extensionDir . '/*.php');
        if (empty($files)) {
            return null;
        }

        // 收集所有驱动类
        $driverClasses = [];
        $baseClass = null;

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClassName = __NAMESPACE__ . '\\Drivers\\' . ucfirst($extensionName) . '\\' . $className;

            if (class_exists($fullClassName)) {
                if ($className === 'Base') {
                    $baseClass = $fullClassName;
                } else {
                    $driverClasses[] = $fullClassName;
                }
            }
        }

        // 如果没有找到任何驱动类，则返回null
        if (empty($driverClasses) && $baseClass === null) {
            return null;
        }

        // 如果只有Base类，则直接返回
        if (empty($driverClasses) && $baseClass !== null) {
            return $baseClass;
        }

        // 准备标签
        $requiredTags = [];
        $optionalTags = [];

        // 扩展名称是必选标签
        $requiredTags[] = strtolower($extensionName);

        // 从PHP版本中获取可选标签
        if ($phpVersion) {
            // 使用PhpTag类获取PHP版本标签
            $phpTags = \VersionManager\Core\Tags\PhpTag::getTagsFromVersion($phpVersion);
            $optionalTags = array_merge($optionalTags, $phpTags);
        }

        // 从操作系统信息中获取可选标签
        if ($distro) {
            // 使用OsTags类获取操作系统标签
            $osTags = \VersionManager\Core\Tags\OsTags::getTagsFromOsInfo($distro, $distroVersion);
            $optionalTags = array_merge($optionalTags, $osTags);
        }

        // 从架构信息中获取可选标签
        if ($arch) {
            // 使用ArchTags类获取架构标签
            $archTags = \VersionManager\Core\Tags\ArchTags::getTagsFromArch($arch);
            $optionalTags = array_merge($optionalTags, $archTags);
        }

        // 使用标签匹配器匹配最合适的驱动
        $matchedClass = \VersionManager\Core\Tags\DriverMatcher::matchClass($driverClasses, $requiredTags, $optionalTags);

        // 如果找到匹配的驱动，则返回
        if ($matchedClass !== null) {
            return $matchedClass;
        }

        // 如果没有找到匹配的驱动，但有Base类，则返回Base类
        if ($baseClass !== null) {
            return $baseClass;
        }

        return null;
    }

    /**
     * 注册驱动类
     *
     * @param string $extension 扩展名称
     * @param string $driverClass 驱动类名
     */
    public static function registerDriver($extension, $driverClass)
    {
        self::$driverMap[$extension] = $driverClass;

        // 清除实例缓存
        if (isset(self::$instances[$extension])) {
            unset(self::$instances[$extension]);
        }
    }
}
