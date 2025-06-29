<?php

namespace VersionManager\Core\Version;

use VersionManager\Core\Version\Util\VersionHelper;
use VersionManager\Core\Version\Util\ConfigureHelper;
use VersionManager\Core\Version\Util\FileHelper;
use VersionManager\Core\Version\Util\DownloadHelper;
use VersionManager\Core\Version\Util\CompileHelper;
use VersionManager\Core\Version\Util\PhpConfigHelper;

/**
 * 基础版本安装驱动类
 *
 * 提供通用的PHP版本安装功能
 */
class BaseVersionDriver extends AbstractVersionDriver
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'base';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = '基础版本安装驱动';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return ['base', $this->name];
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        return VersionHelper::isSupportedVersion($version);
    }

    /**
     * {@inheritdoc}
     */
    public function install($version, array $options = [])
    {
        // 检查版本是否支持
        if (!$this->isSupported($version)) {
            throw new \Exception("不支持的PHP版本: {$version}");
        }

        // 检查版本是否已安装
        if ($this->isInstalled($version)) {
            throw new \Exception("PHP版本 {$version} 已经安装");
        }

        // 创建临时目录
        $tempDir = $this->createTempDir('pvm_php_' . $version . '_');

        try {
            // 安装系统依赖
            $dependencies = $this->getDependencies($version);
            if (!empty($dependencies)) {
                $this->installDependencies($dependencies);
            }

            // 下载PHP源码
            $mirror = isset($options['mirror']) ? $options['mirror'] : null;
            $sourceUrls = $this->getSourceUrl($version, $mirror);
            $sourceFile = $tempDir . '/php-' . $version . '.tar.gz';
            $this->downloadFile($sourceUrls, $sourceFile);

            // 解压源码
            $sourceDir = $tempDir . '/php-' . $version;
            FileHelper::ensureDirectoryExists($sourceDir);
            $this->extractFile($sourceFile, $sourceDir);

            // 查找源码目录
            $phpSourceDir = FileHelper::findPhpSourceDir($sourceDir);
            if (!$phpSourceDir) {
                throw new \Exception("无法找到PHP源码目录");
            }

            // 配置编译选项
            $configureOptions = $this->getConfigureOptions($version, $options);

            // 编译安装PHP
            $jobs = isset($options['jobs']) ? (int)$options['jobs'] : CompileHelper::getRecommendedJobCount();
            CompileHelper::compileAndInstall($phpSourceDir, $version, $configureOptions, $jobs);

            // 配置PHP
            $versionDir = $this->versionsDir . '/' . $version;
            PhpConfigHelper::configurePhp($version, $versionDir);

            // 清理临时目录
            $this->removeDirectory($tempDir);

            return true;
        } catch (\Exception $e) {
            // 清理临时目录
            $this->removeDirectory($tempDir);

            // 如果安装失败，则删除已安装的文件
            $versionDir = $this->versionsDir . '/' . $version;
            if (is_dir($versionDir)) {
                $this->removeDirectory($versionDir);
            }

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($version, array $options = [])
    {
        // 检查版本是否已安装
        if (!$this->isInstalled($version)) {
            throw new \Exception("PHP版本 {$version} 未安装");
        }

        // 删除版本目录
        $versionDir = $this->versionsDir . '/' . $version;
        $this->removeDirectory($versionDir);

        return true;
    }

    /**
     * 获取PHP源码URL
     *
     * @param string $version PHP版本
     * @param string $mirror 镜像名称，如果为null则使用默认镜像
     * @return array 源码URL数组（按优先级排序）
     */
    protected function getSourceUrl($version, $mirror = null)
    {
        // 使用UrlManager获取下载URL，支持镜像源
        $urlManager = new \VersionManager\Core\Download\UrlManager();
        return $urlManager->getPhpDownloadUrls($version);
    }

    /**
     * 获取系统依赖
     *
     * @param string $version PHP版本
     * @return array 依赖包列表
     */
    protected function getDependencies($version)
    {
        return $this->getBaseDependencies($version);
    }

    /**
     * 获取配置选项
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return array
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        $prefix = $this->versionsDir . '/' . $version;
        $customOptions = isset($options['configure_options']) ? $options['configure_options'] : [];
        
        return ConfigureHelper::getFullConfigureOptions($version, $prefix, $customOptions);
    }


}
