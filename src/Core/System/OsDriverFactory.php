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
        // 获取操作系统信息
        $osInfo = self::getOsInfo();

        // 创建驱动类名称
        $driverClass = '\\VersionManager\\Core\\System\\Drivers\\' . ucfirst($osInfo['name']) . 'Driver';

        // 检查驱动类是否存在
        if (class_exists($driverClass)) {
            return new $driverClass($osInfo['name'], $osInfo['description'], $osInfo['version'], $osInfo['arch']);
        }

        // 如果驱动类不存在，使用通用Linux驱动
        $genericDriverClass = '\\VersionManager\\Core\\System\\Drivers\\GenericLinuxDriver';

        if (class_exists($genericDriverClass)) {
            return new $genericDriverClass($osInfo['name'], $osInfo['description'], $osInfo['version'], $osInfo['arch']);
        }

        // 如果通用Linux驱动也不存在，创建一个简单的匿名类实现
        return new class($osInfo['name'], $osInfo['description'], $osInfo['version'], $osInfo['arch']) extends AbstractOsDriver {
        };
    }

    /**
     * 获取操作系统信息
     *
     * @return array
     */
    private static function getOsInfo()
    {
        $name = '';
        $description = '';
        $version = '';
        $arch = php_uname('m');

        // 检查是否是Linux系统
        if (PHP_OS !== 'Linux' && stripos(PHP_OS, 'linux') === false) {
            return [
                'name' => 'unknown',
                'description' => PHP_OS,
                'version' => '',
                'arch' => $arch,
            ];
        }

        // 尝试从/etc/os-release获取信息
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID'])) {
                $name = strtolower($osRelease['ID']);
            }

            if (isset($osRelease['PRETTY_NAME'])) {
                $description = $osRelease['PRETTY_NAME'];
            }

            if (isset($osRelease['VERSION_ID'])) {
                $version = $osRelease['VERSION_ID'];
            }
        }

        // 如果无法从/etc/os-release获取信息，尝试其他方法
        if (empty($name)) {
            // 检查常见的发行版特定文件
            if (file_exists('/etc/debian_version')) {
                $name = 'debian';
                $version = trim(file_get_contents('/etc/debian_version'));

                // 检查是否是Ubuntu
                if (file_exists('/etc/lsb-release')) {
                    $lsbRelease = parse_ini_file('/etc/lsb-release');

                    if (isset($lsbRelease['DISTRIB_ID']) && strtolower($lsbRelease['DISTRIB_ID']) === 'ubuntu') {
                        $name = 'ubuntu';

                        if (isset($lsbRelease['DISTRIB_RELEASE'])) {
                            $version = $lsbRelease['DISTRIB_RELEASE'];
                        }

                        if (isset($lsbRelease['DISTRIB_DESCRIPTION'])) {
                            $description = $lsbRelease['DISTRIB_DESCRIPTION'];
                        }
                    }
                }
            } elseif (file_exists('/etc/centos-release')) {
                $name = 'centos';
                $content = file_get_contents('/etc/centos-release');

                if (preg_match('/release\s+(\d+(\.\d+)*)/', $content, $matches)) {
                    $version = $matches[1];
                }

                $description = trim($content);
            } elseif (file_exists('/etc/fedora-release')) {
                $name = 'fedora';
                $content = file_get_contents('/etc/fedora-release');

                if (preg_match('/release\s+(\d+)/', $content, $matches)) {
                    $version = $matches[1];
                }

                $description = trim($content);
            } elseif (file_exists('/etc/alpine-release')) {
                $name = 'alpine';
                $version = trim(file_get_contents('/etc/alpine-release'));
                $description = "Alpine Linux {$version}";
            }
        }

        // 如果仍然无法确定，使用通用Linux
        if (empty($name)) {
            $name = 'linux';
            $description = 'Generic Linux';
            $version = php_uname('r');
        }

        return [
            'name' => $name,
            'description' => $description,
            'version' => $version,
            'arch' => $arch,
        ];
    }
}
