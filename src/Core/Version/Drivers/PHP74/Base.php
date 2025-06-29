<?php

namespace VersionManager\Core\Version\Drivers\PHP74;

use VersionManager\Core\Version\BaseVersionDriver;
use VersionManager\Core\Version\Util\ConfigureHelper;

/**
 * PHP 7.4版本安装驱动基础类
 */
class Base extends BaseVersionDriver
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'php74';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = 'PHP 7.4版本安装驱动';

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
        $prefix = $this->versionsDir . '/' . $version;
        $customOptions = isset($options['configure_options']) ? $options['configure_options'] : [];

        // 获取基础配置选项
        $configureOptions = ConfigureHelper::getBaseConfigureOptions($version, $prefix);

        // 添加PHP 7.4特定的GD配置选项
        $gdOptions = ConfigureHelper::getGdConfigureOptions($version);
        $configureOptions = array_merge($configureOptions, $gdOptions);

        // 合并自定义选项
        if (!empty($customOptions)) {
            $configureOptions = array_merge($configureOptions, $customOptions);
        }

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
