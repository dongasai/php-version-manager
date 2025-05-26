<?php

namespace VersionManager\Core\Config;

/**
 * PHP配置管理类
 *
 * 负责管理PHP的配置文件，包括php.ini和扩展配置
 */
class PhpConfig
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
     * PHP配置文件路径
     *
     * @var string
     */
    private $phpIni;

    /**
     * 扩展配置目录
     *
     * @var string
     */
    private $extensionConfigDir;

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
        $this->ensurePhpIniExists();
    }

    /**
     * 确保php.ini文件存在
     */
    private function ensurePhpIniExists()
    {
        if (!file_exists($this->phpIni)) {
            $versionDir = dirname($this->configDir);

            // 尝试复制php.ini-development到php.ini
            $phpIniDev = $versionDir . '/lib/php.ini-development';
            if (file_exists($phpIniDev)) {
                copy($phpIniDev, $this->phpIni);
            } else {
                // 如果没有找到php.ini-development，则创建一个空的php.ini
                file_put_contents($this->phpIni, "; PHP Configuration File\n");
            }

            // 设置扩展配置目录
            $this->setPhpIniValue('extension_dir', $versionDir . '/lib/php/extensions');
            $this->setPhpIniValue('scan_dir', $this->extensionConfigDir);
        }
    }

    /**
     * 获取PHP配置文件路径
     *
     * @return string
     */
    public function getPhpIniPath()
    {
        return $this->phpIni;
    }

    /**
     * 获取扩展配置目录
     *
     * @return string
     */
    public function getExtensionConfigDir()
    {
        return $this->extensionConfigDir;
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
     * 获取所有PHP配置
     *
     * @return array
     */
    public function getAllPhpIniValues()
    {
        $values = [];

        // 读取php.ini文件
        $content = file_exists($this->phpIni) ? file_get_contents($this->phpIni) : '';

        // 解析配置
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

                // 去除引号
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                }

                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * 获取PHP配置值（别名方法）
     *
     * @return array
     */
    public function getPhpIniValues()
    {
        return $this->getAllPhpIniValues();
    }

    /**
     * 应用开发环境配置
     *
     * @return bool
     */
    public function applyDevelopmentConfig()
    {
        $versionDir = dirname($this->configDir);
        $phpIniDev = $versionDir . '/lib/php.ini-development';

        if (file_exists($phpIniDev)) {
            // 备份当前配置
            $this->backupConfig();

            // 复制开发环境配置
            if (copy($phpIniDev, $this->phpIni)) {
                // 设置扩展配置目录
                $this->setPhpIniValue('extension_dir', $versionDir . '/lib/php/extensions');
                $this->setPhpIniValue('scan_dir', $this->extensionConfigDir);

                // 设置开发环境特定配置
                $this->setPhpIniValue('display_errors', 'On');
                $this->setPhpIniValue('display_startup_errors', 'On');
                $this->setPhpIniValue('error_reporting', 'E_ALL');
                $this->setPhpIniValue('log_errors', 'On');
                $this->setPhpIniValue('error_log', '/tmp/php_errors.log');
                $this->setPhpIniValue('memory_limit', '256M');
                $this->setPhpIniValue('max_execution_time', '300');

                return true;
            }
        }

        return false;
    }

    /**
     * 应用生产环境配置
     *
     * @return bool
     */
    public function applyProductionConfig()
    {
        $versionDir = dirname($this->configDir);
        $phpIniProd = $versionDir . '/lib/php.ini-production';

        if (file_exists($phpIniProd)) {
            // 备份当前配置
            $this->backupConfig();

            // 复制生产环境配置
            if (copy($phpIniProd, $this->phpIni)) {
                // 设置扩展配置目录
                $this->setPhpIniValue('extension_dir', $versionDir . '/lib/php/extensions');
                $this->setPhpIniValue('scan_dir', $this->extensionConfigDir);

                // 设置生产环境特定配置
                $this->setPhpIniValue('display_errors', 'Off');
                $this->setPhpIniValue('display_startup_errors', 'Off');
                $this->setPhpIniValue('error_reporting', 'E_ALL & ~E_DEPRECATED & ~E_STRICT');
                $this->setPhpIniValue('log_errors', 'On');
                $this->setPhpIniValue('error_log', '/var/log/php_errors.log');
                $this->setPhpIniValue('memory_limit', '128M');
                $this->setPhpIniValue('max_execution_time', '60');

                return true;
            }
        }

        return false;
    }

    /**
     * 备份当前配置
     *
     * @return bool
     */
    public function backupConfig()
    {
        if (file_exists($this->phpIni)) {
            $backupFile = $this->phpIni . '.bak.' . date('YmdHis');
            return copy($this->phpIni, $backupFile);
        }

        return false;
    }

    /**
     * 恢复配置备份
     *
     * @param string $backupFile 备份文件路径，如果为null则使用最新的备份
     * @return bool
     */
    public function restoreConfig($backupFile = null)
    {
        if ($backupFile === null) {
            // 查找最新的备份
            $backups = glob($this->phpIni . '.bak.*');
            if (!empty($backups)) {
                rsort($backups); // 按文件名降序排序
                $backupFile = $backups[0];
            } else {
                return false;
            }
        }

        if (file_exists($backupFile)) {
            return copy($backupFile, $this->phpIni);
        }

        return false;
    }

    /**
     * 获取配置备份列表
     *
     * @return array
     */
    public function getConfigBackups()
    {
        $backups = glob($this->phpIni . '.bak.*');
        $result = [];

        foreach ($backups as $backup) {
            $timestamp = substr($backup, strrpos($backup, '.') + 1);
            $date = date('Y-m-d H:i:s', strtotime($timestamp));
            $result[] = [
                'file' => $backup,
                'date' => $date,
            ];
        }

        return $result;
    }

    /**
     * 应用自定义配置
     *
     * @param array $config 配置数组
     * @return bool
     */
    public function applyCustomConfig(array $config)
    {
        $success = true;

        foreach ($config as $key => $value) {
            if (!$this->setPhpIniValue($key, $value)) {
                $success = false;
            }
        }

        return $success;
    }
}
