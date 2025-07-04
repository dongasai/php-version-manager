<?php

namespace VersionManager\Core\Version;

/**
 * 版本安装驱动工厂类
 *
 * 用于创建和管理版本安装驱动
 */
class VersionDriverFactory
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
     * 获取版本安装驱动实例
     *
     * @param string $phpVersion PHP版本（可选）
     * @param string $distro 发行版名称（可选）
     * @param string $distroVersion 发行版版本（可选）
     * @param string $arch 架构名称（可选）
     * @return VersionDriverInterface
     * @throws \Exception 如果找不到驱动则抛出异常
     */
    public static function getDriver($phpVersion = null, $distro = null, $distroVersion = null, $arch = null)
    {
        // 如果未指定参数，则自动检测
        if ($distro === null || $distroVersion === null || $arch === null) {
            $osInfo = self::getOsInfo();
            $distro = $distro ?: $osInfo['type'];
            $distroVersion = $distroVersion ?: $osInfo['version'];
            $arch = $arch ?: $osInfo['arch'];
        }

        // 生成驱动键
        $driverKey = '';
        if ($phpVersion) {
            $driverKey .= "php" . str_replace('.', '', $phpVersion) . "_";
        }
        if ($distro) {
            $driverKey .= strtolower($distro);
            if ($distroVersion) {
                $driverKey .= "_" . str_replace('.', '_', $distroVersion);
            }
            if ($arch) {
                $driverKey .= "_" . strtolower($arch);
            }
        } elseif ($arch) {
            $driverKey .= strtolower($arch);
        }

        // 如果已经有实例，则直接返回
        if (isset(self::$instances[$driverKey])) {
            return self::$instances[$driverKey];
        }

        // 尝试加载驱动类映射
        self::loadDriverMap();

        // 查找驱动类
        $driverClass = null;

        // 如果指定PHP版本，则查找特定PHP版本的驱动
        if ($phpVersion) {
            // 提取PHP版本的主要和次要版本号
            list($major, $minor, $patch) = explode('.', $phpVersion);
            $phpVersionKey = "PHP{$major}{$minor}";

            // 查找最匹配的驱动
            $driverClass = self::findBestMatchDriver($phpVersionKey, $distro, $distroVersion, $arch);
        } else {
            // 如果没有指定PHP版本，则使用基础版本驱动
            $driverClass = __NAMESPACE__ . '\\BaseVersionDriver';
        }

        // 如果没有找到特定PHP版本的驱动，则抛出异常
        if (!$driverClass) {
            throw new \Exception("未找到适用于PHP版本 {$phpVersion} 的驱动");
        }

        // 创建驱动实例
        $driver = new $driverClass();

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
        $configFile = __DIR__ . '/../../../config/versions/driver_map.php';
        if (file_exists($configFile)) {
            self::$driverMap = require $configFile;
        }
    }

    /**
     * 查找最匹配的驱动
     *
     * @param string $phpVersionKey PHP版本键（如PHP71、PHP80）
     * @param string $distro 发行版名称
     * @param string $distroVersion 发行版版本
     * @param string $arch 架构名称
     * @return string|null 驱动类名，如果找不到则返回null
     */
    private static function findBestMatchDriver($phpVersionKey, $distro, $distroVersion, $arch)
    {
        // 查找PHP版本目录
        $phpVersionDir = __DIR__ . '/Drivers/' . $phpVersionKey;
        if (!is_dir($phpVersionDir)) {
            return null;
        }

        // 获取PHP版本目录中的所有PHP文件
        $files = glob($phpVersionDir . '/*.php');
        if (empty($files)) {
            return null;
        }

        // 收集所有驱动类
        $driverClasses = [];
        $baseClass = null;

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClassName = __NAMESPACE__ . '\\Drivers\\' . $phpVersionKey . '\\' . $className;

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

        // 从PHP版本标签中获取必选标签
        $phpVersionTag = strtolower($phpVersionKey);
        $requiredTags[] = $phpVersionTag;

        // 从操作系统和架构信息中获取可选标签
        if ($distro) {
            $optionalTags[] = strtolower($distro);

            if ($distroVersion) {
                $optionalTags[] = strtolower($distro) . '-' . $distroVersion;
            }
        }

        if ($arch) {
            $optionalTags[] = strtolower($arch);
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
     * 获取操作系统信息
     *
     * @return array [type => 类型, version => 版本, arch => 架构]
     */
    private static function getOsInfo()
    {
        $type = '';
        $version = '';
        $arch = php_uname('m');

        // 读取/etc/os-release文件
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID'])) {
                $type = strtolower($osRelease['ID']);
            }

            if (isset($osRelease['VERSION_ID'])) {
                $version = $osRelease['VERSION_ID'];
            }
        }

        return [
            'type' => $type,
            'version' => $version,
            'arch' => $arch,
        ];
    }

    /**
     * 注册驱动类
     *
     * @param string $key 驱动键
     * @param string $driverClass 驱动类名
     */
    public static function registerDriver($key, $driverClass)
    {
        self::$driverMap[$key] = $driverClass;

        // 清除实例缓存
        foreach (self::$instances as $driverKey => $instance) {
            if (strpos($driverKey, $key) !== false) {
                unset(self::$instances[$driverKey]);
            }
        }
    }
}
