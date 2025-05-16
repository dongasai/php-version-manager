<?php

namespace VersionManager\Core\Version\Drivers\PHP80;

/**
 * PHP 8.0 Ubuntu x86_64版本安装驱动类
 */
class Ubuntu_X8664 extends Ubuntu
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 传递名称和描述给父类构造函数
        parent::__construct('php80_ubuntu_x8664', 'PHP 8.0 Ubuntu x86_64版本安装驱动');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);

        // 添加x86_64特定的配置选项
        $x8664Options = [
            '--enable-jit',
            '--with-pdo-pgsql',
        ];

        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $x8664Options);

        return $configureOptions;
    }
}
