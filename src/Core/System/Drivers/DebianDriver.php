<?php

namespace VersionManager\Core\System\Drivers;

use VersionManager\Core\System\AbstractOsDriver;

/**
 * Debian操作系统驱动类
 */
class DebianDriver extends AbstractOsDriver
{
    /**
     * {@inheritdoc}
     */
    protected function detectOsInfo()
    {
        // 默认设置Debian信息
        $this->name = 'debian';
        $this->description = 'Debian Linux';
        $this->version = '';

        // 从/etc/debian_version获取Debian版本信息
        if (file_exists('/etc/debian_version')) {
            $this->version = trim(file_get_contents('/etc/debian_version'));
            $this->description = "Debian Linux {$this->version}";
        }

        // 如果无法从/etc/debian_version获取信息，则尝试从/etc/os-release获取
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID']) && strtolower($osRelease['ID']) === 'debian') {
                if (isset($osRelease['VERSION_ID'])) {
                    $this->version = $osRelease['VERSION_ID'];
                }

                if (isset($osRelease['PRETTY_NAME'])) {
                    $this->description = $osRelease['PRETTY_NAME'];
                }
            }
        }

        // 如果仍然无法获取版本信息，则尝试使用apt命令
        if (empty($this->version) && $this->commandExists('apt')) {
            $output = [];
            $returnCode = 0;

            exec('apt --version | head -1 | cut -d" " -f2', $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                // apt版本可能与Debian版本不完全一致
                // 这里只是一个备选方案
                $this->version = trim($output[0]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies($phpVersion)
    {
        // 基本依赖
        $dependencies = [
            'build-essential',
            'libxml2-dev',
            'libssl-dev',
            'libcurl4-openssl-dev',
            'libjpeg-dev',
            'libpng-dev',
            'libfreetype6-dev',
            'libzip-dev',
            'libonig-dev',
            'libsqlite3-dev',
            'libicu-dev',
        ];

        // 根据PHP版本添加特定依赖
        $majorVersion = (int)substr($phpVersion, 0, 1);
        $minorVersion = (int)substr($phpVersion, 2, 1);

        if ($majorVersion === 5) {
            // PHP 5.x特定依赖
            $dependencies = array_merge($dependencies, [
                'libmcrypt-dev',
                'libreadline-dev',
                'libedit-dev',
                'libmysqlclient-dev',
            ]);
        } elseif ($majorVersion === 7 && $minorVersion < 4) {
            // PHP 7.0-7.3特定依赖
            $dependencies = array_merge($dependencies, [
                'libmcrypt-dev',
                'libreadline-dev',
                'libedit-dev',
            ]);
        }

        return $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function installDependencies(array $dependencies)
    {
        return $this->installPackages($dependencies);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionDependencies($extension, $phpVersion)
    {
        $dependencies = [];

        switch (strtolower($extension)) {
            case 'gd':
                $dependencies = [
                    'libjpeg-dev',
                    'libpng-dev',
                    'libfreetype6-dev',
                    'libwebp-dev',
                    'libxpm-dev',
                ];
                break;
            case 'curl':
                $dependencies = [
                    'libcurl4-openssl-dev',
                ];
                break;
            case 'intl':
                $dependencies = [
                    'libicu-dev',
                ];
                break;
            case 'mysql':
            case 'mysqli':
            case 'pdo_mysql':
                $dependencies = [
                    'libmysqlclient-dev',
                ];
                break;
            case 'pgsql':
            case 'pdo_pgsql':
                $dependencies = [
                    'libpq-dev',
                ];
                break;
            case 'ldap':
                $dependencies = [
                    'libldap2-dev',
                ];
                break;
            case 'snmp':
                $dependencies = [
                    'libsnmp-dev',
                ];
                break;
            case 'soap':
                $dependencies = [
                    'libxml2-dev',
                ];
                break;
            case 'zip':
                $dependencies = [
                    'libzip-dev',
                ];
                break;
            case 'bz2':
                $dependencies = [
                    'libbz2-dev',
                ];
                break;
            case 'enchant':
                $dependencies = [
                    'libenchant-dev',
                ];
                break;
            case 'gmp':
                $dependencies = [
                    'libgmp-dev',
                ];
                break;
            case 'imap':
                $dependencies = [
                    'libc-client-dev',
                    'libkrb5-dev',
                ];
                break;
            case 'interbase':
            case 'pdo_firebird':
                $dependencies = [
                    'firebird-dev',
                ];
                break;
            case 'imagick':
                $dependencies = [
                    'libmagickwand-dev',
                ];
                break;
            case 'redis':
                $dependencies = [];
                break;
            case 'memcached':
                $dependencies = [
                    'libmemcached-dev',
                ];
                break;
            case 'mongodb':
                $dependencies = [
                    'libssl-dev',
                ];
                break;
            case 'xdebug':
                $dependencies = [];
                break;
            default:
                $dependencies = [];
                break;
        }

        return $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function installExtensionDependencies($extension, $phpVersion)
    {
        $dependencies = $this->getExtensionDependencies($extension, $phpVersion);

        if (empty($dependencies)) {
            return true;
        }

        return $this->installPackages($dependencies);
    }

    /**
     * {@inheritdoc}
     */
    public function isPhpVersionSupported($phpVersion)
    {
        $majorVersion = (int)substr($phpVersion, 0, 1);
        $minorVersion = (int)substr($phpVersion, 2, 1);
        $debianVersion = (int)$this->version;

        // Debian 11支持PHP 5.6+
        if ($debianVersion >= 11) {
            return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 6);
        }

        // Debian 10支持PHP 5.6+
        if ($debianVersion >= 10) {
            return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 6);
        }

        // Debian 9支持PHP 5.6+
        if ($debianVersion >= 9) {
            return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 6);
        }

        // 其他版本默认支持PHP 5.4+
        return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 4);
    }

    /**
     * {@inheritdoc}
     */
    public function isExtensionSupported($extension, $phpVersion)
    {
        $majorVersion = (int)substr($phpVersion, 0, 1);
        $minorVersion = (int)substr($phpVersion, 2, 1);

        // 特定扩展的兼容性检查
        switch (strtolower($extension)) {
            case 'mcrypt':
                // mcrypt在PHP 7.2+中已被移除
                return $majorVersion < 7 || ($majorVersion === 7 && $minorVersion < 2);
            case 'mysql':
                // mysql在PHP 7.0+中已被移除
                return $majorVersion < 7;
            case 'mssql':
                // mssql在PHP 7.0+中已被移除
                return $majorVersion < 7;
            case 'interbase':
                // interbase在PHP 7.4+中已被移除
                return $majorVersion < 7 || ($majorVersion === 7 && $minorVersion < 4);
            case 'recode':
                // recode在PHP 7.4+中已被移除
                return $majorVersion < 7 || ($majorVersion === 7 && $minorVersion < 4);
            case 'wddx':
                // wddx在PHP 7.4+中已被移除
                return $majorVersion < 7 || ($majorVersion === 7 && $minorVersion < 4);
            default:
                return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPhpConfigOptions($phpVersion)
    {
        $majorVersion = (int)substr($phpVersion, 0, 1);
        $minorVersion = (int)substr($phpVersion, 2, 1);

        // 基本配置选项
        $options = [];

        // PHP 5.x特定配置选项
        if ($majorVersion === 5) {
            $options = array_merge($options, [
                '--with-mysql=mysqlnd',
                '--with-mysqli=mysqlnd',
                '--with-pdo-mysql=mysqlnd',
                '--with-mcrypt',
            ]);
        }

        // PHP 7.0-7.1特定配置选项
        if ($majorVersion === 7 && $minorVersion < 2) {
            $options = array_merge($options, [
                '--with-mcrypt',
            ]);
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function isPackageInstalled($package)
    {
        $command = "dpkg -l | grep -w '{$package}' | grep -v '^rc'";
        $output = [];
        $returnCode = 0;

        exec($command, $output, $returnCode);

        return $returnCode === 0 && !empty($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function installPackage($package)
    {
        if ($this->isPackageInstalled($package)) {
            return true;
        }

        // 检查是否有sudo权限
        $sudo = "";
        if (posix_getuid() !== 0) {
            // 检查sudo命令是否存在
            if ($this->commandExists('sudo')) {
                $sudo = "sudo ";
            } else {
                throw new \Exception("需要root权限安装依赖");
            }
        }

        // 使用sudo分别执行每个命令
        $command = "{$sudo}apt-get update && {$sudo}apt-get install -y {$package}";

        return $this->executeCommand($command);
    }

    /**
     * {@inheritdoc}
     */
    public function installPackages(array $packages, array $options = [])
    {
        if (empty($packages)) {
            return true;
        }

        // 过滤掉已安装的包
        $packagesToInstall = [];

        foreach ($packages as $package) {
            if (!$this->isPackageInstalled($package)) {
                $packagesToInstall[] = $package;
            }
        }

        if (empty($packagesToInstall)) {
            return true;
        }

        $packageList = implode(' ', $packagesToInstall);

        // 检查是否有sudo权限
        $sudo = "";
        if (posix_getuid() !== 0) {
            // 检查sudo命令是否存在
            if ($this->commandExists('sudo')) {
                $sudo = "sudo ";
            } else {
                throw new \Exception("需要root权限安装依赖");
            }
        }

        // 使用sudo分别执行每个命令
        $command = "{$sudo}apt-get update && {$sudo}apt-get install -y {$packageList}";

        return $this->executeCommand($command);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageManager()
    {
        return 'apt';
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackageCache(array $options = [])
    {
        echo "\033[33m更新软件包列表...\033[0m\n";

        $command = 'apt-get update';
        list($output, $returnCode) = $this->executeWithPrivileges($command, $options);

        // 对于apt-get update，只要退出码为0就认为成功，忽略警告信息
        if ($returnCode === 0) {
            echo "\033[32m软件包列表更新成功\033[0m\n";
            return true;
        }

        // 检查是否是权限问题
        $outputStr = implode("\n", $output);
        if (strpos($outputStr, '权限不够') !== false ||
            strpos($outputStr, 'Permission denied') !== false ||
            strpos($outputStr, '无法对目录') !== false ||
            strpos($outputStr, '无法打开锁文件') !== false) {
            throw new \Exception("权限不足，无法更新软件包列表");
        }

        // 检查是否是认证失败
        if (strpos($outputStr, '认证失败') !== false ||
            strpos($outputStr, 'Authentication failure') !== false) {
            throw new \Exception("认证失败，无法更新软件包列表");
        }

        // 检查是否是网络问题（ESM源连接超时等）
        if (strpos($outputStr, '连接超时') !== false ||
            strpos($outputStr, 'Connection timed out') !== false ||
            strpos($outputStr, '无法连接') !== false ||
            strpos($outputStr, 'Could not connect') !== false) {
            // 网络问题不应该阻止安装过程，只是警告
            echo "\033[33m警告: 部分软件源连接失败，但主要软件源可用\033[0m\n";
            return true;
        }

        throw new \Exception("更新软件包列表失败: " . $outputStr);
    }
}
