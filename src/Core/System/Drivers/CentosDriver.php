<?php

namespace VersionManager\Core\System\Drivers;

use VersionManager\Core\System\AbstractOsDriver;

/**
 * CentOS操作系统驱动类
 */
class CentosDriver extends AbstractOsDriver
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies($phpVersion)
    {
        // 基本依赖
        $dependencies = [
            'gcc',
            'gcc-c++',
            'make',
            'autoconf',
            'libxml2-devel',
            'openssl-devel',
            'curl-devel',
            'libjpeg-devel',
            'libpng-devel',
            'freetype-devel',
            'libzip-devel',
            'sqlite-devel',
            'libicu-devel',
            'oniguruma-devel',
        ];
        
        // 根据PHP版本添加特定依赖
        $majorVersion = (int)substr($phpVersion, 0, 1);
        $minorVersion = (int)substr($phpVersion, 2, 1);
        
        if ($majorVersion === 5) {
            // PHP 5.x特定依赖
            $dependencies = array_merge($dependencies, [
                'libmcrypt-devel',
                'readline-devel',
                'libedit-devel',
                'mysql-devel',
            ]);
        } elseif ($majorVersion === 7 && $minorVersion < 4) {
            // PHP 7.0-7.3特定依赖
            $dependencies = array_merge($dependencies, [
                'libmcrypt-devel',
                'readline-devel',
                'libedit-devel',
            ]);
        }
        
        // CentOS 8+使用不同的包名
        $centosVersion = (int)$this->version;
        if ($centosVersion >= 8) {
            // 替换一些包名
            $dependencies = array_map(function($package) {
                switch ($package) {
                    case 'mysql-devel':
                        return 'mariadb-devel';
                    default:
                        return $package;
                }
            }, $dependencies);
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
        
        // CentOS 8+使用不同的包名
        $centosVersion = (int)$this->version;
        $isNewCentos = $centosVersion >= 8;
        
        switch (strtolower($extension)) {
            case 'gd':
                $dependencies = [
                    'libjpeg-devel',
                    'libpng-devel',
                    'freetype-devel',
                    'libwebp-devel',
                    'libXpm-devel',
                ];
                break;
            case 'curl':
                $dependencies = [
                    'curl-devel',
                ];
                break;
            case 'intl':
                $dependencies = [
                    'libicu-devel',
                ];
                break;
            case 'mysql':
            case 'mysqli':
            case 'pdo_mysql':
                $dependencies = [
                    $isNewCentos ? 'mariadb-devel' : 'mysql-devel',
                ];
                break;
            case 'pgsql':
            case 'pdo_pgsql':
                $dependencies = [
                    'postgresql-devel',
                ];
                break;
            case 'ldap':
                $dependencies = [
                    'openldap-devel',
                ];
                break;
            case 'snmp':
                $dependencies = [
                    'net-snmp-devel',
                ];
                break;
            case 'soap':
                $dependencies = [
                    'libxml2-devel',
                ];
                break;
            case 'zip':
                $dependencies = [
                    'libzip-devel',
                ];
                break;
            case 'bz2':
                $dependencies = [
                    'bzip2-devel',
                ];
                break;
            case 'enchant':
                $dependencies = [
                    'enchant-devel',
                ];
                break;
            case 'gmp':
                $dependencies = [
                    'gmp-devel',
                ];
                break;
            case 'imap':
                $dependencies = [
                    'libc-client-devel',
                    'krb5-devel',
                ];
                break;
            case 'interbase':
            case 'pdo_firebird':
                $dependencies = [
                    'firebird-devel',
                ];
                break;
            case 'imagick':
                $dependencies = [
                    'ImageMagick-devel',
                ];
                break;
            case 'redis':
                $dependencies = [];
                break;
            case 'memcached':
                $dependencies = [
                    'libmemcached-devel',
                ];
                break;
            case 'mongodb':
                $dependencies = [
                    'openssl-devel',
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
        $centosVersion = (int)$this->version;
        
        // CentOS 8支持PHP 5.6+
        if ($centosVersion >= 8) {
            return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 6);
        }
        
        // CentOS 7支持PHP 5.4+
        if ($centosVersion >= 7) {
            return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 4);
        }
        
        // CentOS 6支持PHP 5.3+
        if ($centosVersion >= 6) {
            return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 3);
        }
        
        // 其他版本默认支持PHP 5.2+
        return $majorVersion >= 5 && ($majorVersion > 5 || $minorVersion >= 2);
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
    protected function isPackageInstalled($package)
    {
        $command = "rpm -q {$package}";
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);
        
        return $returnCode === 0;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function installPackage($package)
    {
        if ($this->isPackageInstalled($package)) {
            return true;
        }
        
        // 检查CentOS版本
        $centosVersion = (int)$this->version;
        
        if ($centosVersion >= 8) {
            $command = "dnf install -y {$package}";
        } else {
            $command = "yum install -y {$package}";
        }
        
        // 检查是否有sudo权限
        if (posix_getuid() !== 0) {
            // 检查sudo命令是否存在
            if ($this->commandExists('sudo')) {
                $command = "sudo {$command}";
            } else {
                throw new \Exception("需要root权限安装依赖");
            }
        }
        
        return $this->executeCommand($command);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function installPackages(array $packages)
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
        
        // 检查CentOS版本
        $centosVersion = (int)$this->version;
        
        if ($centosVersion >= 8) {
            $command = "dnf install -y {$packageList}";
        } else {
            $command = "yum install -y {$packageList}";
        }
        
        // 检查是否有sudo权限
        if (posix_getuid() !== 0) {
            // 检查sudo命令是否存在
            if ($this->commandExists('sudo')) {
                $command = "sudo {$command}";
            } else {
                throw new \Exception("需要root权限安装依赖");
            }
        }
        
        return $this->executeCommand($command);
    }
}
