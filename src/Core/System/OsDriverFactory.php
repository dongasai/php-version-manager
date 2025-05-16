<?php

namespace VersionManager\Core\System;

/**
 * 操作系统驱动工厂类
 *
 * 用于创建操作系统驱动实例
 */
class OsDriverFactory
{
    /**
     * 操作系统驱动实例
     *
     * @var OsDriverInterface
     */
    private static $instance;

    /**
     * 获取操作系统驱动实例
     *
     * @return OsDriverInterface
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = self::createInstance();
        }

        return self::$instance;
    }

    /**
     * 创建操作系统驱动实例
     *
     * @return OsDriverInterface
     */
    private static function createInstance()
    {
        // 检测操作系统类型和信息
        $osInfo = self::detectOsInfo();
        $osType = $osInfo['type'];
        $distroName = $osInfo['distro'];
        $distroVersion = $osInfo['version'];

        // 收集所有可用的驱动类
        $driverClasses = [];
        $genericDriverClass = null;

        // 扫描驱动目录
        $driversDir = __DIR__ . '/Drivers';
        if (is_dir($driversDir)) {
            $files = glob($driversDir . '/*.php');

            foreach ($files as $file) {
                $className = basename($file, '.php');
                $fullClassName = __NAMESPACE__ . '\\Drivers\\' . $className;

                if (class_exists($fullClassName)) {
                    if ($className === 'GenericLinuxDriver') {
                        $genericDriverClass = $fullClassName;
                    } else {
                        $driverClasses[] = $fullClassName;
                    }
                }
            }
        }

        // 如果没有找到任何驱动类，则创建一个匿名驱动
        if (empty($driverClasses) && $genericDriverClass === null) {
            return new class() extends AbstractOsDriver {
                protected function detectOsInfo()
                {
                    $this->name = strtolower(PHP_OS);
                    $this->description = PHP_OS;
                    $this->version = php_uname('r');
                    $this->arch = php_uname('m');
                }
            };
        }

        // 准备标签
        $requiredTags = [];
        $optionalTags = [];

        // 操作系统类型是必选标签
        $requiredTags[] = strtolower($osType);

        // 发行版和版本是可选标签
        if ($distroName) {
            $optionalTags[] = strtolower($distroName);

            if ($distroVersion) {
                $optionalTags[] = strtolower($distroName) . '-' . $distroVersion;
            }
        }

        // 使用标签匹配器匹配最合适的驱动
        $matchedClass = \VersionManager\Core\Tags\DriverMatcher::matchClass($driverClasses, $requiredTags, $optionalTags);

        // 如果找到匹配的驱动，则返回
        if ($matchedClass !== null) {
            return new $matchedClass();
        }

        // 如果没有找到匹配的驱动，但有通用Linux驱动，则返回通用Linux驱动
        if ($genericDriverClass !== null) {
            return new $genericDriverClass();
        }

        // 如果没有找到任何驱动，则创建一个匿名驱动
        return new class() extends AbstractOsDriver {
            protected function detectOsInfo()
            {
                $this->name = strtolower(PHP_OS);
                $this->description = PHP_OS;
                $this->version = php_uname('r');
                $this->arch = php_uname('m');
            }
        };
    }

    /**
     * 检测操作系统信息
     *
     * @return array [type => 操作系统类型, distro => 发行版名称, version => 发行版版本]
     */
    private static function detectOsInfo()
    {
        $osType = PHP_OS;
        $distroName = '';
        $distroVersion = '';

        // 如果是Linux系统，则尝试检测发行版
        if ($osType === 'Linux' || stripos($osType, 'linux') !== false) {
            $osType = 'linux';

            // 尝试从/etc/os-release获取发行版信息
            if (file_exists('/etc/os-release')) {
                $osRelease = parse_ini_file('/etc/os-release');

                if (isset($osRelease['ID'])) {
                    $distroName = strtolower($osRelease['ID']);
                }

                if (isset($osRelease['VERSION_ID'])) {
                    $distroVersion = $osRelease['VERSION_ID'];
                }
            }

            // 如果无法从/etc/os-release获取信息，则尝试其他方法
            if (empty($distroName)) {
                if (file_exists('/etc/debian_version')) {
                    // 检查是否是Ubuntu
                    if (file_exists('/etc/lsb-release')) {
                        $lsbRelease = parse_ini_file('/etc/lsb-release');

                        if (isset($lsbRelease['DISTRIB_ID'])) {
                            $distroName = strtolower($lsbRelease['DISTRIB_ID']);
                        }

                        if (isset($lsbRelease['DISTRIB_RELEASE'])) {
                            $distroVersion = $lsbRelease['DISTRIB_RELEASE'];
                        }
                    }

                    // 如果不是Ubuntu，则可能是Debian
                    if (empty($distroName)) {
                        $distroName = 'debian';
                        $distroVersion = trim(file_get_contents('/etc/debian_version'));
                    }
                } elseif (file_exists('/etc/centos-release')) {
                    $distroName = 'centos';
                    $content = file_get_contents('/etc/centos-release');

                    if (preg_match('/release\s+(\d+(\.\d+)*)/', $content, $matches)) {
                        $distroVersion = $matches[1];
                    }
                } elseif (file_exists('/etc/fedora-release')) {
                    $distroName = 'fedora';
                    $content = file_get_contents('/etc/fedora-release');

                    if (preg_match('/release\s+(\d+)/', $content, $matches)) {
                        $distroVersion = $matches[1];
                    }
                } elseif (file_exists('/etc/alpine-release')) {
                    $distroName = 'alpine';
                    $distroVersion = trim(file_get_contents('/etc/alpine-release'));
                }
            }
        } elseif (stripos($osType, 'win') !== false) {
            $osType = 'windows';
        } elseif (stripos($osType, 'darwin') !== false) {
            $osType = 'macos';
        }

        return [
            'type' => $osType,
            'distro' => $distroName,
            'version' => $distroVersion,
        ];
    }
}
