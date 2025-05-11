<?php

namespace VersionManager\Core;

/**
 * PHP版本检测类
 *
 * 负责检测系统中已安装的PHP版本和可用的PHP版本
 */
class VersionDetector
{
    /**
     * PHP官方下载地址
     */
    const PHP_DOWNLOAD_URL = 'https://www.php.net/downloads.php';

    /**
     * PHP版本镜像源
     */
    const PHP_MIRROR_URL = 'https://www.php.net/releases/';

    /**
     * 支持的版本管理器
     *
     * @var SupportedVersions
     */
    private $supportedVersions;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->supportedVersions = new SupportedVersions();
    }

    /**
     * 获取当前系统默认PHP版本
     *
     * @return string|null 返回PHP版本号，如果未安装PHP则返回null
     */
    public function getCurrentVersion()
    {
        $output = [];
        $returnCode = 0;

        // 执行php -v命令获取版本信息
        exec('php -v 2>/dev/null', $output, $returnCode);

        if ($returnCode !== 0 || empty($output)) {
            return null;
        }

        // 解析版本号
        $versionLine = $output[0];
        if (preg_match('/PHP\s+([0-9]+\.[0-9]+\.[0-9]+)/', $versionLine, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 获取系统中所有已安装的PHP版本
     *
     * @return array 返回已安装的PHP版本列表
     */
    public function getInstalledVersions()
    {
        $versions = [];
        $homeDir = getenv('HOME');
        $pvmDir = $homeDir . '/.pvm/versions';

        // 检查PVM目录是否存在
        if (!is_dir($pvmDir)) {
            return $versions;
        }

        // 扫描版本目录
        $dirs = scandir($pvmDir);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            // 检查是否是有效的PHP版本目录
            if (is_dir($pvmDir . '/' . $dir) && file_exists($pvmDir . '/' . $dir . '/bin/php')) {
                $versions[] = $dir;
            }
        }

        return $versions;
    }



    /**
     * 获取可用的PHP版本列表
     *
     * @return array 返回可用的PHP版本列表
     */
    public function getAvailableVersions()
    {
        // 获取当前系统支持的PHP版本
        $supportedVersions = $this->supportedVersions->getSupportedVersionsForCurrentSystem();

        // 过滤出推荐的版本
        $recommendedVersions = [];
        foreach ($supportedVersions as $version => $supportLevel) {
            if ($this->supportedVersions->isRecommended($version)) {
                // 添加最新的补丁版本
                switch ($version) {
                    case '7.1':
                        $recommendedVersions[] = '7.1.33';
                        break;
                    case '7.2':
                        $recommendedVersions[] = '7.2.34';
                        break;
                    case '7.3':
                        $recommendedVersions[] = '7.3.33';
                        break;
                    case '7.4':
                        $recommendedVersions[] = '7.4.33';
                        break;
                    case '8.0':
                        $recommendedVersions[] = '8.0.30';
                        break;
                    case '8.1':
                        $recommendedVersions[] = '8.1.27';
                        break;
                    case '8.2':
                        $recommendedVersions[] = '8.2.17';
                        break;
                    case '8.3':
                        $recommendedVersions[] = '8.3.5';
                        break;
                }
            }
        }

        // 如果没有推荐的版本，返回默认版本
        if (empty($recommendedVersions)) {
            return [
                '7.1.33', // PHP 7.1系列最新版本
                '7.2.34', // PHP 7.2系列最新版本
                '7.3.33', // PHP 7.3系列最新版本
                '7.4.33', // PHP 7.4系列最新版本
                '8.0.30', // PHP 8.0系列最新版本
                '8.1.27', // PHP 8.1系列最新版本
                '8.2.17', // PHP 8.2系列最新版本
                '8.3.5',  // PHP 8.3系列最新版本
            ];
        }

        return $recommendedVersions;
    }

    /**
     * 检查指定PHP版本是否与当前系统兼容
     *
     * @param string $version PHP版本号
     * @return bool 返回是否兼容
     */
    public function isVersionCompatible($version)
    {
        // 检查操作系统兼容性
        $osCompatible = $this->checkOSCompatibility($version);

        // 检查架构兼容性
        $archCompatible = $this->checkArchCompatibility($version);

        return $osCompatible && $archCompatible;
    }

    /**
     * 检查操作系统兼容性
     *
     * @param string $version PHP版本号
     * @return bool 返回是否兼容
     */
    private function checkOSCompatibility($version)
    {
        // 获取操作系统信息
        $osInfo = php_uname('s');

        // 目前只支持Linux系统
        if (stripos($osInfo, 'Linux') === false) {
            return false;
        }

        // 检查Linux发行版
        $output = [];
        exec('cat /etc/os-release 2>/dev/null', $output);

        $distro = '';
        foreach ($output as $line) {
            if (strpos($line, 'ID=') === 0) {
                $distro = trim(substr($line, 3), '"\'');
                break;
            }
        }

        // 检查是否是支持的发行版
        $supportedDistros = ['ubuntu', 'debian', 'centos', 'fedora', 'rhel', 'alpine'];

        return in_array(strtolower($distro), $supportedDistros);
    }

    /**
     * 检查架构兼容性
     *
     * @param string $version PHP版本号
     * @return bool 返回是否兼容
     */
    private function checkArchCompatibility($version)
    {
        // 获取系统架构
        $arch = php_uname('m');

        // 支持的架构
        $supportedArch = ['x86_64', 'aarch64', 'armv7l', 'armv8'];

        return in_array($arch, $supportedArch);
    }

    /**
     * 获取PHP版本的依赖关系
     *
     * @param string $version PHP版本号
     * @return array 返回依赖列表
     */
    public function getVersionDependencies($version)
    {
        // 基础依赖
        $dependencies = [
            'build-essential',
            'libxml2-dev',
            'libssl-dev',
            'libcurl4-openssl-dev',
            'libpng-dev',
            'libjpeg-dev',
            'libzip-dev',
            'libonig-dev',
        ];

        // 版本特定依赖
        if (version_compare($version, '7.4.0', '>=')) {
            $dependencies[] = 'libsqlite3-dev';
        }

        if (version_compare($version, '8.0.0', '>=')) {
            $dependencies[] = 'libffi-dev';
        }

        return $dependencies;
    }

    /**
     * 检查系统是否满足指定PHP版本的依赖要求
     *
     * @param string $version PHP版本号
     * @return array 返回缺失的依赖列表
     */
    public function checkDependencies($version)
    {
        $dependencies = $this->getVersionDependencies($version);
        $missingDependencies = [];

        // 检测包管理器
        $packageManager = $this->detectPackageManager();
        if (!$packageManager) {
            return $dependencies; // 无法检测包管理器，假设所有依赖都缺失
        }

        // 根据不同的包管理器检查依赖
        switch ($packageManager) {
            case 'apt':
                foreach ($dependencies as $dependency) {
                    $output = [];
                    exec("dpkg -s $dependency 2>/dev/null", $output, $returnCode);
                    if ($returnCode !== 0) {
                        $missingDependencies[] = $dependency;
                    }
                }
                break;

            case 'yum':
            case 'dnf':
                foreach ($dependencies as $dependency) {
                    $output = [];
                    exec("rpm -q $dependency 2>/dev/null", $output, $returnCode);
                    if ($returnCode !== 0) {
                        $missingDependencies[] = $dependency;
                    }
                }
                break;

            case 'apk':
                foreach ($dependencies as $dependency) {
                    $output = [];
                    exec("apk info -e $dependency 2>/dev/null", $output, $returnCode);
                    if ($returnCode !== 0) {
                        $missingDependencies[] = $dependency;
                    }
                }
                break;

            default:
                return $dependencies; // 不支持的包管理器，假设所有依赖都缺失
        }

        return $missingDependencies;
    }

    /**
     * 检测系统包管理器
     *
     * @return string|null 返回包管理器名称，如果未检测到则返回null
     */
    private function detectPackageManager()
    {
        $packageManagers = [
            'apt' => 'apt-get',
            'yum' => 'yum',
            'dnf' => 'dnf',
            'apk' => 'apk'
        ];

        foreach ($packageManagers as $name => $command) {
            $output = [];
            exec("which $command 2>/dev/null", $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                return $name;
            }
        }

        return null;
    }
}
