<?php

namespace VersionManager\Core;

use VersionManager\Core\Version\VersionDriverFactory;
use VersionManager\Core\Version\VersionDriverInterface;

/**
 * 版本管理器类
 *
 * 负责管理所有PHP版本
 */
class VersionManager
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
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $versionSwitcher;

    /**
     * 版本安装器
     *
     * @var VersionInstaller
     */
    private $versionInstaller;

    /**
     * 版本删除器
     *
     * @var VersionRemover
     */
    private $versionRemover;

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
        $this->pvmRoot = getenv('HOME') . '/.pvm';
        $this->versionsDir = $this->pvmRoot . '/versions';
        $this->versionSwitcher = new VersionSwitcher();
        $this->versionInstaller = new VersionInstaller();
        $this->versionRemover = new VersionRemover();
        $this->supportedVersions = new SupportedVersions();

        // 确保目录存在
        if (!is_dir($this->pvmRoot)) {
            mkdir($this->pvmRoot, 0755, true);
        }

        if (!is_dir($this->versionsDir)) {
            mkdir($this->versionsDir, 0755, true);
        }
    }

    /**
     * 安装PHP版本
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return bool 是否安装成功
     */
    public function install($version, array $options = [])
    {
        return $this->versionInstaller->install($version, $options);
    }

    /**
     * 使用PHP版本
     *
     * @param string $version PHP版本
     * @param bool $global 是否设置为全局版本
     * @return bool 是否切换成功
     */
    public function use($version, $global = false)
    {
        return $this->versionSwitcher->switchVersion($version, $global);
    }

    /**
     * 删除PHP版本
     *
     * @param string $version PHP版本
     * @param array $options 删除选项
     * @return bool 是否删除成功
     */
    public function remove($version, array $options = [])
    {
        return $this->versionRemover->remove($version, $options);
    }

    /**
     * 获取已安装的PHP版本列表
     *
     * @return array 已安装的PHP版本列表
     */
    public function getInstalledVersions()
    {
        return $this->versionSwitcher->getInstalledVersions();
    }

    /**
     * 检查版本是否已安装
     *
     * @param string $version PHP版本
     * @return bool 是否已安装
     */
    public function isVersionInstalled($version)
    {
        return $this->versionSwitcher->isVersionInstalled($version);
    }

    /**
     * 获取当前PHP版本
     *
     * @return string 当前PHP版本
     */
    public function getCurrentVersion()
    {
        return $this->versionSwitcher->getCurrentVersion();
    }

    /**
     * 获取全局PHP版本
     *
     * @return string|null 全局PHP版本，如果未设置则返回null
     */
    public function getGlobalVersion()
    {
        return $this->versionSwitcher->getGlobalVersion();
    }

    /**
     * 获取项目PHP版本
     *
     * @param string|null $dir 项目目录，如果为null则使用当前目录
     * @return string|null 项目PHP版本，如果未设置则返回null
     */
    public function getProjectVersion($dir = null)
    {
        return $this->versionSwitcher->getProjectVersion($dir);
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
        return $this->versionSwitcher->setProjectVersion($version, $dir);
    }
    /**
     * 获取可用的PHP版本列表
     *
     * @return array 可用的PHP版本数组，包含version和release_date
     */
    public function getAvailableVersions()
    {
        $supported = $this->supportedVersions->getSupportedVersionsForCurrentSystem();
        $versions = [];

        foreach (array_keys($supported) as $version) {
            $versions[] = [
                'version' => $version,
                'release_date' => $this->getReleaseDate($version)
            ];
        }

        return $versions;
    }

    /**
     * 获取PHP版本的发布日期
     *
     * @param string $version PHP版本号
     * @return string 发布日期字符串
     */
    private function getReleaseDate($version)
    {
        // 这里可以添加更精确的发布日期查询逻辑
        // 目前返回一个默认格式的日期
        return date('Y-m-d', strtotime("-$version years"));
    }
}
