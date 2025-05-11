<?php

namespace VersionManager\Core\Extension;

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
     * @param string $distro 发行版名称（可选）
     * @param string $arch 架构名称（可选）
     * @return ExtensionDriverInterface
     * @throws \Exception 如果找不到驱动则抛出异常
     */
    public static function getDriver($extension, $distro = null, $arch = null)
    {
        // 生成驱动键
        $driverKey = $extension;
        if ($distro && $arch) {
            $driverKey = $distro . ':' . $arch . ':' . $extension;
        } elseif ($distro) {
            $driverKey = $distro . ':' . $extension;
        }

        // 如果已经有实例，则直接返回
        if (isset(self::$instances[$driverKey])) {
            return self::$instances[$driverKey];
        }

        // 尝试加载驱动类映射
        self::loadDriverMap();

        // 查找驱动类
        $driverClass = null;

        // 先尝试查找特定发行版和架构的驱动
        if ($distro && $arch) {
            $driverClass = self::findDriverClass($distro . ':' . $arch . ':' . $extension);
        }

        // 如果没有找到，则尝试查找特定发行版的驱动
        if (!$driverClass && $distro) {
            $driverClass = self::findDriverClass($distro . ':' . $extension);
        }

        // 如果还是没有找到，则尝试查找通用驱动
        if (!$driverClass) {
            $driverClass = self::findDriverClass($extension);
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
     * @return string|null 驱动类名，如果找不到则返回null
     */
    private static function findDriverClass($extension)
    {
        // 直接查找
        if (isset(self::$driverMap[$extension])) {
            return self::$driverMap[$extension];
        }

        // 尝试查找特定驱动类
        $driverClass = __NAMESPACE__ . '\\Drivers\\' . ucfirst($extension);
        if (class_exists($driverClass)) {
            return $driverClass;
        }

        // 尝试查找特定驱动类（小写）
        $driverClass = __NAMESPACE__ . '\\Drivers\\' . strtolower($extension);
        if (class_exists($driverClass)) {
            return $driverClass;
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
