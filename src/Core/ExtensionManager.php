<?php

namespace VersionManager\Core;

use VersionManager\Core\Extension\ExtensionDriverFactory;
use VersionManager\Core\Extension\ExtensionDriverInterface;

/**
 * 扩展管理器类
 *
 * 负责管理PHP扩展的安装、配置和删除
 */
class ExtensionManager
{
    /**
     * PHP版本
     *
     * @var string
     */
    private $phpVersion;

    /**
     * 发行版名称
     *
     * @var string
     */
    private $distro;

    /**
     * 架构名称
     *
     * @var string
     */
    private $arch;

    /**
     * 构造函数
     *
     * @param string $phpVersion PHP版本
     */
    public function __construct($phpVersion = null)
    {
        $switcher = new VersionSwitcher();
        $this->phpVersion = $phpVersion ?: $switcher->getCurrentVersion();

        // 检测系统信息
        $this->detectSystemInfo();
    }

    /**
     * 检测系统信息
     */
    private function detectSystemInfo()
    {
        // 检测架构
        $this->arch = php_uname('m');

        // 检测发行版
        $this->distro = '';
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');
            if (isset($osRelease['ID'])) {
                $this->distro = strtolower($osRelease['ID']);
            }
        }
    }

    /**
     * 获取扩展驱动
     *
     * @param string $extension 扩展名称
     * @return ExtensionDriverInterface
     */
    public function getDriver($extension)
    {
        return ExtensionDriverFactory::getDriver($extension, $this->distro, $this->arch);
    }

    /**
     * 获取已安装的扩展列表
     *
     * @return array
     */
    public function getInstalledExtensions()
    {
        $result = [];

        try {
            // 获取PHP内置扩展
            $builtinExtensions = $this->getBuiltinExtensions();

            // 获取PHP配置目录的扩展（仅对PVM管理的版本）
            $configuredExtensions = [];
            if ($this->isVersionManaged()) {
                $config = new ExtensionConfig($this->phpVersion);
                $configuredExtensions = $config->getConfiguredExtensions();
            }

            // 合并扩展列表
            $extensions = array_merge(array_keys($configuredExtensions), array_keys($builtinExtensions));
            $extensions = array_unique($extensions);

            // 获取每个扩展的信息
            foreach ($extensions as $extension) {
                if (isset($builtinExtensions[$extension])) {
                    $result[$extension] = $builtinExtensions[$extension];
                } elseif (isset($configuredExtensions[$extension])) {
                    $result[$extension] = [
                        'name' => $extension,
                        'type' => 'external',
                        'status' => 'enabled',
                        'config' => $configuredExtensions[$extension],
                    ];
                }
            }
        } catch (\Exception $e) {
            // 如果出错，返回空数组
            error_log("Error getting installed extensions: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * 检查当前PHP版本是否由PVM管理
     *
     * @return bool
     */
    private function isVersionManaged()
    {
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $this->phpVersion;
        return is_dir($versionDir);
    }

    /**
     * 获取可用的扩展列表
     *
     * @return array
     */
    public function getAvailableExtensions()
    {
        $result = [];

        // 加载常用扩展配置
        $configFile = __DIR__ . '/../../config/extensions/common_extensions.php';
        if (file_exists($configFile)) {
            $extensions = require $configFile;

            // 获取每个扩展的信息
            foreach ($extensions as $extension => $info) {
                $driver = $this->getDriver($extension);
                if ($driver->isAvailable($this->phpVersion) && !$driver->isInstalled($this->phpVersion)) {
                    $result[$extension] = $driver->getInfo($this->phpVersion);
                }
            }
        }

        return $result;
    }

    /**
     * 获取PHP内置扩展
     *
     * @return array
     */
    private function getBuiltinExtensions()
    {
        $extensions = [];

        // 使用 PHP 命令行获取内置扩展列表
        $output = [];
        $phpBin = $this->getPhpBinary();
        exec($phpBin . ' -m', $output);

        $inExtensions = false;
        foreach ($output as $line) {
            $line = trim($line);

            if ($line === '[PHP Modules]') {
                $inExtensions = true;
                continue;
            }

            if ($line === '[Zend Modules]') {
                $inExtensions = false;
                continue;
            }

            if ($inExtensions && !empty($line)) {
                $extensions[$line] = [
                    'name' => $line,
                    'type' => 'builtin',
                    'status' => 'enabled',
                    'config' => [],
                ];
            }
        }

        return $extensions;
    }

    /**
     * 获取PHP二进制文件路径
     *
     * @return string
     */
    private function getPhpBinary()
    {
        // 默认使用 PVM 管理的 PHP 版本
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $this->phpVersion;
        $phpBin = $versionDir . '/bin/php';

        if (file_exists($phpBin)) {
            return $phpBin;
        }

        // 如果不存在，则使用系统 PHP
        return 'php';
    }

    /**
     * 安装扩展
     *
     * @param string $extension 扩展名称
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws \Exception 安装失败时抛出异常
     */
    public function installExtension($extension, array $options = [])
    {
        $driver = $this->getDriver($extension);
        return $driver->install($this->phpVersion, $options);
    }

    /**
     * 删除扩展
     *
     * @param string $extension 扩展名称
     * @param array $options 删除选项
     * @return bool 是否删除成功
     * @throws \Exception 删除失败时抛出异常
     */
    public function removeExtension($extension, array $options = [])
    {
        $driver = $this->getDriver($extension);
        return $driver->remove($this->phpVersion, $options);
    }

    /**
     * 启用扩展
     *
     * @param string $extension 扩展名称
     * @param array $config 扩展配置
     * @return bool 是否启用成功
     * @throws \Exception 启用失败时抛出异常
     */
    public function enableExtension($extension, array $config = [])
    {
        $driver = $this->getDriver($extension);
        return $driver->enable($this->phpVersion, $config);
    }

    /**
     * 禁用扩展
     *
     * @param string $extension 扩展名称
     * @return bool 是否禁用成功
     * @throws \Exception 禁用失败时抛出异常
     */
    public function disableExtension($extension)
    {
        $driver = $this->getDriver($extension);
        return $driver->disable($this->phpVersion);
    }

    /**
     * 配置扩展
     *
     * @param string $extension 扩展名称
     * @param array $config 扩展配置
     * @return bool 是否配置成功
     * @throws \Exception 配置失败时抛出异常
     */
    public function configureExtension($extension, array $config)
    {
        $driver = $this->getDriver($extension);
        return $driver->configure($this->phpVersion, $config);
    }

    /**
     * 获取扩展信息
     *
     * @param string $extension 扩展名称
     * @return array|null
     */
    public function getExtensionInfo($extension)
    {
        $driver = $this->getDriver($extension);
        return $driver->getInfo($this->phpVersion);
    }

    /**
     * 检查扩展是否已安装
     *
     * @param string $extension 扩展名称
     * @return bool
     */
    public function isExtensionInstalled($extension)
    {
        $driver = $this->getDriver($extension);
        return $driver->isInstalled($this->phpVersion);
    }
}
