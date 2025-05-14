<?php

namespace VersionManager\Core\Version\Drivers\PHP74;

use VersionManager\Core\Version\GenericVersionDriver;

/**
 * PHP 7.4版本安装驱动基础类
 */
class Base extends GenericVersionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('php74', 'PHP 7.4版本安装驱动');
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return ['php74'];
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 只支持PHP 7.4.x版本
        return preg_match('/^7\.4\.\d+$/', $version);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);

        // 添加PHP 7.4特定的配置选项
        $php74Options = [
            '--enable-gd',
            '--with-jpeg',
            '--with-webp',
            '--with-freetype',
            '--with-xpm',
        ];

        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $php74Options);

        return $configureOptions;
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
            'libwebp-dev',
            'libfreetype6-dev',
            'libxpm-dev',
            'libzip-dev',
            'libonig-dev',
            'libsqlite3-dev',
            'libsodium-dev',
            'libargon2-dev',
        ];

        return $dependencies;
    }
}
