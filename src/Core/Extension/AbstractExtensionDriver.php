<?php

namespace VersionManager\Core\Extension;

use VersionManager\Core\ExtensionConfig;
use VersionManager\Core\Extension\ExtensionType;
use VersionManager\Core\Config\MirrorConfig;
use VersionManager\Core\Tags\TaggableInterface;

/**
 * 抽象扩展驱动基类
 *
 * 实现一些通用功能
 */
abstract class AbstractExtensionDriver implements ExtensionDriverInterface, TaggableInterface
{
    /**
     * 扩展名称
     *
     * @var string
     */
    protected $name;

    /**
     * 扩展描述
     *
     * @var string
     */
    protected $description;

    /**
     * 扩展版本
     *
     * @var string
     */
    protected $version;

    /**
     * 扩展类型
     *
     * @var string
     */
    protected $type;

    /**
     * 扩展依赖
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * 扩展默认配置
     *
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * 是否是Zend扩展
     *
     * @var bool
     */
    protected $isZend = false;

    /**
     * 构造函数
     *
     * @param string $name 扩展名称
     * @param string $description 扩展描述
     * @param string $version 扩展版本
     * @param string $type 扩展类型，使用ExtensionType枚举
     * @param array $dependencies 扩展依赖
     * @param array $defaultConfig 扩展默认配置
     * @param bool $isZend 是否是Zend扩展
     * @throws \InvalidArgumentException 如果类型无效
     */
    public function __construct($name, $description = '', $version = '', $type = ExtensionType::BUILTIN, array $dependencies = [], array $defaultConfig = [], $isZend = false)
    {
        $this->name = $name;
        $this->description = $description;
        $this->version = $version;

        // 验证扩展类型
        if (!empty($type) && !ExtensionType::isValid($type)) {
            throw new \InvalidArgumentException("无效的扩展类型: {$type}");
        }

        $this->type = $type;
        $this->dependencies = $dependencies;
        $this->defaultConfig = $defaultConfig;
        $this->isZend = $isZend;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 获取扩展类型描述
     *
     * @return string
     */
    public function getTypeDescription()
    {
        return ExtensionType::getDescription($this->type);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return $this->defaultConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function isZend()
    {
        return $this->isZend;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        $tags = [];

        // 添加扩展名称作为标签
        $tags[] = strtolower($this->name);

        // 添加扩展类型作为标签
        if ($this->type) {
            $tags[] = strtolower($this->type);
        }

        // 添加Zend标签
        if ($this->isZend) {
            $tags[] = 'zend';
        }

        return $tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo($phpVersion)
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'version' => $this->getVersion(),
            'type' => $this->getType(),
            'type_description' => $this->getTypeDescription(),
            'dependencies' => $this->getDependencies(),
            'config' => $this->getDefaultConfig(),
            'installed' => $this->isInstalled($phpVersion),
            'available' => $this->isAvailable($phpVersion),
            'zend' => $this->isZend(),
        ];
    }

    /**
     * 获取PHP配置对象
     *
     * @param string $phpVersion PHP版本
     * @return ExtensionConfig
     */
    protected function getConfig($phpVersion)
    {
        return new ExtensionConfig($phpVersion);
    }

    /**
     * 获取PHP二进制文件路径
     *
     * @param string $phpVersion PHP版本
     * @return string
     */
    protected function getPhpBinary($phpVersion)
    {
        // 默认使用 PVM 管理的 PHP 版本
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $phpVersion;
        $phpBin = $versionDir . '/bin/php';

        if (file_exists($phpBin)) {
            return $phpBin;
        }

        // 如果不存在，则使用系统 PHP
        return 'php';
    }

    /**
     * 获取PHP配置目录
     *
     * @param string $phpVersion PHP版本
     * @return string
     */
    protected function getPhpConfigDir($phpVersion)
    {
        $config = $this->getConfig($phpVersion);
        return $config->getConfigDir();
    }

    /**
     * 获取PHP扩展目录
     *
     * @param string $phpVersion PHP版本
     * @return string
     */
    protected function getPhpExtensionDir($phpVersion)
    {
        $config = $this->getConfig($phpVersion);
        return $config->getExtensionDir();
    }

    /**
     * 获取扩展配置文件路径
     *
     * @param string $phpVersion PHP版本
     * @return string
     */
    protected function getExtensionConfigFile($phpVersion)
    {
        $config = $this->getConfig($phpVersion);
        return $config->getExtensionConfigFilePath($this->getName());
    }

    /**
     * 获取扩展库文件路径
     *
     * @param string $phpVersion PHP版本
     * @return string
     */
    protected function getExtensionLibraryFile($phpVersion)
    {
        $config = $this->getConfig($phpVersion);
        return $config->getExtensionLibraryPath($this->getName());
    }

    /**
     * 检查扩展配置文件是否存在
     *
     * @param string $phpVersion PHP版本
     * @return bool
     */
    protected function hasExtensionConfigFile($phpVersion)
    {
        $config = $this->getConfig($phpVersion);
        return $config->hasExtensionConfig($this->getName());
    }

    /**
     * 检查扩展库文件是否存在
     *
     * @param string $phpVersion PHP版本
     * @return bool
     */
    protected function hasExtensionLibraryFile($phpVersion)
    {
        $config = $this->getConfig($phpVersion);
        return $config->hasExtensionLibrary($this->getName());
    }

    /**
     * 读取扩展配置
     *
     * @param string $phpVersion PHP版本
     * @return array
     */
    protected function readExtensionConfig($phpVersion)
    {
        $config = $this->getConfig($phpVersion);
        return $config->readExtensionConfig($this->getName());
    }

    /**
     * 写入扩展配置
     *
     * @param string $phpVersion PHP版本
     * @param array $config 扩展配置
     * @return bool
     */
    protected function writeExtensionConfig($phpVersion, array $config)
    {
        $extensionConfig = $this->getConfig($phpVersion);
        return $extensionConfig->writeExtensionConfig($this->getName(), $config, $this->isZend());
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled($phpVersion)
    {
        // 检查扩展配置文件和库文件是否存在
        return $this->hasExtensionConfigFile($phpVersion) && $this->hasExtensionLibraryFile($phpVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable($phpVersion)
    {
        // 默认实现，子类可以重写
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function enable($phpVersion, array $config = [])
    {
        // 检查扩展是否已安装
        if (!$this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 未安装");
        }

        // 合并默认配置和用户配置
        $mergedConfig = array_merge($this->getDefaultConfig(), $config);

        // 写入扩展配置
        return $this->writeExtensionConfig($phpVersion, $mergedConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function disable($phpVersion)
    {
        // 检查扩展是否已安装
        if (!$this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 未安装");
        }

        // 禁用扩展
        $config = $this->getConfig($phpVersion);
        return $config->disableExtension($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function configure($phpVersion, array $config)
    {
        // 检查扩展是否已安装
        if (!$this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 未安装");
        }

        // 读取当前配置
        $currentConfig = $this->readExtensionConfig($phpVersion);

        // 合并配置
        $mergedConfig = array_merge($currentConfig, $config);

        // 写入扩展配置
        return $this->writeExtensionConfig($phpVersion, $mergedConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($phpVersion, array $options = [])
    {
        // 检查扩展是否已安装
        if (!$this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 未安装");
        }

        // 如果是内置扩展，则只能禁用而不能删除
        if ($this->getType() === ExtensionType::BUILTIN) {
            if (isset($options['disable']) && $options['disable']) {
                return $this->disable($phpVersion);
            } else {
                throw new \Exception("无法删除内置扩展 {$this->getName()}，只能禁用");
            }
        }

        // 禁用扩展
        $this->disable($phpVersion);

        // 删除扩展配置文件
        $configFile = $this->getExtensionConfigFile($phpVersion);
        if (file_exists($configFile)) {
            unlink($configFile);
        }

        // 如果是 PECL 扩展，则使用 pecl uninstall 命令删除
        if ($this->getType() === ExtensionType::PECL) {
            $command = "pecl uninstall {$this->getName()}";
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("删除扩展 {$this->getName()} 失败: " . implode("\n", $output));
            }
        }

        return true;
    }
}