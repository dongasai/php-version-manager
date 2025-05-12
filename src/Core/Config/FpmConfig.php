<?php

namespace VersionManager\Core\Config;

/**
 * PHP-FPM配置管理类
 * 
 * 负责管理PHP-FPM的配置文件
 */
class FpmConfig
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
     * PHP-FPM配置文件路径
     *
     * @var string
     */
    private $fpmConf;
    
    /**
     * PHP-FPM www配置文件路径
     *
     * @var string
     */
    private $fpmWwwConf;
    
    /**
     * 构造函数
     *
     * @param string $phpVersion PHP版本
     */
    public function __construct($phpVersion = null)
    {
        if ($phpVersion === null) {
            // 获取当前PHP版本
            $switcher = new \VersionManager\Core\VersionSwitcher();
            $phpVersion = $switcher->getCurrentVersion();
        }
        
        $this->phpVersion = $phpVersion;
        
        // 设置配置目录
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $phpVersion;
        $this->configDir = $versionDir . '/etc';
        $this->fpmConf = $this->configDir . '/php-fpm.conf';
        $this->fpmWwwConf = $this->configDir . '/php-fpm.d/www.conf';
        
        // 确保目录存在
        if (!is_dir($this->configDir)) {
            mkdir($this->configDir, 0755, true);
        }
        
        $fpmDir = dirname($this->fpmWwwConf);
        if (!is_dir($fpmDir)) {
            mkdir($fpmDir, 0755, true);
        }
        
        // 确保配置文件存在
        $this->ensureFpmConfigExists();
    }
    
    /**
     * 确保FPM配置文件存在
     */
    private function ensureFpmConfigExists()
    {
        $versionDir = dirname($this->configDir);
        
        // 检查php-fpm.conf是否存在
        if (!file_exists($this->fpmConf)) {
            // 尝试复制默认配置
            $defaultConf = $versionDir . '/etc/php-fpm.conf.default';
            if (file_exists($defaultConf)) {
                copy($defaultConf, $this->fpmConf);
            } else {
                // 创建基本配置
                $content = <<<EOT
[global]
pid = /tmp/php-fpm-{$this->phpVersion}.pid
error_log = /tmp/php-fpm-{$this->phpVersion}.log
include = {$this->configDir}/php-fpm.d/*.conf
EOT;
                file_put_contents($this->fpmConf, $content);
            }
        }
        
        // 检查www.conf是否存在
        if (!file_exists($this->fpmWwwConf)) {
            // 尝试复制默认配置
            $defaultWwwConf = $versionDir . '/etc/php-fpm.d/www.conf.default';
            if (file_exists($defaultWwwConf)) {
                copy($defaultWwwConf, $this->fpmWwwConf);
            } else {
                // 创建基本配置
                $content = <<<EOT
[www]
user = www-data
group = www-data
listen = 127.0.0.1:9000
listen.allowed_clients = 127.0.0.1
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
EOT;
                file_put_contents($this->fpmWwwConf, $content);
            }
        }
    }
    
    /**
     * 获取PHP-FPM配置文件路径
     *
     * @return string
     */
    public function getFpmConfPath()
    {
        return $this->fpmConf;
    }
    
    /**
     * 获取PHP-FPM www配置文件路径
     *
     * @return string
     */
    public function getFpmWwwConfPath()
    {
        return $this->fpmWwwConf;
    }
    
    /**
     * 设置FPM配置值
     *
     * @param string $section 配置节
     * @param string $key 配置键
     * @param string $value 配置值
     * @param string $file 配置文件，可以是'fpm'或'www'
     * @return bool
     */
    public function setFpmValue($section, $key, $value, $file = 'www')
    {
        $configFile = $file === 'fpm' ? $this->fpmConf : $this->fpmWwwConf;
        
        // 读取配置文件
        $content = file_exists($configFile) ? file_get_contents($configFile) : '';
        
        // 检查节是否存在
        $sectionPattern = '/^\[' . preg_quote($section, '/') . '\]/m';
        if (!preg_match($sectionPattern, $content)) {
            // 添加节
            $content .= "\n[{$section}]\n";
        }
        
        // 检查配置是否已存在
        $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=.*$/m';
        $replacement = "{$key} = {$value}";
        
        // 查找节的范围
        $sectionStart = strpos($content, "[{$section}]");
        if ($sectionStart !== false) {
            $sectionEnd = strpos($content, '[', $sectionStart + 1);
            if ($sectionEnd === false) {
                $sectionEnd = strlen($content);
            }
            
            $sectionContent = substr($content, $sectionStart, $sectionEnd - $sectionStart);
            
            if (preg_match($pattern, $sectionContent)) {
                // 更新配置
                $newSectionContent = preg_replace($pattern, $replacement, $sectionContent);
                $content = substr_replace($content, $newSectionContent, $sectionStart, $sectionEnd - $sectionStart);
            } else {
                // 添加配置到节
                $insertPos = $sectionEnd;
                $content = substr_replace($content, "\n{$replacement}\n", $insertPos, 0);
            }
        }
        
        // 写入配置文件
        return file_put_contents($configFile, $content) !== false;
    }
    
    /**
     * 获取FPM配置值
     *
     * @param string $section 配置节
     * @param string $key 配置键
     * @param string $file 配置文件，可以是'fpm'或'www'
     * @return string|null
     */
    public function getFpmValue($section, $key, $file = 'www')
    {
        $configFile = $file === 'fpm' ? $this->fpmConf : $this->fpmWwwConf;
        
        // 读取配置文件
        $content = file_exists($configFile) ? file_get_contents($configFile) : '';
        
        // 查找节的范围
        $sectionStart = strpos($content, "[{$section}]");
        if ($sectionStart !== false) {
            $sectionEnd = strpos($content, '[', $sectionStart + 1);
            if ($sectionEnd === false) {
                $sectionEnd = strlen($content);
            }
            
            $sectionContent = substr($content, $sectionStart, $sectionEnd - $sectionStart);
            
            // 查找配置
            $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=\s*(.*)$/m';
            if (preg_match($pattern, $sectionContent, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return null;
    }
    
    /**
     * 获取FPM节的所有配置
     *
     * @param string $section 配置节
     * @param string $file 配置文件，可以是'fpm'或'www'
     * @return array
     */
    public function getSectionValues($section, $file = 'www')
    {
        $configFile = $file === 'fpm' ? $this->fpmConf : $this->fpmWwwConf;
        $values = [];
        
        // 读取配置文件
        $content = file_exists($configFile) ? file_get_contents($configFile) : '';
        
        // 查找节的范围
        $sectionStart = strpos($content, "[{$section}]");
        if ($sectionStart !== false) {
            $sectionEnd = strpos($content, '[', $sectionStart + 1);
            if ($sectionEnd === false) {
                $sectionEnd = strlen($content);
            }
            
            $sectionContent = substr($content, $sectionStart, $sectionEnd - $sectionStart);
            
            // 解析配置
            $lines = explode("\n", $sectionContent);
            foreach ($lines as $line) {
                $line = trim($line);
                
                // 跳过注释和空行
                if (empty($line) || $line[0] === ';' || $line[0] === '[') {
                    continue;
                }
                
                // 解析配置项
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $values[trim($key)] = trim($value);
                }
            }
        }
        
        return $values;
    }
    
    /**
     * 应用开发环境配置
     *
     * @return bool
     */
    public function applyDevelopmentConfig()
    {
        // 备份当前配置
        $this->backupConfig();
        
        // 设置全局配置
        $this->setFpmValue('global', 'error_log', '/tmp/php-fpm-error.log', 'fpm');
        $this->setFpmValue('global', 'log_level', 'debug', 'fpm');
        
        // 设置www配置
        $this->setFpmValue('www', 'user', get_current_user());
        $this->setFpmValue('www', 'group', get_current_user());
        $this->setFpmValue('www', 'listen', '127.0.0.1:9000');
        $this->setFpmValue('www', 'pm', 'dynamic');
        $this->setFpmValue('www', 'pm.max_children', '10');
        $this->setFpmValue('www', 'pm.start_servers', '2');
        $this->setFpmValue('www', 'pm.min_spare_servers', '1');
        $this->setFpmValue('www', 'pm.max_spare_servers', '3');
        $this->setFpmValue('www', 'pm.max_requests', '500');
        $this->setFpmValue('www', 'catch_workers_output', 'yes');
        $this->setFpmValue('www', 'php_flag[display_errors]', 'on');
        $this->setFpmValue('www', 'php_admin_value[error_log]', '/tmp/php-fpm-www-error.log');
        $this->setFpmValue('www', 'php_admin_value[memory_limit]', '256M');
        
        return true;
    }
    
    /**
     * 应用生产环境配置
     *
     * @return bool
     */
    public function applyProductionConfig()
    {
        // 备份当前配置
        $this->backupConfig();
        
        // 设置全局配置
        $this->setFpmValue('global', 'error_log', '/var/log/php-fpm/error.log', 'fpm');
        $this->setFpmValue('global', 'log_level', 'notice', 'fpm');
        
        // 设置www配置
        $this->setFpmValue('www', 'user', 'www-data');
        $this->setFpmValue('www', 'group', 'www-data');
        $this->setFpmValue('www', 'listen', '127.0.0.1:9000');
        $this->setFpmValue('www', 'pm', 'dynamic');
        $this->setFpmValue('www', 'pm.max_children', '50');
        $this->setFpmValue('www', 'pm.start_servers', '5');
        $this->setFpmValue('www', 'pm.min_spare_servers', '5');
        $this->setFpmValue('www', 'pm.max_spare_servers', '35');
        $this->setFpmValue('www', 'pm.max_requests', '1000');
        $this->setFpmValue('www', 'catch_workers_output', 'no');
        $this->setFpmValue('www', 'php_flag[display_errors]', 'off');
        $this->setFpmValue('www', 'php_admin_value[error_log]', '/var/log/php-fpm/www-error.log');
        $this->setFpmValue('www', 'php_admin_value[memory_limit]', '128M');
        
        return true;
    }
    
    /**
     * 备份当前配置
     *
     * @return bool
     */
    public function backupConfig()
    {
        $success = true;
        
        if (file_exists($this->fpmConf)) {
            $backupFile = $this->fpmConf . '.bak.' . date('YmdHis');
            if (!copy($this->fpmConf, $backupFile)) {
                $success = false;
            }
        }
        
        if (file_exists($this->fpmWwwConf)) {
            $backupFile = $this->fpmWwwConf . '.bak.' . date('YmdHis');
            if (!copy($this->fpmWwwConf, $backupFile)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * 恢复配置备份
     *
     * @param string $backupFile 备份文件路径，如果为null则使用最新的备份
     * @param string $file 配置文件，可以是'fpm'或'www'
     * @return bool
     */
    public function restoreConfig($backupFile = null, $file = 'both')
    {
        $success = true;
        
        if ($file === 'both' || $file === 'fpm') {
            $fpmBackup = $backupFile;
            if ($fpmBackup === null) {
                // 查找最新的备份
                $backups = glob($this->fpmConf . '.bak.*');
                if (!empty($backups)) {
                    rsort($backups); // 按文件名降序排序
                    $fpmBackup = $backups[0];
                } else {
                    $success = false;
                }
            }
            
            if ($fpmBackup && file_exists($fpmBackup)) {
                if (!copy($fpmBackup, $this->fpmConf)) {
                    $success = false;
                }
            }
        }
        
        if ($file === 'both' || $file === 'www') {
            $wwwBackup = $backupFile;
            if ($wwwBackup === null) {
                // 查找最新的备份
                $backups = glob($this->fpmWwwConf . '.bak.*');
                if (!empty($backups)) {
                    rsort($backups); // 按文件名降序排序
                    $wwwBackup = $backups[0];
                } else {
                    $success = false;
                }
            }
            
            if ($wwwBackup && file_exists($wwwBackup)) {
                if (!copy($wwwBackup, $this->fpmWwwConf)) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
    
    /**
     * 获取配置备份列表
     *
     * @param string $file 配置文件，可以是'fpm'或'www'
     * @return array
     */
    public function getConfigBackups($file = 'both')
    {
        $result = [];
        
        if ($file === 'both' || $file === 'fpm') {
            $fpmBackups = glob($this->fpmConf . '.bak.*');
            foreach ($fpmBackups as $backup) {
                $timestamp = substr($backup, strrpos($backup, '.') + 1);
                $date = date('Y-m-d H:i:s', strtotime($timestamp));
                $result['fpm'][] = [
                    'file' => $backup,
                    'date' => $date,
                ];
            }
        }
        
        if ($file === 'both' || $file === 'www') {
            $wwwBackups = glob($this->fpmWwwConf . '.bak.*');
            foreach ($wwwBackups as $backup) {
                $timestamp = substr($backup, strrpos($backup, '.') + 1);
                $date = date('Y-m-d H:i:s', strtotime($timestamp));
                $result['www'][] = [
                    'file' => $backup,
                    'date' => $date,
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * 应用自定义配置
     *
     * @param string $section 配置节
     * @param array $config 配置数组
     * @param string $file 配置文件，可以是'fpm'或'www'
     * @return bool
     */
    public function applyCustomConfig($section, array $config, $file = 'www')
    {
        $success = true;
        
        foreach ($config as $key => $value) {
            if (!$this->setFpmValue($section, $key, $value, $file)) {
                $success = false;
            }
        }
        
        return $success;
    }
}
