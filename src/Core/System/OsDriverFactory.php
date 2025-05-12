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
        // 检测操作系统类型
        $osType = PHP_OS;

        // 如果是Linux系统，则尝试检测发行版
        if ($osType === 'Linux' || stripos($osType, 'linux') !== false) {
            // 尝试从/etc/os-release获取发行版信息
            if (file_exists('/etc/os-release')) {
                $osRelease = parse_ini_file('/etc/os-release');

                if (isset($osRelease['ID'])) {
                    $distroName = strtolower($osRelease['ID']);

                    // 创建驱动类名称
                    $driverClass = '\\VersionManager\\Core\\System\\Drivers\\' . ucfirst($distroName) . 'Driver';

                    // 检查驱动类是否存在
                    if (class_exists($driverClass)) {
                        return new $driverClass();
                    }
                }
            }

            // 如果无法从/etc/os-release获取信息，则尝试其他方法
            if (file_exists('/etc/debian_version')) {
                // 检查是否是Ubuntu
                if (file_exists('/etc/lsb-release')) {
                    $lsbRelease = parse_ini_file('/etc/lsb-release');

                    if (isset($lsbRelease['DISTRIB_ID']) && strtolower($lsbRelease['DISTRIB_ID']) === 'ubuntu') {
                        $driverClass = '\\VersionManager\\Core\\System\\Drivers\\UbuntuDriver';

                        if (class_exists($driverClass)) {
                            return new $driverClass();
                        }
                    }
                }

                // 如果不是Ubuntu，则可能是Debian
                $driverClass = '\\VersionManager\\Core\\System\\Drivers\\DebianDriver';

                if (class_exists($driverClass)) {
                    return new $driverClass();
                }
            } elseif (file_exists('/etc/centos-release')) {
                $driverClass = '\\VersionManager\\Core\\System\\Drivers\\CentosDriver';

                if (class_exists($driverClass)) {
                    return new $driverClass();
                }
            } elseif (file_exists('/etc/fedora-release')) {
                $driverClass = '\\VersionManager\\Core\\System\\Drivers\\FedoraDriver';

                if (class_exists($driverClass)) {
                    return new $driverClass();
                }
            } elseif (file_exists('/etc/alpine-release')) {
                $driverClass = '\\VersionManager\\Core\\System\\Drivers\\AlpineDriver';

                if (class_exists($driverClass)) {
                    return new $driverClass();
                }
            }

            // 如果没有找到特定的发行版驱动，则使用通用Linux驱动
            $genericDriverClass = '\\VersionManager\\Core\\System\\Drivers\\GenericLinuxDriver';

            if (class_exists($genericDriverClass)) {
                return new $genericDriverClass();
            }
        }

        // 如果不是Linux系统或没有找到适合的驱动，则创建一个通用驱动
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
}
