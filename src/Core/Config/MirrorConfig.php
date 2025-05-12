<?php

namespace VersionManager\Core\Config;

/**
 * 镜像配置类
 * 
 * 用于管理PHP和扩展的下载镜像地址
 */
class MirrorConfig
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
        'php' => [
            'official' => 'https://www.php.net/distributions',
            'mirrors' => [
                'huaweicloud' => 'https://mirrors.huaweicloud.com/php',
                'aliyun' => 'https://mirrors.aliyun.com/php',
                'tencent' => 'https://mirrors.cloud.tencent.com/php',
                'ustc' => 'https://mirrors.ustc.edu.cn/php',
            ],
            'default' => 'official',
        ],
        'pecl' => [
            'official' => 'https://pecl.php.net/get',
            'mirrors' => [
                'huaweicloud' => 'https://mirrors.huaweicloud.com/pecl',
                'aliyun' => 'https://mirrors.aliyun.com/pecl',
                'tencent' => 'https://mirrors.cloud.tencent.com/pecl',
                'ustc' => 'https://mirrors.ustc.edu.cn/pecl',
            ],
            'default' => 'official',
        ],
        'extensions' => [
            'redis' => [
                'official' => 'https://pecl.php.net/get/redis',
                'mirrors' => [
                    'github' => 'https://github.com/phpredis/phpredis/archive/refs/tags',
                ],
                'default' => 'official',
            ],
            'memcached' => [
                'official' => 'https://pecl.php.net/get/memcached',
                'mirrors' => [
                    'github' => 'https://github.com/php-memcached-dev/php-memcached/archive/refs/tags',
                ],
                'default' => 'official',
            ],
            'xdebug' => [
                'official' => 'https://pecl.php.net/get/xdebug',
                'mirrors' => [
                    'github' => 'https://github.com/xdebug/xdebug/archive/refs/tags',
                ],
                'default' => 'official',
            ],
        ],
        'composer' => [
            'official' => 'https://getcomposer.org/download',
            'mirrors' => [
                'aliyun' => 'https://mirrors.aliyun.com/composer',
                'tencent' => 'https://mirrors.cloud.tencent.com/composer',
                'huaweicloud' => 'https://mirrors.huaweicloud.com/composer',
                'phpcomposer' => 'https://packagist.phpcomposer.com',
            ],
            'default' => 'official',
        ],
    ];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->configFile = getenv('HOME') . '/.pvm/config/mirrors.php';
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
        $content = "<?php\n\n// 镜像配置文件\n// 由 PVM 自动生成，可以手动修改\n\nreturn " . var_export($this->config, true) . ";\n";
        return file_put_contents($this->configFile, $content) !== false;
    }
    
    /**
     * 获取PHP镜像地址
     *
     * @param string $mirror 镜像名称，如果为null则使用默认镜像
     * @return string
     */
    public function getPhpMirror($mirror = null)
    {
        if ($mirror === null) {
            $mirror = $this->config['php']['default'];
        }
        
        if ($mirror === 'official') {
            return $this->config['php']['official'];
        }
        
        if (isset($this->config['php']['mirrors'][$mirror])) {
            return $this->config['php']['mirrors'][$mirror];
        }
        
        return $this->config['php']['official'];
    }
    
    /**
     * 获取PECL镜像地址
     *
     * @param string $mirror 镜像名称，如果为null则使用默认镜像
     * @return string
     */
    public function getPeclMirror($mirror = null)
    {
        if ($mirror === null) {
            $mirror = $this->config['pecl']['default'];
        }
        
        if ($mirror === 'official') {
            return $this->config['pecl']['official'];
        }
        
        if (isset($this->config['pecl']['mirrors'][$mirror])) {
            return $this->config['pecl']['mirrors'][$mirror];
        }
        
        return $this->config['pecl']['official'];
    }
    
    /**
     * 获取扩展镜像地址
     *
     * @param string $extension 扩展名称
     * @param string $mirror 镜像名称，如果为null则使用默认镜像
     * @return string
     */
    public function getExtensionMirror($extension, $mirror = null)
    {
        if (!isset($this->config['extensions'][$extension])) {
            return $this->getPeclMirror($mirror);
        }
        
        if ($mirror === null) {
            $mirror = $this->config['extensions'][$extension]['default'];
        }
        
        if ($mirror === 'official') {
            return $this->config['extensions'][$extension]['official'];
        }
        
        if (isset($this->config['extensions'][$extension]['mirrors'][$mirror])) {
            return $this->config['extensions'][$extension]['mirrors'][$mirror];
        }
        
        return $this->config['extensions'][$extension]['official'];
    }
    
    /**
     * 获取Composer镜像地址
     *
     * @param string $mirror 镜像名称，如果为null则使用默认镜像
     * @return string
     */
    public function getComposerMirror($mirror = null)
    {
        if ($mirror === null) {
            $mirror = $this->config['composer']['default'];
        }
        
        if ($mirror === 'official') {
            return $this->config['composer']['official'];
        }
        
        if (isset($this->config['composer']['mirrors'][$mirror])) {
            return $this->config['composer']['mirrors'][$mirror];
        }
        
        return $this->config['composer']['official'];
    }
    
    /**
     * 设置默认PHP镜像
     *
     * @param string $mirror 镜像名称
     * @return bool
     */
    public function setDefaultPhpMirror($mirror)
    {
        if ($mirror === 'official' || isset($this->config['php']['mirrors'][$mirror])) {
            $this->config['php']['default'] = $mirror;
            return $this->saveConfig();
        }
        
        return false;
    }
    
    /**
     * 设置默认PECL镜像
     *
     * @param string $mirror 镜像名称
     * @return bool
     */
    public function setDefaultPeclMirror($mirror)
    {
        if ($mirror === 'official' || isset($this->config['pecl']['mirrors'][$mirror])) {
            $this->config['pecl']['default'] = $mirror;
            return $this->saveConfig();
        }
        
        return false;
    }
    
    /**
     * 设置默认扩展镜像
     *
     * @param string $extension 扩展名称
     * @param string $mirror 镜像名称
     * @return bool
     */
    public function setDefaultExtensionMirror($extension, $mirror)
    {
        if (!isset($this->config['extensions'][$extension])) {
            return false;
        }
        
        if ($mirror === 'official' || isset($this->config['extensions'][$extension]['mirrors'][$mirror])) {
            $this->config['extensions'][$extension]['default'] = $mirror;
            return $this->saveConfig();
        }
        
        return false;
    }
    
    /**
     * 设置默认Composer镜像
     *
     * @param string $mirror 镜像名称
     * @return bool
     */
    public function setDefaultComposerMirror($mirror)
    {
        if ($mirror === 'official' || isset($this->config['composer']['mirrors'][$mirror])) {
            $this->config['composer']['default'] = $mirror;
            return $this->saveConfig();
        }
        
        return false;
    }
    
    /**
     * 添加PHP镜像
     *
     * @param string $name 镜像名称
     * @param string $url 镜像地址
     * @return bool
     */
    public function addPhpMirror($name, $url)
    {
        if ($name === 'official') {
            return false;
        }
        
        $this->config['php']['mirrors'][$name] = $url;
        return $this->saveConfig();
    }
    
    /**
     * 添加PECL镜像
     *
     * @param string $name 镜像名称
     * @param string $url 镜像地址
     * @return bool
     */
    public function addPeclMirror($name, $url)
    {
        if ($name === 'official') {
            return false;
        }
        
        $this->config['pecl']['mirrors'][$name] = $url;
        return $this->saveConfig();
    }
    
    /**
     * 添加扩展镜像
     *
     * @param string $extension 扩展名称
     * @param string $name 镜像名称
     * @param string $url 镜像地址
     * @return bool
     */
    public function addExtensionMirror($extension, $name, $url)
    {
        if ($name === 'official') {
            return false;
        }
        
        if (!isset($this->config['extensions'][$extension])) {
            $this->config['extensions'][$extension] = [
                'official' => "https://pecl.php.net/get/{$extension}",
                'mirrors' => [],
                'default' => 'official',
            ];
        }
        
        $this->config['extensions'][$extension]['mirrors'][$name] = $url;
        return $this->saveConfig();
    }
    
    /**
     * 添加Composer镜像
     *
     * @param string $name 镜像名称
     * @param string $url 镜像地址
     * @return bool
     */
    public function addComposerMirror($name, $url)
    {
        if ($name === 'official') {
            return false;
        }
        
        $this->config['composer']['mirrors'][$name] = $url;
        return $this->saveConfig();
    }
    
    /**
     * 删除PHP镜像
     *
     * @param string $name 镜像名称
     * @return bool
     */
    public function removePhpMirror($name)
    {
        if ($name === 'official' || !isset($this->config['php']['mirrors'][$name])) {
            return false;
        }
        
        unset($this->config['php']['mirrors'][$name]);
        
        // 如果删除的是默认镜像，则重置为官方镜像
        if ($this->config['php']['default'] === $name) {
            $this->config['php']['default'] = 'official';
        }
        
        return $this->saveConfig();
    }
    
    /**
     * 删除PECL镜像
     *
     * @param string $name 镜像名称
     * @return bool
     */
    public function removePeclMirror($name)
    {
        if ($name === 'official' || !isset($this->config['pecl']['mirrors'][$name])) {
            return false;
        }
        
        unset($this->config['pecl']['mirrors'][$name]);
        
        // 如果删除的是默认镜像，则重置为官方镜像
        if ($this->config['pecl']['default'] === $name) {
            $this->config['pecl']['default'] = 'official';
        }
        
        return $this->saveConfig();
    }
    
    /**
     * 删除扩展镜像
     *
     * @param string $extension 扩展名称
     * @param string $name 镜像名称
     * @return bool
     */
    public function removeExtensionMirror($extension, $name)
    {
        if ($name === 'official' || !isset($this->config['extensions'][$extension]['mirrors'][$name])) {
            return false;
        }
        
        unset($this->config['extensions'][$extension]['mirrors'][$name]);
        
        // 如果删除的是默认镜像，则重置为官方镜像
        if ($this->config['extensions'][$extension]['default'] === $name) {
            $this->config['extensions'][$extension]['default'] = 'official';
        }
        
        return $this->saveConfig();
    }
    
    /**
     * 删除Composer镜像
     *
     * @param string $name 镜像名称
     * @return bool
     */
    public function removeComposerMirror($name)
    {
        if ($name === 'official' || !isset($this->config['composer']['mirrors'][$name])) {
            return false;
        }
        
        unset($this->config['composer']['mirrors'][$name]);
        
        // 如果删除的是默认镜像，则重置为官方镜像
        if ($this->config['composer']['default'] === $name) {
            $this->config['composer']['default'] = 'official';
        }
        
        return $this->saveConfig();
    }
    
    /**
     * 获取所有PHP镜像
     *
     * @return array
     */
    public function getAllPhpMirrors()
    {
        $mirrors = ['official' => $this->config['php']['official']];
        return array_merge($mirrors, $this->config['php']['mirrors']);
    }
    
    /**
     * 获取所有PECL镜像
     *
     * @return array
     */
    public function getAllPeclMirrors()
    {
        $mirrors = ['official' => $this->config['pecl']['official']];
        return array_merge($mirrors, $this->config['pecl']['mirrors']);
    }
    
    /**
     * 获取所有扩展镜像
     *
     * @param string $extension 扩展名称
     * @return array
     */
    public function getAllExtensionMirrors($extension)
    {
        if (!isset($this->config['extensions'][$extension])) {
            return $this->getAllPeclMirrors();
        }
        
        $mirrors = ['official' => $this->config['extensions'][$extension]['official']];
        return array_merge($mirrors, $this->config['extensions'][$extension]['mirrors']);
    }
    
    /**
     * 获取所有Composer镜像
     *
     * @return array
     */
    public function getAllComposerMirrors()
    {
        $mirrors = ['official' => $this->config['composer']['official']];
        return array_merge($mirrors, $this->config['composer']['mirrors']);
    }
    
    /**
     * 获取默认PHP镜像名称
     *
     * @return string
     */
    public function getDefaultPhpMirrorName()
    {
        return $this->config['php']['default'];
    }
    
    /**
     * 获取默认PECL镜像名称
     *
     * @return string
     */
    public function getDefaultPeclMirrorName()
    {
        return $this->config['pecl']['default'];
    }
    
    /**
     * 获取默认扩展镜像名称
     *
     * @param string $extension 扩展名称
     * @return string
     */
    public function getDefaultExtensionMirrorName($extension)
    {
        if (!isset($this->config['extensions'][$extension])) {
            return $this->getDefaultPeclMirrorName();
        }
        
        return $this->config['extensions'][$extension]['default'];
    }
    
    /**
     * 获取默认Composer镜像名称
     *
     * @return string
     */
    public function getDefaultComposerMirrorName()
    {
        return $this->config['composer']['default'];
    }
}
