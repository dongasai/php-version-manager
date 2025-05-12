<?php

namespace VersionManager\Core\Extension;

/**
 * 扩展配置类
 * 
 * 用于管理PHP扩展的配置
 */
class ExtensionConfig
{
    /**
     * PHP版本
     *
     * @var string
     */
    private $phpVersion;
    
    /**
     * 配置目录
     *
     * @var string
     */
    private $configDir;
    
    /**
     * 扩展配置目录
     *
     * @var string
     */
    private $extensionConfigDir;
    
    /**
     * PHP配置文件
     *
     * @var string
     */
    private $phpIni;
    
    /**
     * 构造函数
     *
     * @param string $phpVersion PHP版本
     */
    public function __construct($phpVersion)
    {
        $this->phpVersion = $phpVersion;
        
        // 设置配置目录
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $phpVersion;
        $this->configDir = $versionDir . '/etc';
        $this->extensionConfigDir = $this->configDir . '/conf.d';
        $this->phpIni = $this->configDir . '/php.ini';
        
        // 确保目录存在
        if (!is_dir($this->configDir)) {
            mkdir($this->configDir, 0755, true);
        }
        
        if (!is_dir($this->extensionConfigDir)) {
            mkdir($this->extensionConfigDir, 0755, true);
        }
        
        // 确保php.ini文件存在
        if (!file_exists($this->phpIni)) {
            // 复制php.ini-development到php.ini
            $phpIniDev = $versionDir . '/lib/php.ini-development';
            if (file_exists($phpIniDev)) {
                copy($phpIniDev, $this->phpIni);
            } else {
                // 如果没有找到php.ini-development，则创建一个空的php.ini
                file_put_contents($this->phpIni, "; PHP Configuration File\n");
            }
            
            // 设置扩展配置目录
            $this->setPhpIniValue('scan_dir', $this->extensionConfigDir);
        }
    }
    
    /**
     * 获取已配置的扩展列表
     *
     * @return array
     */
    public function getConfiguredExtensions()
    {
        $extensions = [];
        
        // 读取扩展配置目录中的所有配置文件
        $files = glob($this->extensionConfigDir . '/*.ini');
        foreach ($files as $file) {
            $extensionName = basename($file, '.ini');
            $config = $this->parseIniFile($file);
            $extensions[$extensionName] = $config;
        }
        
        return $extensions;
    }
    
    /**
     * 获取扩展配置
     *
     * @param string $extension 扩展名称
     * @return array|null
     */
    public function getExtensionConfig($extension)
    {
        $configFile = $this->extensionConfigDir . '/' . $extension . '.ini';
        if (file_exists($configFile)) {
            return $this->parseIniFile($configFile);
        }
        
        return null;
    }
    
    /**
     * 设置扩展配置
     *
     * @param string $extension 扩展名称
     * @param array $config 扩展配置
     * @return bool
     */
    public function setExtensionConfig($extension, array $config)
    {
        $configFile = $this->extensionConfigDir . '/' . $extension . '.ini';
        
        // 构建配置内容
        $content = "; {$extension} Extension Configuration\n";
        
        // 添加扩展加载配置
        if (isset($config['zend_extension']) && $config['zend_extension']) {
            $content .= "zend_extension={$extension}\n";
            unset($config['zend_extension']);
        } else {
            $content .= "extension={$extension}\n";
        }
        
        // 添加其他配置项
        foreach ($config as $key => $value) {
            $content .= "{$extension}.{$key}={$value}\n";
        }
        
        // 写入配置文件
        return file_put_contents($configFile, $content) !== false;
    }
    
    /**
     * 删除扩展配置
     *
     * @param string $extension 扩展名称
     * @return bool
     */
    public function removeExtensionConfig($extension)
    {
        $configFile = $this->extensionConfigDir . '/' . $extension . '.ini';
        if (file_exists($configFile)) {
            return unlink($configFile);
        }
        
        return true;
    }
    
    /**
     * 设置PHP配置值
     *
     * @param string $key 配置键
     * @param string $value 配置值
     * @return bool
     */
    public function setPhpIniValue($key, $value)
    {
        // 读取php.ini文件
        $content = file_exists($this->phpIni) ? file_get_contents($this->phpIni) : '';
        
        // 检查配置是否已存在
        $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=.*$/m';
        if (preg_match($pattern, $content)) {
            // 更新配置
            $content = preg_replace($pattern, "{$key} = \"{$value}\"", $content);
        } else {
            // 添加配置
            $content .= "\n{$key} = \"{$value}\"\n";
        }
        
        // 写入php.ini文件
        return file_put_contents($this->phpIni, $content) !== false;
    }
    
    /**
     * 获取PHP配置值
     *
     * @param string $key 配置键
     * @return string|null
     */
    public function getPhpIniValue($key)
    {
        // 读取php.ini文件
        $content = file_exists($this->phpIni) ? file_get_contents($this->phpIni) : '';
        
        // 查找配置
        $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=\s*"?([^"]*)"?/m';
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * 解析INI文件
     *
     * @param string $file 文件路径
     * @return array
     */
    private function parseIniFile($file)
    {
        $config = [];
        
        // 读取文件内容
        $content = file_get_contents($file);
        
        // 解析每一行
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 跳过注释和空行
            if (empty($line) || $line[0] === ';') {
                continue;
            }
            
            // 解析配置项
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // 处理扩展加载配置
                if ($key === 'extension') {
                    $config['extension'] = $value;
                } elseif ($key === 'zend_extension') {
                    $config['zend_extension'] = true;
                    $config['extension'] = $value;
                } else {
                    // 处理扩展特定配置
                    $parts = explode('.', $key, 2);
                    if (count($parts) === 2) {
                        $extension = $parts[0];
                        $configKey = $parts[1];
                        $config[$configKey] = $value;
                    } else {
                        $config[$key] = $value;
                    }
                }
            }
        }
        
        return $config;
    }
}
