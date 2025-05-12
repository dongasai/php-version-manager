<?php

namespace VersionManager\Core\Version\Drivers;

/**
 * Ubuntu x86_64版本安装驱动类
 */
class UbuntuX8664Driver extends UbuntuDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'ubuntu_x86_64';
        $this->description = 'Ubuntu x86_64版本安装驱动';
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
            '--enable-gd',
            '--with-jpeg',
            '--with-webp',
            '--with-freetype',
            '--with-xpm',
            '--with-avif',
        ];
        
        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $x8664Options);
        
        return $configureOptions;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function installDependencies()
    {
        // 调用父类的安装依赖方法
        parent::installDependencies();
        
        // 安装x86_64特定的依赖
        $command = 'apt-get install -y '
            . 'libavif-dev';
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装x86_64特定依赖失败: " . implode("\n", $output));
        }
        
        return true;
    }
}
