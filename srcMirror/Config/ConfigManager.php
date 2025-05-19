<?php

namespace Mirror\Config;

/**
 * 配置管理类
 * 
 * 用于管理镜像应用的所有配置
 */
class ConfigManager
{
    /**
     * 镜像内容配置
     *
     * @var array
     */
    private $mirrorConfig;
    
    /**
     * 运行时配置
     *
     * @var array
     */
    private $runtimeConfig;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->loadConfigs();
    }
    
    /**
     * 加载所有配置
     */
    private function loadConfigs()
    {
        $this->loadMirrorConfig();
        $this->loadRuntimeConfig();
    }
    
    /**
     * 加载镜像内容配置
     */
    private function loadMirrorConfig()
    {
        $configFile = ROOT_DIR . '/config/mirror.php';
        
        if (!file_exists($configFile)) {
            throw new \Exception("镜像配置文件不存在: $configFile");
        }
        
        $this->mirrorConfig = require $configFile;
    }
    
    /**
     * 加载运行时配置
     */
    private function loadRuntimeConfig()
    {
        $configFile = ROOT_DIR . '/config/runtime.php';
        
        if (!file_exists($configFile)) {
            throw new \Exception("运行时配置文件不存在: $configFile");
        }
        
        $this->runtimeConfig = require $configFile;
    }
    
    /**
     * 获取数据目录
     * 
     * @return string
     */
    public function getDataDir()
    {
        $dataDir = $this->runtimeConfig['data_dir'] ?? '';
        
        if (empty($dataDir)) {
            $dataDir = ROOT_DIR . '/data';
        }
        
        return $dataDir;
    }
    
    /**
     * 获取日志目录
     * 
     * @return string
     */
    public function getLogDir()
    {
        $logDir = $this->runtimeConfig['log_dir'] ?? '';
        
        if (empty($logDir)) {
            $logDir = ROOT_DIR . '/logs';
        }
        
        return $logDir;
    }
    
    /**
     * 获取日志级别
     * 
     * @return string
     */
    public function getLogLevel()
    {
        return $this->runtimeConfig['log_level'] ?? 'info';
    }
    
    /**
     * 获取服务器配置
     * 
     * @return array
     */
    public function getServerConfig()
    {
        return $this->runtimeConfig['server'] ?? [
            'host' => '0.0.0.0',
            'port' => 8080,
            'public_url' => 'http://localhost:8080',
        ];
    }
    
    /**
     * 获取同步配置
     * 
     * @return array
     */
    public function getSyncConfig()
    {
        return $this->runtimeConfig['sync'] ?? [
            'interval' => 24,
            'max_retries' => 3,
            'retry_interval' => 300,
        ];
    }
    
    /**
     * 获取清理配置
     * 
     * @return array
     */
    public function getCleanupConfig()
    {
        return $this->runtimeConfig['cleanup'] ?? [
            'keep_versions' => 5,
            'min_age' => 30,
        ];
    }
    
    /**
     * 获取缓存配置
     * 
     * @return array
     */
    public function getCacheConfig()
    {
        return $this->runtimeConfig['cache'] ?? [
            'enabled' => true,
            'driver' => 'file',
            'ttl' => 3600,
        ];
    }
    
    /**
     * 获取安全配置
     * 
     * @return array
     */
    public function getSecurityConfig()
    {
        return $this->runtimeConfig['security'] ?? [
            'enable_access_control' => false,
            'allowed_ips' => [],
        ];
    }
    
    /**
     * 获取PHP源码配置
     * 
     * @return array
     */
    public function getPhpConfig()
    {
        return $this->mirrorConfig['php'] ?? [];
    }
    
    /**
     * 获取PECL扩展配置
     * 
     * @return array
     */
    public function getPeclConfig()
    {
        return $this->mirrorConfig['pecl'] ?? [];
    }
    
    /**
     * 获取特定扩展配置
     * 
     * @return array
     */
    public function getExtensionsConfig()
    {
        return $this->mirrorConfig['extensions'] ?? [];
    }
    
    /**
     * 获取Composer配置
     * 
     * @return array
     */
    public function getComposerConfig()
    {
        return $this->mirrorConfig['composer'] ?? [];
    }
    
    /**
     * 获取完整的镜像配置
     * 
     * @return array
     */
    public function getMirrorConfig()
    {
        return $this->mirrorConfig;
    }
    
    /**
     * 获取完整的运行时配置
     * 
     * @return array
     */
    public function getRuntimeConfig()
    {
        return $this->runtimeConfig;
    }
    
    /**
     * 获取所有配置
     * 
     * @return array
     */
    public function getAllConfig()
    {
        return [
            'mirror' => $this->mirrorConfig,
            'runtime' => $this->runtimeConfig,
        ];
    }
    
    /**
     * 保存镜像配置
     * 
     * @param array $config 配置数组
     * @return bool
     */
    public function saveMirrorConfig(array $config)
    {
        $this->mirrorConfig = $config;
        
        $content = "<?php\n\n/**\n * PVM 镜像内容配置文件\n * \n * 用于配置需要镜像的内容，包括PHP版本、扩展等\n */\n\nreturn " . var_export($config, true) . ";\n";
        
        $configFile = ROOT_DIR . '/config/mirror.php';
        
        return file_put_contents($configFile, $content) !== false;
    }
    
    /**
     * 保存运行时配置
     * 
     * @param array $config 配置数组
     * @return bool
     */
    public function saveRuntimeConfig(array $config)
    {
        $this->runtimeConfig = $config;
        
        $content = "<?php\n\n/**\n * PVM 镜像运行时配置文件\n * \n * 用于配置镜像服务的运行环境和行为\n */\n\nreturn " . var_export($config, true) . ";\n";
        
        $configFile = ROOT_DIR . '/config/runtime.php';
        
        return file_put_contents($configFile, $content) !== false;
    }
}
