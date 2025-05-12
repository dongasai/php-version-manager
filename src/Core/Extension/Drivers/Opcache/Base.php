<?php

namespace VersionManager\Core\Extension\Drivers\Opcache;

use VersionManager\Core\Extension\AbstractExtensionDriver;
use VersionManager\Core\Extension\ExtensionType;

/**
 * Opcache扩展基础驱动类
 */
class Base extends AbstractExtensionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct(
            'opcache',
            'OPcache Extension',
            '',
            ExtensionType::BUILTIN,
            [],
            [
                'enable' => '1',
                'memory_consumption' => '128',
                'interned_strings_buffer' => '8',
                'max_accelerated_files' => '10000',
                'revalidate_freq' => '2',
                'save_comments' => '1',
                'validate_timestamps' => '1',
            ],
            true
        );
    }
    
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
        
        // 检查PHP是否支持该扩展
        $output = [];
        exec($phpBin . ' -m | grep ' . $this->getName(), $output);
        
        if (empty($output)) {
            // 如果不支持，则安装依赖并重新编译PHP
            throw new \Exception("当前 PHP 版本不支持 OPcache 扩展，需要重新编译 PHP");
        }
        
        // 启用扩展
        $config = isset($options['config']) ? $options['config'] : $this->getDefaultConfig();
        return $this->enable($phpVersion, $config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function enable($phpVersion, array $config = [])
    {
        // 检查扩展是否已安装
        if (!$this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 未安装");
        }
        
        // 合并默认配置和用户配置
        $mergedConfig = array_merge($this->getDefaultConfig(), $config);
        
        // 获取PHP配置对象
        $phpConfig = $this->getConfig($phpVersion);
        
        // 启用Zend扩展
        $phpConfig->enableZendExtension($this->getName());
        
        // 写入扩展配置
        foreach ($mergedConfig as $key => $value) {
            $phpConfig->setExtensionConfig($this->getName(), $key, $value);
        }
        
        return true;
    }
    
    /**
     * 获取操作系统信息
     *
     * @return array [type => 类型, version => 版本]
     */
    protected function getOsInfo()
    {
        $type = '';
        $version = '';
        
        // 读取/etc/os-release文件
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');
            
            if (isset($osRelease['ID'])) {
                $type = strtolower($osRelease['ID']);
            }
            
            if (isset($osRelease['VERSION_ID'])) {
                $version = $osRelease['VERSION_ID'];
            }
        }
        
        return [
            'type' => $type,
            'version' => $version,
        ];
    }
}
