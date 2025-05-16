<?php

namespace VersionManager\Core\Version\Drivers\PHP71;

/**
 * PHP 7.1 Debian版本安装驱动类
 */
class Debian extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 传递名称和描述给父类构造函数
        parent::__construct('php71_debian', 'PHP 7.1 Debian版本安装驱动');
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
            'libmcrypt-dev',
        ];

        // 使用主类的依赖安装方法
        $installer = new \VersionManager\Core\VersionInstaller();

        // 调用installDependencies方法
        // 使用反射调用私有方法
        $reflection = new \ReflectionClass($installer);
        $method = $reflection->getMethod('installDependencies');
        $method->setAccessible(true);

        try {
            $method->invoke($installer, $dependencies);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("安装依赖失败: " . $e->getMessage());
        }
    }
}
