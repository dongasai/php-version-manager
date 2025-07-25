<?php

namespace VersionManager\Core;

use VersionManager\Core\Version\VersionDriverFactory;

/**
 * 版本切换器类
 *
 * 负责管理和切换PHP版本
 */
class VersionSwitcher
{
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmRoot;

    /**
     * 版本目录
     *
     * @var string
     */
    private $versionsDir;

    /**
     * 符号链接目录
     *
     * @var string
     */
    private $shimsDir;

    /**
     * 全局版本文件
     *
     * @var string
     */
    private $globalVersionFile;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->pvmRoot = getenv('HOME') . '/.pvm';
        $this->versionsDir = $this->pvmRoot . '/versions';
        $this->shimsDir = $this->pvmRoot . '/shims';
        $this->globalVersionFile = $this->pvmRoot . '/version';

        // 确保目录存在
        if (!is_dir($this->pvmRoot)) {
            mkdir($this->pvmRoot, 0755, true);
        }

        if (!is_dir($this->versionsDir)) {
            mkdir($this->versionsDir, 0755, true);
        }

        if (!is_dir($this->shimsDir)) {
            mkdir($this->shimsDir, 0755, true);
        }
    }

    /**
     * 获取当前PHP版本
     *
     * @return string 当前PHP版本
     */
    public function getCurrentVersion()
    {
        // 优先使用项目版本
        $projectVersion = $this->getProjectVersion();
        if ($projectVersion !== null && $this->isVersionInstalled($projectVersion)) {
            return $projectVersion;
        }

        // 其次使用全局版本
        $globalVersion = $this->getGlobalVersion();
        if ($globalVersion !== null && $this->isVersionInstalled($globalVersion)) {
            return $globalVersion;
        }

        // 最后使用系统PHP版本
        return $this->getSystemVersion();
    }

    /**
     * 获取系统PHP版本
     *
     * @return string 系统PHP版本
     */
    public function getSystemVersion()
    {
        $output = [];
        exec('php -r "echo PHP_VERSION;"', $output);

        if (!empty($output)) {
            return $output[0];
        }

        return '7.1.0'; // 默认版本
    }

    /**
     * 切换PHP版本
     *
     * @param string $version PHP版本
     * @param bool $global 是否设置为全局版本
     * @return bool 是否切换成功
     */
    public function switchVersion($version, $global = false)
    {
        // 检查版本是否已安装
        if (!$this->isVersionInstalled($version)) {
            return false;
        }

        // 更新符号链接
        if (!$this->updateShims($version)) {
            return false;
        }

        // 如果设置为全局版本，则更新全局版本文件
        if ($global) {
            file_put_contents($this->globalVersionFile, $version);
        }

        return true;
    }

    /**
     * 安装PHP版本
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return bool 是否安装成功
     */
    public function installVersion($version, array $options = [])
    {
        // 检查版本是否已安装
        if ($this->isVersionInstalled($version)) {
            return false;
        }

        // 解析版本号
        list($major, $minor, $patch) = explode('.', $version);
        $phpVersionKey = "PHP{$major}{$minor}";

        // 获取版本安装驱动
        $driver = VersionDriverFactory::getDriver($version);

        // 检查版本是否支持
        if (!$driver->isSupported($version)) {
            return false;
        }

        // 安装版本
        return $driver->install($version, $options);
    }

    /**
     * 删除PHP版本
     *
     * @param string $version PHP版本
     * @return bool 是否删除成功
     */
    public function removeVersion($version)
    {
        // 检查版本是否已安装
        if (!$this->isVersionInstalled($version)) {
            return false;
        }

        // 解析版本号
        list($major, $minor, $patch) = explode('.', $version);
        $phpVersionKey = "PHP{$major}{$minor}";

        // 获取版本安装驱动
        $driver = VersionDriverFactory::getDriver($version);

        // 删除版本
        return $driver->remove($version);
    }

    /**
     * 获取已安装的PHP版本列表
     *
     * @return array 已安装的PHP版本列表
     */
    public function getInstalledVersions()
    {
        $versions = [];

        // 添加系统PHP版本
        $systemVersion = $this->getSystemVersion();
        if ($systemVersion) {
            $versions[] = [
                'version' => $systemVersion,
                'type' => 'system',
                'path' => $this->getSystemPhpPath(),
                'status' => 'active',
                'is_current' => true,
            ];
        }

        // 添加PVM管理的版本
        if (is_dir($this->versionsDir)) {
            $dirs = scandir($this->versionsDir);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }

                $versionDir = $this->versionsDir . '/' . $dir;
                if (is_dir($versionDir)) {
                    $phpBin = $versionDir . '/bin/php';
                    $status = file_exists($phpBin) && is_executable($phpBin) ? 'installed' : 'incomplete';

                    $versions[] = [
                        'version' => $dir,
                        'type' => 'pvm',
                        'path' => $phpBin,
                        'status' => $status,
                        'is_current' => false,
                    ];
                }
            }
        }

        return $versions;
    }

    /**
     * 获取系统PHP路径
     *
     * @return string
     */
    private function getSystemPhpPath()
    {
        $output = [];
        exec('which php', $output);
        return !empty($output) ? $output[0] : '/usr/bin/php';
    }

    /**
     * 设置项目PHP版本
     *
     * @param string $version PHP版本
     * @param string|null $dir 项目目录，如果为null则使用当前目录
     * @return bool 是否设置成功
     */
    public function setProjectVersion($version, $dir = null)
    {
        if ($dir === null) {
            $dir = getcwd();
        }

        // 检查版本是否已安装
        if (!$this->isVersionInstalled($version)) {
            return false;
        }

        // 写入项目版本文件
        $versionFile = $dir . '/.php-version';
        file_put_contents($versionFile, $version);

        return true;
    }

    /**
     * 获取项目PHP版本
     *
     * @param string|null $dir 项目目录，如果为null则使用当前目录
     * @return string|null 项目PHP版本，如果未设置则返回null
     */
    public function getProjectVersion($dir = null)
    {
        if ($dir === null) {
            $dir = getcwd();
        }

        // 向上递归查找.php-version文件
        while ($dir !== '/' && $dir !== '') {
            $versionFile = $dir . '/.php-version';
            if (file_exists($versionFile)) {
                return trim(file_get_contents($versionFile));
            }
            $dir = dirname($dir);
        }

        return null;
    }

    /**
     * 获取全局PHP版本
     *
     * @return string|null 全局PHP版本，如果未设置则返回null
     */
    public function getGlobalVersion()
    {
        if (file_exists($this->globalVersionFile)) {
            return trim(file_get_contents($this->globalVersionFile));
        }

        return null;
    }

    /**
     * 检查版本是否已安装
     *
     * @param string $version PHP版本
     * @return bool 是否已安装
     */
    public function isVersionInstalled($version)
    {
        // 检查是否是系统版本
        $systemVersion = $this->getSystemVersion();
        if ($version === $systemVersion) {
            return true;
        }

        // 检查PVM管理的版本
        $versionDir = $this->versionsDir . '/' . $version;
        $phpBin = $versionDir . '/bin/php';

        return is_dir($versionDir) && file_exists($phpBin) && is_executable($phpBin);
    }

    /**
     * 更新符号链接
     *
     * @param string $version PHP版本
     * @return bool 是否更新成功
     */
    private function updateShims($version)
    {
        // 获取版本安装驱动
        $driver = VersionDriverFactory::getDriver();

        // 获取版本目录
        $versionDir = $this->versionsDir . '/' . $version;
        $binDir = $versionDir . '/bin';

        // 检查bin目录是否存在
        if (!is_dir($binDir)) {
            return false;
        }

        // 清空符号链接目录
        $this->clearShimsDir();

        // 创建符号链接
        $binFiles = scandir($binDir);
        foreach ($binFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $source = $binDir . '/' . $file;
            $target = $this->shimsDir . '/' . $file;

            if (is_file($source) && is_executable($source)) {
                symlink($source, $target);
            }
        }

        return true;
    }

    /**
     * 清空符号链接目录
     *
     * @return bool 是否清空成功
     */
    private function clearShimsDir()
    {
        if (!is_dir($this->shimsDir)) {
            return false;
        }

        $files = scandir($this->shimsDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $this->shimsDir . '/' . $file;

            if (is_link($path) || is_file($path)) {
                unlink($path);
            }
        }

        return true;
    }
}
