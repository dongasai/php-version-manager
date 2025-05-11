<?php

namespace VersionManager\Core\Extension\Drivers;

use VersionManager\Core\Extension\AbstractExtensionDriver;

/**
 * MySQLi扩展驱动类
 */
class Mysqli extends AbstractExtensionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct(
            'mysqli',
            'MySQL Improved Extension',
            '',
            'builtin',
            [],
            [
                'default_socket' => '/tmp/mysql.sock',
                'default_host' => 'localhost',
                'default_user' => 'root',
                'default_pw' => '',
                'default_port' => '3306',
                'default_charset' => 'utf8mb4',
            ],
            false
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
        
        // 检查PHP是否支持MySQLi扩展
        $output = [];
        exec($phpBin . ' -m | grep mysqli', $output);
        
        if (empty($output)) {
            // 如果不支持，则安装依赖并重新编译PHP
            $this->installDependencies();
            throw new \Exception("当前 PHP 版本不支持 MySQLi 扩展，需要重新编译 PHP");
        }
        
        // 启用扩展
        $config = isset($options['config']) ? $options['config'] : $this->getDefaultConfig();
        return $this->enable($phpVersion, $config);
    }
    
    /**
     * 安装MySQLi依赖
     */
    protected function installDependencies()
    {
        // 检测操作系统类型
        $osInfo = $this->getOsInfo();
        
        switch ($osInfo['type']) {
            case 'debian':
            case 'ubuntu':
                $this->installDebianDependencies();
                break;
            case 'centos':
            case 'fedora':
            case 'rhel':
                $this->installRhelDependencies();
                break;
            case 'alpine':
                $this->installAlpineDependencies();
                break;
            default:
                throw new \Exception("不支持的操作系统类型: {$osInfo['type']}");
        }
    }
    
    /**
     * 安装Debian/Ubuntu依赖
     */
    protected function installDebianDependencies()
    {
        $command = 'apt-get update && apt-get install -y '
            . 'libmysqlclient-dev '
            . 'mysql-client';
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 MySQLi 依赖失败: " . implode("\n", $output));
        }
    }
    
    /**
     * 安装RHEL/CentOS/Fedora依赖
     */
    protected function installRhelDependencies()
    {
        $command = 'yum install -y '
            . 'mysql-devel '
            . 'mysql-libs '
            . 'mysql';
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 MySQLi 依赖失败: " . implode("\n", $output));
        }
    }
    
    /**
     * 安装Alpine依赖
     */
    protected function installAlpineDependencies()
    {
        $command = 'apk add --no-cache '
            . 'mysql-client '
            . 'mysql-dev';
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 MySQLi 依赖失败: " . implode("\n", $output));
        }
    }
    
    /**
     * 获取操作系统信息
     *
     * @return array [type => 类型, version => 版本]
     */
    private function getOsInfo()
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
    
    /**
     * {@inheritdoc}
     */
    public function remove($phpVersion, array $options = [])
    {
        // 检查扩展是否已安装
        if (!$this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 未安装");
        }
        
        // 如果是内置扩展，则只能禁用而不能删除
        if ($this->getType() === 'builtin') {
            if (isset($options['disable']) && $options['disable']) {
                return $this->disable($phpVersion);
            } else {
                throw new \Exception("无法删除内置扩展 {$this->getName()}，只能禁用");
            }
        }
        
        // 删除扩展配置
        $config = $this->getConfig($phpVersion);
        $config->removeExtensionConfig($this->getName());
        
        return true;
    }
}
