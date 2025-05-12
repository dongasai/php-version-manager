<?php

namespace VersionManager\Core\Extension\Drivers\Xdebug;

use VersionManager\Core\Extension\AbstractExtensionDriver;
use VersionManager\Core\Extension\ExtensionType;

/**
 * Xdebug扩展基础驱动类
 */
class Base extends AbstractExtensionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct(
            'xdebug',
            'Xdebug Extension',
            '',
            ExtensionType::PECL,
            [],
            [
                'mode' => 'develop',
                'client_host' => 'localhost',
                'client_port' => '9003',
                'idekey' => 'PHPSTORM',
                'start_with_request' => 'yes',
                'discover_client_host' => 'yes',
                'max_nesting_level' => '256',
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
            // 如果不支持，则安装依赖并安装扩展
            $this->installDependencies();
            return $this->installPeclExtension($phpVersion, $options);
        }
        
        // 启用扩展
        $config = isset($options['config']) ? $options['config'] : $this->getDefaultConfig();
        return $this->enable($phpVersion, $config);
    }
    
    /**
     * 安装扩展依赖
     */
    protected function installDependencies()
    {
        // Xdebug通常不需要额外的依赖
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
