<?php

namespace VersionManager\Core\Version\Drivers\PHP71;

use VersionManager\Core\Version\BaseVersionDriver;
use VersionManager\Core\Version\Util\ConfigureHelper;

/**
 * PHP 7.1 基础版本安装驱动类
 */
class Base extends BaseVersionDriver
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'php72';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = 'PHP 7.2版本安装驱动';
    
    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 只支持PHP 7.2.x版本
        return preg_match('/^7\.2\.\d+$/', $version);
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
}
