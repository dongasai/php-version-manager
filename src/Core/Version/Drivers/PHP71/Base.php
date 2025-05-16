<?php

namespace VersionManager\Core\Version\Drivers\PHP71;



use VersionManager\Core\Tags\PhpTag;
use VersionManager\Core\Version\AbstractVersionDriver;

/**
 * PHP 7.1 基础版本安装驱动类
 */
class Base extends AbstractVersionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('php71', 'PHP 7.1 版本安装驱动');
    }




    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 只支持PHP 7.1.x版本
        return preg_match('/^7\.1\.\d+$/', $version);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);

        // 添加PHP 7.1特定的配置选项
        $php71Options = [
            '--with-mcrypt',
            '--enable-gd-native-ttf',
        ];

        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $php71Options);

        return $configureOptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSourceUrl($version, $mirror = null)
    {
        // 如果是PHP 7.1.0，则使用特定的URL
        if ($version === '7.1.0') {
            return "https://www.php.net/distributions/php-7.1.0.tar.gz";
        }

        return parent::getSourceUrl($version, $mirror);
    }

    public function getTags(): array
    {
        return [
            PhpTag::PHP71
        ];
    }

    public function install($version, array $options = [])
    {
        // TODO: Implement install() method.
    }

    public function remove($version, array $options = [])
    {
        // TODO: Implement remove() method.
    }

}
