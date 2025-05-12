<?php

namespace VersionManager\Core\Version\Drivers;

use VersionManager\Core\Version\GenericVersionDriver;

/**
 * Ubuntu版本安装驱动类
 */
class UbuntuDriver extends GenericVersionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'ubuntu';
        $this->description = 'Ubuntu版本安装驱动';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);

        // 添加Ubuntu特定的配置选项
        $ubuntuOptions = [
            '--with-fpm-user=www-data',
            '--with-fpm-group=www-data',
        ];

        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $ubuntuOptions);

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
    protected function installDependencies($version = null)
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
        ];

        // 根据PHP版本添加特定的依赖
        if ($version) {
            list($major, $minor, $patch) = explode('.', $version);
            $majorMinor = (int)$major . '.' . (int)$minor;

            // PHP 7.4+
            if (version_compare($majorMinor, '7.4', '>=')) {
                $dependencies[] = 'libonig-dev';
            }

            // PHP 8.0+
            if (version_compare($majorMinor, '8.0', '>=')) {
                $dependencies[] = 'libavif-dev';
            }

            // PHP 8.1+
            if (version_compare($majorMinor, '8.1', '>=')) {
                $dependencies[] = 'libffi-dev';
            }
        }

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
