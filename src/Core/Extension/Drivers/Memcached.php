<?php

namespace VersionManager\Core\Extension\Drivers;

use VersionManager\Core\Extension\AbstractExtensionDriver;

/**
 * Memcached扩展驱动类
 */
class Memcached extends AbstractExtensionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct(
            'memcached',
            'Memcached Extension',
            '',
            'pecl',
            [],
            [
                'memcached.sess_prefix' => 'memc.sess.',
                'memcached.sess_lock_wait' => '150000',
                'memcached.sess_lock_max_wait' => '0',
                'memcached.sess_lock_expire' => '0',
                'memcached.sess_consistent_hash' => '1',
                'memcached.sess_binary' => '1',
                'memcached.sess_persistent' => '0',
                'memcached.sess_number_of_replicas' => '0',
                'memcached.sess_randomize_replica_read' => '0',
                'memcached.sess_remove_failed' => '0',
                'memcached.sess_connect_timeout' => '1000',
                'memcached.sess_sasl_username' => '',
                'memcached.sess_sasl_password' => '',
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
        
        // 安装依赖
        $this->installDependencies();
        
        // 从PECL安装Memcached扩展
        $this->installFromPecl($phpVersion, $options);
        
        // 启用扩展
        $config = isset($options['config']) ? $options['config'] : $this->getDefaultConfig();
        return $this->enable($phpVersion, $config);
    }
    
    /**
     * 从PECL安装Memcached扩展
     *
     * @param string $phpVersion PHP版本
     * @param array $options 安装选项
     * @return bool
     * @throws \Exception
     */
    protected function installFromPecl($phpVersion, array $options = [])
    {
        // 获取PHP配置工具
        $phpConfig = dirname($this->getPhpBinary($phpVersion)) . '/php-config';
        if (!file_exists($phpConfig)) {
            $phpConfig = 'php-config';
        }
        
        // 构建pecl命令
        $command = 'pecl install';
        
        // 添加版本限制
        if (isset($options['version'])) {
            $command .= ' memcached-' . $options['version'];
        } else {
            $command .= ' memcached';
        }
        
        // 添加其他选项
        if (isset($options['force']) && $options['force']) {
            $command .= ' --force';
        }
        
        // 执行安装命令
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装扩展 {$this->getName()} 失败: " . implode("\n", $output));
        }
        
        return true;
    }
    
    /**
     * 安装Memcached依赖
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
            . 'libmemcached-dev '
            . 'zlib1g-dev '
            . 'memcached';
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 Memcached 依赖失败: " . implode("\n", $output));
        }
    }
    
    /**
     * 安装RHEL/CentOS/Fedora依赖
     */
    protected function installRhelDependencies()
    {
        $command = 'yum install -y '
            . 'libmemcached-devel '
            . 'zlib-devel '
            . 'memcached';
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 Memcached 依赖失败: " . implode("\n", $output));
        }
    }
    
    /**
     * 安装Alpine依赖
     */
    protected function installAlpineDependencies()
    {
        $command = 'apk add --no-cache '
            . 'libmemcached-dev '
            . 'zlib-dev '
            . 'memcached';
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 Memcached 依赖失败: " . implode("\n", $output));
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
        
        // 删除扩展配置
        $config = $this->getConfig($phpVersion);
        $config->removeExtensionConfig($this->getName());
        
        // 使用pecl卸载扩展
        $command = "pecl uninstall {$this->getName()}";
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("删除扩展 {$this->getName()} 失败: " . implode("\n", $output));
        }
        
        return true;
    }
}
