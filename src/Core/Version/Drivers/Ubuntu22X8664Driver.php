<?php

namespace VersionManager\Core\Version\Drivers;

/**
 * Ubuntu 22.04 x86_64版本安装驱动类
 */
class Ubuntu22X8664Driver extends Ubuntu22Driver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'ubuntu22_x86_64';
        $this->description = 'Ubuntu 22.04 x86_64版本安装驱动';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);
        
        // 添加Ubuntu 22.04 x86_64特定的配置选项
        $ubuntu22X8664Options = [
            '--enable-jit',
            '--with-pdo-pgsql',
        ];
        
        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $ubuntu22X8664Options);
        
        return $configureOptions;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function installDependencies()
    {
        // 调用父类的安装依赖方法
        parent::installDependencies();
        
        // 安装Ubuntu 22.04 x86_64特定的依赖
        $command = 'apt-get install -y '
            . 'libpq-dev';
        
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装Ubuntu 22.04 x86_64特定依赖失败: " . implode("\n", $output));
        }
        
        return true;
    }
}
