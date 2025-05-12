<?php

namespace VersionManager\Core\Version\Drivers\PHP80;

/**
 * PHP 8.0 Debian版本安装驱动类
 */
class Debian extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'php80_debian';
        $this->description = 'PHP 8.0 Debian版本安装驱动';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);
        
        // 添加Debian特定的配置选项
        $debianOptions = [
            '--with-fpm-user=www-data',
            '--with-fpm-group=www-data',
        ];
        
        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $debianOptions);
        
        return $configureOptions;
    }
    
    /**
     * {@inheritdoc}
     */
    public function install($version, array $options = [])
    {
        // 安装依赖
        $this->installDependencies($version);
        
        // 调用父类的安装方法
        return parent::install($version, $options);
    }
    
    /**
     * 安装依赖
     *
     * @param string $version PHP版本
     * @return bool
     */
    protected function installDependencies($version)
    {
        // 基本依赖
        $dependencies = [
            'build-essential',
            'libxml2-dev',
            'libssl-dev',
            'libsqlite3-dev',
            'zlib1g-dev',
            'libcurl4-openssl-dev',
            'libpng-dev',
            'libjpeg-dev',
            'libfreetype6-dev',
            'libwebp-dev',
            'libxpm-dev',
            'libonig-dev',
            'libzip-dev',
            'libavif-dev',
        ];
        
        // 安装依赖
        $command = 'apt-get update && apt-get install -y ' . implode(' ', $dependencies);
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装依赖失败: " . implode("\n", $output));
        }
        
        return true;
    }
}
