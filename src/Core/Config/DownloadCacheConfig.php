<?php

namespace VersionManager\Core\Config;

/**
 * 下载缓存配置管理类
 * 
 * 负责管理下载缓存的配置设置
 */
class DownloadCacheConfig
{
    /**
     * 配置数据
     *
     * @var array
     */
    private $config;

    /**
     * 配置文件路径
     *
     * @var string
     */
    private $configFile;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->configFile = $this->getConfigFilePath();
        $this->loadConfig();
    }

    /**
     * 获取配置文件路径
     *
     * @return string
     */
    private function getConfigFilePath()
    {
        // 优先使用项目根目录下的配置文件
        $projectRoot = dirname(dirname(dirname(__DIR__)));
        $configFile = $projectRoot . '/config/download_cache.php';
        
        if (file_exists($configFile)) {
            return $configFile;
        }
        
        // 如果项目配置不存在，使用默认配置
        return __DIR__ . '/../../../config/download_cache.php';
    }

    /**
     * 加载配置
     */
    private function loadConfig()
    {
        if (file_exists($this->configFile)) {
            $this->config = require $this->configFile;
        } else {
            // 使用默认配置
            $this->config = $this->getDefaultConfig();
        }
    }

    /**
     * 获取默认配置
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        return [
            'enabled' => true,
            'cache_expire' => 86400 * 7,
            'max_cache_size' => 1024 * 1024 * 1024,
            'auto_cleanup' => true,
            'integrity_check' => [
                'enabled' => true,
                'algorithms' => ['md5', 'sha256'],
                'verify_on_cache_hit' => true,
                'verify_on_cache_set' => true,
            ],
            'logging' => [
                'enabled' => true,
                'log_cache_hits' => true,
                'log_cache_misses' => true,
                'log_cache_operations' => true,
                'log_integrity_checks' => true,
                'log_cleanup_operations' => true,
            ],
        ];
    }

    /**
     * 检查缓存是否启用
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->get('enabled', true);
    }

    /**
     * 获取缓存过期时间
     *
     * @return int
     */
    public function getCacheExpire()
    {
        return $this->get('cache_expire', 86400 * 7);
    }

    /**
     * 获取最大缓存大小
     *
     * @return int
     */
    public function getMaxCacheSize()
    {
        return $this->get('max_cache_size', 1024 * 1024 * 1024);
    }

    /**
     * 检查是否启用自动清理
     *
     * @return bool
     */
    public function isAutoCleanupEnabled()
    {
        return $this->get('auto_cleanup', true);
    }

    /**
     * 检查是否启用完整性校验
     *
     * @return bool
     */
    public function isIntegrityCheckEnabled()
    {
        return $this->get('integrity_check.enabled', true);
    }

    /**
     * 获取默认校验算法
     *
     * @return array
     */
    public function getDefaultAlgorithms()
    {
        return $this->get('integrity_check.algorithms', ['md5', 'sha256']);
    }

    /**
     * 检查缓存命中时是否验证完整性
     *
     * @return bool
     */
    public function shouldVerifyOnCacheHit()
    {
        return $this->get('integrity_check.verify_on_cache_hit', true);
    }

    /**
     * 检查设置缓存时是否生成校验和
     *
     * @return bool
     */
    public function shouldVerifyOnCacheSet()
    {
        return $this->get('integrity_check.verify_on_cache_set', true);
    }

    /**
     * 检查是否启用日志记录
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->get('logging.enabled', true);
    }

    /**
     * 检查是否记录缓存命中
     *
     * @return bool
     */
    public function shouldLogCacheHits()
    {
        return $this->get('logging.log_cache_hits', true);
    }

    /**
     * 检查是否记录缓存未命中
     *
     * @return bool
     */
    public function shouldLogCacheMisses()
    {
        return $this->get('logging.log_cache_misses', true);
    }

    /**
     * 检查是否记录缓存操作
     *
     * @return bool
     */
    public function shouldLogCacheOperations()
    {
        return $this->get('logging.log_cache_operations', true);
    }

    /**
     * 检查是否记录完整性检查
     *
     * @return bool
     */
    public function shouldLogIntegrityChecks()
    {
        return $this->get('logging.log_integrity_checks', true);
    }

    /**
     * 获取文件类型特定配置
     *
     * @param string $fileType 文件类型
     * @return array
     */
    public function getFileTypeConfig($fileType)
    {
        return $this->get("file_types.{$fileType}", []);
    }

    /**
     * 获取配置值
     *
     * @param string $key 配置键，支持点号分隔的嵌套键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * 设置配置值
     *
     * @param string $key 配置键
     * @param mixed $value 配置值
     */
    public function set($key, $value)
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * 获取所有配置
     *
     * @return array
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * 保存配置到文件
     *
     * @return bool
     */
    public function save()
    {
        $configContent = "<?php\n\nreturn " . var_export($this->config, true) . ";\n";
        return file_put_contents($this->configFile, $configContent) !== false;
    }
}
