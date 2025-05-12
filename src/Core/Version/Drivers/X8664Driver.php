<?php

namespace VersionManager\Core\Version\Drivers;

use VersionManager\Core\Version\GenericVersionDriver;

/**
 * x86_64架构版本安装驱动类
 */
class X8664Driver extends GenericVersionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'x86_64';
        $this->description = 'x86_64架构版本安装驱动';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);
        
        // 添加x86_64架构特定的配置选项
        $x8664Options = [
            '--enable-jit',
        ];
        
        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $x8664Options);
        
        return $configureOptions;
    }
}
