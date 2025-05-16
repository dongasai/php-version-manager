<?php

namespace Mirror\Config;

/**
 * 镜像配置管理类
 */
class MirrorConfig
{
    /**
     * 配置数据
     *
     * @var array
     */
    private $config;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig()
    {
        $configFile = ROOT_DIR . '/config/mirror.php';
        
        if (!file_exists($configFile)) {
            throw new \Exception("配置文件不存在: $configFile");
        }
        
        $this->config = require $configFile;
    }

    /**
     * 获取PHP源码配置
     *
     * @return array
     */
    public function getPhpConfig()
    {
        return $this->config['php'] ?? [];
    }

    /**
     * 获取PECL扩展配置
     *
     * @return array
     */
    public function getPeclConfig()
    {
        return $this->config['pecl'] ?? [];
    }

    /**
     * 获取特定扩展配置
     *
     * @return array
     */
    public function getExtensionsConfig()
    {
        return $this->config['extensions'] ?? [];
    }

    /**
     * 获取Composer配置
     *
     * @return array
     */
    public function getComposerConfig()
    {
        return $this->config['composer'] ?? [];
    }

    /**
     * 获取服务器配置
     *
     * @return array
     */
    public function getServerConfig()
    {
        return $this->config['server'] ?? [
            'host' => '0.0.0.0',
            'port' => 8080,
            'public_url' => 'http://localhost:8080',
        ];
    }

    /**
     * 获取清理配置
     *
     * @return array
     */
    public function getCleanupConfig()
    {
        return $this->config['cleanup'] ?? [
            'keep_versions' => 5,
            'min_age' => 30,
        ];
    }

    /**
     * 获取完整配置
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
