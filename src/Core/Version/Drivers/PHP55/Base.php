<?php

namespace VersionManager\Core\Version\Drivers\PHP55;

use VersionManager\Core\Version\BaseVersionDriver;

/**
 * PHP 5.5版本安装驱动基础类
 */
class Base extends BaseVersionDriver
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'php55';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = 'PHP 5.5版本安装驱动';

    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 只支持PHP 5.5.x版本
        return preg_match('/^5\.5\.\d+$/', $version);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 基本配置选项
        $configureOptions = [
            "--prefix={$this->versionsDir}/{$version}",
            "--with-config-file-path={$this->versionsDir}/{$version}/etc",
            "--with-config-file-scan-dir={$this->versionsDir}/{$version}/etc/conf.d",
            "--enable-fpm",
            "--enable-mbstring",
            "--enable-zip",
            "--with-mysql",
            "--with-mysqli",
            "--with-pdo-mysql",
            "--with-curl",
            "--with-openssl",
            "--with-zlib",
            "--with-gd",
            "--with-jpeg-dir",
            "--with-png-dir",
            "--with-freetype-dir",
            "--enable-bcmath",
            "--enable-exif",
            "--enable-ftp",
            "--enable-intl",
            "--enable-soap",
            "--enable-sockets",
            "--enable-opcache",
        ];

        // 添加自定义配置选项
        if (isset($options['configure_options']) && is_array($options['configure_options'])) {
            $configureOptions = array_merge($configureOptions, $options['configure_options']);
        }

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

    /**
     * {@inheritdoc}
     */
    protected function getDependencies($version)
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
            'libmcrypt-dev',
            'libreadline-dev',
            'libedit-dev',
            'libzip-dev',
            'libicu-dev',
        ];

        return $dependencies;
    }
}
