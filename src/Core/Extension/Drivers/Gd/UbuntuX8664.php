<?php

namespace VersionManager\Core\Extension\Drivers\Gd;

/**
 * Ubuntu系统上x86_64架构的GD扩展驱动类
 */
class UbuntuX8664 extends Ubuntu
{
    /**
     * {@inheritdoc}
     */
    public function install($phpVersion, array $options = [])
    {
        // 检查扩展是否已安装
        if ($this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 已经安装");
        }
        
        // 获取PHP二进制文件路径
        $phpBin = $this->getPhpBinary($phpVersion);
        
        // 检查PHP是否支持GD扩展
        $output = [];
        exec($phpBin . ' -m | grep gd', $output);
        
        if (empty($output)) {
            // 如果不支持，则安装依赖并重新编译PHP
            $this->installDebianDependencies();
            throw new \Exception("当前 PHP 版本不支持 GD 扩展，需要重新编译 PHP");
        }
        
        // 启用扩展
        $config = isset($options['config']) ? $options['config'] : $this->getDefaultConfig();
        
        // 为x86_64架构添加特定配置
        $config['jpeg_ignore_warning'] = '1';
        $config['enable_gd_jis_conv'] = '1';
        
        return $this->enable($phpVersion, $config);
    }
}
