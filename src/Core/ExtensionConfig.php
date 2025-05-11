<?php

namespace VersionManager\Core;

/**
 * PHP扩展配置类
 *
 * 负责管理PHP扩展的配置
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
     * PHP配置目录
     *
     * @var string
     */
    private $configDir;

    /**
     * PHP扩展目录
     *
     * @var string
     */
    private $extensionDir;

    /**
     * 构造函数
     *
     * @param string $phpVersion PHP版本
     * @param string $configDir PHP配置目录
     * @param string $extensionDir PHP扩展目录
     */
    public function __construct($phpVersion, $configDir = null, $extensionDir = null)
    {
        $this->phpVersion = $phpVersion;
        $this->configDir = $configDir ?: $this->detectConfigDir();
        $this->extensionDir = $extensionDir ?: $this->detectExtensionDir();
    }

    /**
     * 检测 PHP 配置目录
     *
     * @return string
     */
    private function detectConfigDir()
    {
        // 默认使用 PVM 管理的 PHP 版本的配置目录
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $this->phpVersion;
        $configDir = $versionDir . '/etc';

        if (is_dir($configDir)) {
            return $configDir;
        }

        // 如果不存在，则尝试使用系统 PHP 的配置目录
        $output = [];
        exec('php -i | grep "Loaded Configuration File"', $output);

        if (!empty($output)) {
            $line = $output[0];
            $configFile = trim(str_replace('Loaded Configuration File => ', '', $line));
            return dirname($configFile);
        }

        // 如果无法检测，则使用默认目录
        return '/etc/php';
    }

    /**
     * 检测 PHP 扩展目录
     *
     * @return string
     */
    private function detectExtensionDir()
    {
        // 默认使用 PVM 管理的 PHP 版本的扩展目录
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $this->phpVersion;
        $extensionDir = $versionDir . '/lib/php/extensions';

        if (is_dir($extensionDir)) {
            return $extensionDir;
        }

        // 如果不存在，则尝试使用系统 PHP 的扩展目录
        $output = [];
        exec('php -i | grep "extension_dir"', $output);

        if (!empty($output)) {
            foreach ($output as $line) {
                if (strpos($line, '=>') !== false) {
                    $parts = explode('=>', $line);
                    return trim($parts[1]);
                }
            }
        }

        // 如果无法检测，则使用默认目录
        return '/usr/lib/php/extensions';
    }

    /**
     * 获取 PHP 配置目录
     *
     * @return string
     */
    public function getConfigDir()
    {
        return $this->configDir;
    }

    /**
     * 获取 PHP 扩展目录
     *
     * @return string
     */
    public function getExtensionDir()
    {
        return $this->extensionDir;
    }

    /**
     * 获取 PHP 配置文件路径
     *
     * @return string
     */
    public function getConfigFilePath()
    {
        return $this->configDir . '/php.ini';
    }

    /**
     * 获取 PHP 扩展配置目录
     *
     * @return string
     */
    public function getExtensionConfigDir()
    {
        $confDir = $this->configDir . '/conf.d';

        if (!is_dir($confDir)) {
            mkdir($confDir, 0755, true);
        }

        return $confDir;
    }

    /**
     * 获取扩展配置文件路径
     *
     * @param string $extension 扩展名称
     * @return string
     */
    public function getExtensionConfigFilePath($extension)
    {
        return $this->getExtensionConfigDir() . '/' . $extension . '.ini';
    }

    /**
     * 获取扩展库文件路径
     *
     * @param string $extension 扩展名称
     * @return string
     */
    public function getExtensionLibraryPath($extension)
    {
        return $this->extensionDir . '/' . $extension . '.so';
    }

    /**
     * 检查扩展配置文件是否存在
     *
     * @param string $extension 扩展名称
     * @return bool
     */
    public function hasExtensionConfig($extension)
    {
        return file_exists($this->getExtensionConfigFilePath($extension));
    }

    /**
     * 检查扩展库文件是否存在
     *
     * @param string $extension 扩展名称
     * @return bool
     */
    public function hasExtensionLibrary($extension)
    {
        return file_exists($this->getExtensionLibraryPath($extension));
    }

    /**
     * 读取扩展配置
     *
     * @param string $extension 扩展名称
     * @return array
     */
    public function readExtensionConfig($extension)
    {
        $configFile = $this->getExtensionConfigFilePath($extension);

        if (!file_exists($configFile)) {
            return [];
        }

        $config = [];
        $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // 跳过注释
            if (empty($line) || $line[0] === ';') {
                continue;
            }

            // 解析配置项
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // 处理引号
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                }

                $config[$key] = $value;
            } elseif (strpos($line, 'extension=') === 0) {
                // 处理扩展声明
                $extName = trim(substr($line, 10));
                $config['extension'] = $extName;
            } elseif (strpos($line, 'zend_extension=') === 0) {
                // 处理Zend扩展声明
                $extName = trim(substr($line, 15));
                $config['zend_extension'] = $extName;
            }
        }

        return $config;
    }

    /**
     * 写入扩展配置
     *
     * @param string $extension 扩展名称
     * @param array $config 扩展配置
     * @param bool $isZend 是否是Zend扩展
     * @return bool
     */
    public function writeExtensionConfig($extension, array $config, $isZend = false)
    {
        $configFile = $this->getExtensionConfigFilePath($extension);
        $content = '';

        // 添加扩展声明
        if ($isZend) {
            $content .= 'zend_extension=' . $extension . '.so' . PHP_EOL;
        } else {
            $content .= 'extension=' . $extension . '.so' . PHP_EOL;
        }

        // 添加配置项
        foreach ($config as $key => $value) {
            if ($key === 'extension' || $key === 'zend_extension') {
                continue;
            }

            $content .= $extension . '.' . $key . '=' . $value . PHP_EOL;
        }

        return file_put_contents($configFile, $content) !== false;
    }

    /**
     * 启用扩展
     *
     * @param string $extension 扩展名称
     * @param array $config 扩展配置
     * @param bool $isZend 是否是Zend扩展
     * @return bool
     */
    public function enableExtension($extension, array $config = [], $isZend = false)
    {
        return $this->writeExtensionConfig($extension, $config, $isZend);
    }

    /**
     * 禁用扩展
     *
     * @param string $extension 扩展名称
     * @return bool
     */
    public function disableExtension($extension)
    {
        $configFile = $this->getExtensionConfigFilePath($extension);

        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $content = preg_replace('/^(zend_)?extension=/', ';$1extension=', $content);
            return file_put_contents($configFile, $content) !== false;
        }

        return true;
    }

    /**
     * 删除扩展配置
     *
     * @param string $extension 扩展名称
     * @return bool
     */
    public function removeExtensionConfig($extension)
    {
        $configFile = $this->getExtensionConfigFilePath($extension);

        if (file_exists($configFile)) {
            return unlink($configFile);
        }

        return true;
    }

    /**
     * 获取所有已配置的扩展
     *
     * @return array
     */
    public function getConfiguredExtensions()
    {
        $extensions = [];
        $confDir = $this->getExtensionConfigDir();

        if (!is_dir($confDir)) {
            return $extensions;
        }

        $files = glob($confDir . '/*.ini');

        foreach ($files as $file) {
            $extension = pathinfo($file, PATHINFO_FILENAME);
            $config = $this->readExtensionConfig($extension);
            $isEnabled = true;

            // 检查扩展是否被禁用
            $content = file_get_contents($file);
            if (preg_match('/^;(zend_)?extension=/', $content)) {
                $isEnabled = false;
            }

            $isZend = isset($config['zend_extension']) || strpos($content, 'zend_extension=') !== false;

            $extensions[$extension] = [
                'name' => $extension,
                'config' => $config,
                'enabled' => $isEnabled,
                'zend' => $isZend,
            ];
        }

        return $extensions;
    }
}
