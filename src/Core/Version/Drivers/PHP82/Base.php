<?php

namespace VersionManager\Core\Version\Drivers\PHP82;

use VersionManager\Core\Version\GenericVersionDriver;

/**
 * PHP 8.2版本安装驱动基础类
 */
class Base extends GenericVersionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'php82';
        $this->description = 'PHP 8.2版本安装驱动';
    }
    
    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 只支持PHP 8.2.x版本
        return preg_match('/^8\.2\.\d+$/', $version);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);
        
        // 添加PHP 8.2特定的配置选项
        $php82Options = [
            '--enable-gd',
            '--with-jpeg',
            '--with-webp',
            '--with-freetype',
            '--with-xpm',
            '--with-avif',
            '--with-ffi',
            '--enable-jit',
        ];
        
        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $php82Options);
        
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
            'libavif-dev',
            'libfreetype6-dev',
            'libxpm-dev',
            'libzip-dev',
            'libonig-dev',
            'libsqlite3-dev',
            'libsodium-dev',
            'libargon2-dev',
            'libffi-dev',
        ];
        
        return $dependencies;
    }
}
