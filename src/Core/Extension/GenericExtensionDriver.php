<?php

namespace VersionManager\Core\Extension;

use VersionManager\Core\Extension\ExtensionType;
use VersionManager\Core\Config\MirrorConfig;

/**
 * 通用扩展驱动类
 *
 * 用于处理没有特定驱动的扩展
 */
class GenericExtensionDriver extends AbstractExtensionDriver
{
    /**
     * 构造函数
     *
     * @param string $name 扩展名称
     */
    public function __construct($name)
    {
        // 加载扩展信息
        $info = $this->loadExtensionInfo($name);

        parent::__construct(
            $name,
            isset($info['description']) ? $info['description'] : '',
            isset($info['version']) ? $info['version'] : '',
            isset($info['type']) ? $info['type'] : ExtensionType::PECL,
            isset($info['dependencies']) ? $info['dependencies'] : [],
            isset($info['config']) ? $info['config'] : [],
            isset($info['zend']) && $info['zend']
        );
    }

    /**
     * 加载扩展信息
     *
     * @param string $name 扩展名称
     * @return array
     */
    private function loadExtensionInfo($name)
    {
        // 从配置文件加载扩展信息
        $configFile = __DIR__ . '/../../../config/extensions/common_extensions.php';
        if (file_exists($configFile)) {
            $extensions = require $configFile;
            if (isset($extensions[$name])) {
                return $extensions[$name];
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function install($phpVersion, array $options = [])
    {
        // 检查扩展是否已安装
        if ($this->isInstalled($phpVersion)) {
            throw new \Exception("扩展 {$this->getName()} 已经安装");
        }

        // 根据扩展类型选择安装方式
        switch ($this->getType()) {
            case 'builtin':
                return $this->installBuiltinExtension($phpVersion, $options);
            case 'pecl':
            default:
                return $this->installPeclExtension($phpVersion, $options);
        }
    }

    /**
     * 安装内置扩展
     *
     * @param string $phpVersion PHP版本
     * @param array $options 安装选项
     * @return bool
     * @throws \Exception 安装失败时抛出异常
     */
    private function installBuiltinExtension($phpVersion, array $options = [])
    {
        // 获取PHP二进制文件路径
        $phpBin = $this->getPhpBinary($phpVersion);

        // 检查PHP是否支持该扩展
        $output = [];
        exec($phpBin . ' -m | grep ' . $this->getName(), $output);

        if (empty($output)) {
            throw new \Exception("当前 PHP 版本不支持扩展 {$this->getName()}");
        }

        // 启用扩展
        $config = isset($options['config']) ? $options['config'] : $this->getDefaultConfig();
        return $this->enable($phpVersion, $config);
    }

    /**
     * 从 PECL 安装扩展
     *
     * @param string $phpVersion PHP版本
     * @param array $options 安装选项
     * @return bool
     * @throws \Exception 安装失败时抛出异常
     */
    private function installPeclExtension($phpVersion, array $options = [])
    {
        // 获取 PHP 版本的配置选项
        $phpConfig = dirname($this->getPhpBinary($phpVersion)) . '/php-config';
        if (!file_exists($phpConfig)) {
            $phpConfig = 'php-config';
        }

        // 获取镜像配置
        $mirrorConfig = new MirrorConfig();
        $mirror = isset($options['mirror']) ? $options['mirror'] : null;

        // 构建 pecl 命令
        $command = 'pecl install';

        // 添加镜像地址
        $peclUrl = $mirrorConfig->getPeclMirror($mirror);
        if ($peclUrl !== $mirrorConfig->getPeclMirror('official')) {
            $command .= ' -d preferred_mirror=' . escapeshellarg($peclUrl);
        }

        // 添加版本限制
        if (isset($options['version'])) {
            $command .= ' ' . $this->getName() . '-' . $options['version'];
        } else {
            $command .= ' ' . $this->getName();
        }

        // 添加其他选项
        if (isset($options['force']) && $options['force']) {
            $command .= ' --force';
        }

        // 执行安装命令
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("安装扩展 {$this->getName()} 失败: " . implode("\n", $output));
        }

        // 启用扩展
        $config = isset($options['config']) ? $options['config'] : $this->getDefaultConfig();
        return $this->enable($phpVersion, $config);
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
        if ($this->getType() === 'builtin') {
            if (isset($options['disable']) && $options['disable']) {
                return $this->disable($phpVersion);
            } else {
                throw new \Exception("无法删除内置扩展 {$this->getName()}，只能禁用");
            }
        }

        // 删除扩展配置
        $config = $this->getConfig($phpVersion);
        $config->removeExtensionConfig($this->getName());

        // 如果是 PECL 扩展，则使用 pecl uninstall 命令删除
        if ($this->getType() === 'pecl') {
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
