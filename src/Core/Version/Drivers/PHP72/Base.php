<?php

namespace VersionManager\Core\Version\Drivers\PHP71;

use VersionManager\Core\Version\GenericVersionDriver;

/**
 * PHP 7.1 基础版本安装驱动类
 */
class Base extends GenericVersionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'php71_base';
        $this->description = 'PHP 7.1 基础版本安装驱动';
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
}
