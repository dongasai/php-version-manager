<?php

namespace VersionManager\Core\System;

/**
 * 环境变量管理类
 * 
 * 负责管理系统环境变量
 */
class EnvironmentManager
{
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmDir;
    
    /**
     * 环境变量文件路径
     *
     * @var string
     */
    private $envFile;
    
    /**
     * 当前环境变量
     *
     * @var array
     */
    private $env = [];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->pvmDir = getenv('HOME') . '/.pvm';
        $this->envFile = $this->pvmDir . '/env';
        
        // 加载环境变量
        $this->loadEnv();
    }
    
    /**
     * 加载环境变量
     */
    private function loadEnv()
    {
        // 加载系统环境变量
        $this->env = $_ENV;
        
        // 加载PVM环境变量文件
        if (file_exists($this->envFile)) {
            $content = file_get_contents($this->envFile);
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // 跳过注释和空行
                if (empty($line) || $line[0] === '#') {
                    continue;
                }
                
                // 解析环境变量
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // 去除引号
                    if (preg_match('/^"(.*)"$/', $value, $matches)) {
                        $value = $matches[1];
                    } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                        $value = $matches[1];
                    }
                    
                    $this->env[$key] = $value;
                }
            }
        }
    }
    
    /**
     * 保存环境变量
     *
     * @return bool 是否保存成功
     */
    private function saveEnv()
    {
        $content = "# PVM Environment Variables\n";
        
        foreach ($this->env as $key => $value) {
            // 跳过系统环境变量
            if (isset($_ENV[$key]) && $_ENV[$key] === $value) {
                continue;
            }
            
            $content .= "{$key}=\"{$value}\"\n";
        }
        
        return file_put_contents($this->envFile, $content) !== false;
    }
    
    /**
     * 获取环境变量
     *
     * @param string $key 环境变量名
     * @param string $default 默认值
     * @return string 环境变量值
     */
    public function get($key, $default = '')
    {
        return isset($this->env[$key]) ? $this->env[$key] : $default;
    }
    
    /**
     * 设置环境变量
     *
     * @param string $key 环境变量名
     * @param string $value 环境变量值
     * @param bool $persist 是否持久化
     * @return bool 是否设置成功
     */
    public function set($key, $value, $persist = true)
    {
        $this->env[$key] = $value;
        
        // 设置当前进程的环境变量
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        
        // 持久化环境变量
        if ($persist) {
            return $this->saveEnv();
        }
        
        return true;
    }
    
    /**
     * 删除环境变量
     *
     * @param string $key 环境变量名
     * @param bool $persist 是否持久化
     * @return bool 是否删除成功
     */
    public function delete($key, $persist = true)
    {
        if (isset($this->env[$key])) {
            unset($this->env[$key]);
            
            // 删除当前进程的环境变量
            putenv($key);
            unset($_ENV[$key]);
            
            // 持久化环境变量
            if ($persist) {
                return $this->saveEnv();
            }
        }
        
        return true;
    }
    
    /**
     * 获取所有环境变量
     *
     * @param bool $includeSystem 是否包含系统环境变量
     * @return array 环境变量数组
     */
    public function getAll($includeSystem = false)
    {
        if ($includeSystem) {
            return $this->env;
        }
        
        $env = [];
        
        foreach ($this->env as $key => $value) {
            // 跳过系统环境变量
            if (isset($_ENV[$key]) && $_ENV[$key] === $value) {
                continue;
            }
            
            $env[$key] = $value;
        }
        
        return $env;
    }
    
    /**
     * 设置多个环境变量
     *
     * @param array $env 环境变量数组
     * @param bool $persist 是否持久化
     * @return bool 是否设置成功
     */
    public function setMultiple(array $env, $persist = true)
    {
        foreach ($env as $key => $value) {
            $this->env[$key] = $value;
            
            // 设置当前进程的环境变量
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
        
        // 持久化环境变量
        if ($persist) {
            return $this->saveEnv();
        }
        
        return true;
    }
    
    /**
     * 删除多个环境变量
     *
     * @param array $keys 环境变量名数组
     * @param bool $persist 是否持久化
     * @return bool 是否删除成功
     */
    public function deleteMultiple(array $keys, $persist = true)
    {
        foreach ($keys as $key) {
            if (isset($this->env[$key])) {
                unset($this->env[$key]);
                
                // 删除当前进程的环境变量
                putenv($key);
                unset($_ENV[$key]);
            }
        }
        
        // 持久化环境变量
        if ($persist) {
            return $this->saveEnv();
        }
        
        return true;
    }
    
    /**
     * 清除所有环境变量
     *
     * @param bool $persist 是否持久化
     * @return bool 是否清除成功
     */
    public function clear($persist = true)
    {
        $this->env = $_ENV;
        
        // 持久化环境变量
        if ($persist) {
            return $this->saveEnv();
        }
        
        return true;
    }
    
    /**
     * 生成环境变量加载脚本
     *
     * @param string $shell Shell类型，可以是'bash'或'zsh'
     * @return string 环境变量加载脚本
     */
    public function generateEnvScript($shell = 'bash')
    {
        $script = "# PVM Environment Variables\n";
        
        foreach ($this->env as $key => $value) {
            // 跳过系统环境变量
            if (isset($_ENV[$key]) && $_ENV[$key] === $value) {
                continue;
            }
            
            $script .= "export {$key}=\"{$value}\"\n";
        }
        
        return $script;
    }
    
    /**
     * 安装环境变量加载脚本
     *
     * @param string $shell Shell类型，可以是'bash'或'zsh'
     * @return bool 是否安装成功
     */
    public function installEnvScript($shell = 'bash')
    {
        $homeDir = getenv('HOME');
        $script = $this->generateEnvScript($shell);
        
        if ($shell === 'bash') {
            $rcFile = $homeDir . '/.bashrc';
        } elseif ($shell === 'zsh') {
            $rcFile = $homeDir . '/.zshrc';
        } else {
            return false;
        }
        
        // 检查是否已经安装
        $content = file_exists($rcFile) ? file_get_contents($rcFile) : '';
        
        if (strpos($content, '# PVM Environment Variables') !== false) {
            // 已经安装，更新脚本
            $pattern = '/# PVM Environment Variables.*?# End PVM Environment Variables/s';
            $replacement = "# PVM Environment Variables\n{$script}# End PVM Environment Variables";
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            // 未安装，添加脚本
            $content .= "\n# PVM Environment Variables\n{$script}# End PVM Environment Variables\n";
        }
        
        return file_put_contents($rcFile, $content) !== false;
    }
    
    /**
     * 卸载环境变量加载脚本
     *
     * @param string $shell Shell类型，可以是'bash'或'zsh'
     * @return bool 是否卸载成功
     */
    public function uninstallEnvScript($shell = 'bash')
    {
        $homeDir = getenv('HOME');
        
        if ($shell === 'bash') {
            $rcFile = $homeDir . '/.bashrc';
        } elseif ($shell === 'zsh') {
            $rcFile = $homeDir . '/.zshrc';
        } else {
            return false;
        }
        
        // 检查是否已经安装
        $content = file_exists($rcFile) ? file_get_contents($rcFile) : '';
        
        if (strpos($content, '# PVM Environment Variables') !== false) {
            // 已经安装，删除脚本
            $pattern = '/\n# PVM Environment Variables.*?# End PVM Environment Variables\n/s';
            $content = preg_replace($pattern, '', $content);
            return file_put_contents($rcFile, $content) !== false;
        }
        
        return true;
    }
    
    /**
     * 设置PHP版本环境变量
     *
     * @param string $phpVersion PHP版本
     * @param bool $persist 是否持久化
     * @return bool 是否设置成功
     */
    public function setPhpVersion($phpVersion, $persist = true)
    {
        $pvmDir = $this->pvmDir;
        $versionDir = $pvmDir . '/versions/' . $phpVersion;
        
        // 设置PHP相关环境变量
        $env = [
            'PVM_PHP_VERSION' => $phpVersion,
            'PVM_PHP_BIN' => $versionDir . '/bin/php',
            'PVM_PHP_CONFIG_DIR' => $versionDir . '/etc',
            'PVM_PHP_EXTENSION_DIR' => $versionDir . '/lib/php/extensions',
            'PATH' => $versionDir . '/bin:' . getenv('PATH'),
        ];
        
        return $this->setMultiple($env, $persist);
    }
}
