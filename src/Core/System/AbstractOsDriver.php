<?php

namespace VersionManager\Core\System;

/**
 * 操作系统驱动抽象基类
 *
 * 实现一些通用功能
 */
abstract class AbstractOsDriver implements OsDriverInterface
{
    /**
     * 操作系统名称
     *
     * @var string
     */
    protected $name;

    /**
     * 操作系统描述
     *
     * @var string
     */
    protected $description;

    /**
     * 操作系统版本
     *
     * @var string
     */
    protected $version;

    /**
     * 操作系统架构
     *
     * @var string
     */
    protected $arch;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->detectSystemInfo();
    }

    /**
     * 检测系统信息
     */
    protected function detectSystemInfo()
    {
        // 默认架构
        $this->arch = php_uname('m');

        // 检测操作系统信息
        $this->detectOsInfo();
    }

    /**
     * 检测操作系统信息
     * 子类可以重写此方法来提供特定的检测逻辑
     */
    protected function detectOsInfo()
    {
        // 默认实现，检测通用Linux信息
        $this->name = 'linux';
        $this->description = 'Generic Linux';
        $this->version = php_uname('r');

        // 尝试从/etc/os-release获取信息
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID'])) {
                $this->name = strtolower($osRelease['ID']);
            }

            if (isset($osRelease['PRETTY_NAME'])) {
                $this->description = $osRelease['PRETTY_NAME'];
            }

            if (isset($osRelease['VERSION_ID'])) {
                $this->version = $osRelease['VERSION_ID'];
            }
        }
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
    public function getArch()
    {
        return $this->arch;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'version' => $this->getVersion(),
            'arch' => $this->getArch(),
            'kernel' => php_uname('r'),
            'hostname' => php_uname('n'),
        ];
    }

    /**
     * 执行命令
     *
     * @param string $command 要执行的命令
     * @param array $output 输出结果
     * @param int $returnCode 返回代码
     * @return bool 是否执行成功
     */
    protected function executeCommand($command, &$output = null, &$returnCode = null)
    {
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * 检查命令是否存在
     *
     * @param string $command 命令名称
     * @return bool 是否存在
     */
    protected function commandExists($command)
    {
        $output = [];
        $returnCode = 0;

        exec("which {$command} 2>/dev/null", $output, $returnCode);

        return $returnCode === 0;
    }
}
