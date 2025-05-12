<?php

namespace VersionManager\Core\Version\Drivers\PHP71;

/**
 * PHP 7.1 Ubuntu x86_64版本安装驱动类
 */
class Ubuntu_X8664 extends Ubuntu
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'php71_ubuntu_x8664';
        $this->description = 'PHP 7.1 Ubuntu x86_64版本安装驱动';
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
            '--enable-inline-optimization',
        ];
        
        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $x8664Options);
        
        return $configureOptions;
    }
}
