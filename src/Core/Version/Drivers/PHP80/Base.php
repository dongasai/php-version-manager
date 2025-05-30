<?php

namespace VersionManager\Core\Version\Drivers\PHP80;

use VersionManager\Core\Version\GenericVersionDriver;

/**
 * PHP 8.0 基础版本安装驱动类
 */
class Base extends GenericVersionDriver
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'php80';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = 'PHP 8.0 版本安装驱动';

    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 只支持PHP 8.0.x版本
        return preg_match('/^8\.0\.\d+$/', $version);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);

        // 添加PHP 8.0特定的配置选项
        $php80Options = [
            '--with-pear',
            '--enable-mysqlnd',
            '--enable-gd',
            '--with-jpeg',
            '--with-webp',
            '--with-freetype',
            '--with-xpm',
            '--with-avif',
        ];

        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $php80Options);

        return $configureOptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSourceUrl($version, $mirror = null)
    {
        // 使用UrlManager获取下载URL，支持镜像源
        $urlManager = new \VersionManager\Core\Download\UrlManager();
        return $urlManager->getPhpDownloadUrls($version);
    }
}
