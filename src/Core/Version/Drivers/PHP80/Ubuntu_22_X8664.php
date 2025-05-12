<?php

namespace VersionManager\Core\Version\Drivers\PHP80;

/**
 * PHP 8.0 Ubuntu 22.04 x86_64版本安装驱动类
 */
class Ubuntu_22_X8664 extends Ubuntu_X8664
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'php80_ubuntu_22_x8664';
        $this->description = 'PHP 8.0 Ubuntu 22.04 x86_64版本安装驱动';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);
        
        // 添加Ubuntu 22.04特定的配置选项
        $ubuntu22Options = [
            '--with-ffi',
        ];
        
        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $ubuntu22Options);
        
        return $configureOptions;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function installDependencies($version)
    {
        // 调用父类的安装依赖方法
        parent::installDependencies($version);
        
        // 安装Ubuntu 22.04特定的依赖
        $command = 'apt-get install -y '
            . 'libffi-dev';
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装Ubuntu 22.04特定依赖失败: " . implode("\n", $output));
        }
        
        return true;
    }
}
