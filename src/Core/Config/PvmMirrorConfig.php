<?php

namespace VersionManager\Core\Config;

/**
 * PVM镜像源配置类
 * 
 * 统一管理PVM自建镜像源配置，简化镜像管理
 */
class PvmMirrorConfig
{
    /**
     * 配置文件路径
     *
     * @var string
     */
    private $configFile;
    
    /**
     * 镜像配置
     *
     * @var array
     */
    private $config;
    
    /**
     * 默认镜像配置
     *
     * @var array
     */
    private $defaultConfig = [
        // 是否启用PVM镜像源
        'enabled' => false,
        
        // PVM镜像源地址
        'mirror_url' => 'https://pvm.2sxo.com',
        
        // 备用镜像源地址
        'fallback_mirrors' => [
            'http://localhost:34403',  // 本地镜像
        ],
        
        // 连接超时时间（秒）
        'timeout' => 30,
        
        // 是否使用HTTPS验证
        'verify_ssl' => true,
        
        // 自动回退到官方源
        'auto_fallback_to_official' => true,
    ];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->configFile = getenv('HOME') . '/.pvm/config/pvm-mirror.php';
        $this->loadConfig();
    }
    
    /**
     * 加载配置
     */
    private function loadConfig()
    {
        // 如果配置文件存在，则加载配置
        if (file_exists($this->configFile)) {
            $this->config = require $this->configFile;
        } else {
            // 否则使用默认配置
            $this->config = $this->defaultConfig;
            
            // 确保配置目录存在
            $configDir = dirname($this->configFile);
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }
            
            // 保存默认配置
            $this->saveConfig();
        }
    }
    
    /**
     * 保存配置
     *
     * @return bool
     */
    public function saveConfig()
    {
        $content = "<?php\n\n// PVM镜像源配置文件\n// 由 PVM 自动生成，可以手动修改\n\nreturn " . var_export($this->config, true) . ";\n";
        return file_put_contents($this->configFile, $content) !== false;
    }
    
    /**
     * 是否启用PVM镜像源
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config['enabled'] ?? false;
    }
    
    /**
     * 启用PVM镜像源
     *
     * @return bool
     */
    public function enable()
    {
        $this->config['enabled'] = true;
        return $this->saveConfig();
    }
    
    /**
     * 禁用PVM镜像源
     *
     * @return bool
     */
    public function disable()
    {
        $this->config['enabled'] = false;
        return $this->saveConfig();
    }
    
    /**
     * 获取主镜像源地址
     *
     * @return string
     */
    public function getMirrorUrl()
    {
        return $this->config['mirror_url'] ?? $this->defaultConfig['mirror_url'];
    }
    
    /**
     * 设置主镜像源地址
     *
     * @param string $url 镜像源地址
     * @return bool
     */
    public function setMirrorUrl($url)
    {
        // 验证URL格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $this->config['mirror_url'] = rtrim($url, '/');
        return $this->saveConfig();
    }
    
    /**
     * 获取备用镜像源地址列表
     *
     * @return array
     */
    public function getFallbackMirrors()
    {
        return $this->config['fallback_mirrors'] ?? $this->defaultConfig['fallback_mirrors'];
    }
    
    /**
     * 添加备用镜像源
     *
     * @param string $url 镜像源地址
     * @return bool
     */
    public function addFallbackMirror($url)
    {
        // 验证URL格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $url = rtrim($url, '/');
        $fallbacks = $this->getFallbackMirrors();
        
        if (!in_array($url, $fallbacks)) {
            $fallbacks[] = $url;
            $this->config['fallback_mirrors'] = $fallbacks;
            return $this->saveConfig();
        }
        
        return true;
    }
    
    /**
     * 移除备用镜像源
     *
     * @param string $url 镜像源地址
     * @return bool
     */
    public function removeFallbackMirror($url)
    {
        $url = rtrim($url, '/');
        $fallbacks = $this->getFallbackMirrors();
        
        $key = array_search($url, $fallbacks);
        if ($key !== false) {
            unset($fallbacks[$key]);
            $this->config['fallback_mirrors'] = array_values($fallbacks);
            return $this->saveConfig();
        }
        
        return false;
    }
    
    /**
     * 获取所有可用的镜像源（主镜像+备用镜像）
     *
     * @return array
     */
    public function getAllMirrors()
    {
        $mirrors = [$this->getMirrorUrl()];
        return array_merge($mirrors, $this->getFallbackMirrors());
    }
    
    /**
     * 获取下载URL
     *
     * @param string $type 下载类型 (php|pecl|composer|extension)
     * @param string $filename 文件名
     * @param string $extension 扩展名（仅当type为extension时需要）
     * @return array 可用的下载URL列表
     */
    public function getDownloadUrls($type, $filename, $extension = null)
    {
        $urls = [];
        
        if ($this->isEnabled()) {
            // 构建PVM镜像源URL
            foreach ($this->getAllMirrors() as $mirror) {
                switch ($type) {
                    case 'php':
                        $urls[] = $mirror . '/php/' . $filename;
                        break;
                    case 'pecl':
                        $urls[] = $mirror . '/pecl/' . $filename;
                        break;
                    case 'composer':
                        $urls[] = $mirror . '/composer/' . $filename;
                        break;
                    case 'extension':
                        if ($extension) {
                            $urls[] = $mirror . '/extensions/' . $extension . '/' . $filename;
                        }
                        break;
                }
            }
        }
        
        // 如果启用了自动回退到官方源，添加官方源URL
        if ($this->config['auto_fallback_to_official'] ?? true) {
            $urls = array_merge($urls, $this->getOfficialUrls($type, $filename, $extension));
        }
        
        return $urls;
    }
    
    /**
     * 获取官方源URL
     *
     * @param string $type 下载类型
     * @param string $filename 文件名
     * @param string $extension 扩展名
     * @return array
     */
    private function getOfficialUrls($type, $filename, $extension = null)
    {
        switch ($type) {
            case 'php':
                return ['https://www.php.net/distributions/' . $filename];
            case 'pecl':
                return ['https://pecl.php.net/get/' . $filename];
            case 'composer':
                return ['https://getcomposer.org/download/' . $filename];
            case 'extension':
                // 对于扩展，返回PECL源
                return ['https://pecl.php.net/get/' . $filename];
            default:
                return [];
        }
    }
    
    /**
     * 获取连接超时时间
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->config['timeout'] ?? $this->defaultConfig['timeout'];
    }
    
    /**
     * 设置连接超时时间
     *
     * @param int $timeout 超时时间（秒）
     * @return bool
     */
    public function setTimeout($timeout)
    {
        if ($timeout > 0) {
            $this->config['timeout'] = $timeout;
            return $this->saveConfig();
        }
        
        return false;
    }
    
    /**
     * 是否验证SSL
     *
     * @return bool
     */
    public function isVerifySsl()
    {
        return $this->config['verify_ssl'] ?? $this->defaultConfig['verify_ssl'];
    }
    
    /**
     * 设置SSL验证
     *
     * @param bool $verify 是否验证SSL
     * @return bool
     */
    public function setVerifySsl($verify)
    {
        $this->config['verify_ssl'] = (bool)$verify;
        return $this->saveConfig();
    }
    
    /**
     * 测试镜像源连接
     *
     * @param string $url 镜像源地址
     * @return array 测试结果
     */
    public function testMirror($url = null)
    {
        if ($url === null) {
            $url = $this->getMirrorUrl();
        }
        
        $startTime = microtime(true);
        
        // 测试连接
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->isVerifySsl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2); // 毫秒
        
        return [
            'success' => $result !== false && $httpCode >= 200 && $httpCode < 400,
            'http_code' => $httpCode,
            'response_time' => $responseTime,
            'error' => $error,
            'url' => $url,
        ];
    }
    
    /**
     * 获取配置摘要
     *
     * @return array
     */
    public function getConfigSummary()
    {
        return [
            'enabled' => $this->isEnabled(),
            'mirror_url' => $this->getMirrorUrl(),
            'fallback_count' => count($this->getFallbackMirrors()),
            'timeout' => $this->getTimeout(),
            'verify_ssl' => $this->isVerifySsl(),
            'auto_fallback' => $this->config['auto_fallback_to_official'] ?? true,
        ];
    }
}
