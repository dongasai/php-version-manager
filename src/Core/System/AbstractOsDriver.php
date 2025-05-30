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
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        $tags = [];

        // 添加操作系统类型标签
        if ($this->name) {
            $tags[] = strtolower($this->name);

            // 添加操作系统家族标签
            if (in_array($this->name, ['ubuntu', 'debian'])) {
                $tags[] = 'debian-based';
            } elseif (in_array($this->name, ['centos', 'fedora', 'rhel'])) {
                $tags[] = 'rhel-based';
            }

            // 添加版本标签
            if ($this->version) {
                $tags[] = strtolower($this->name) . '-' . $this->version;
            }
        }

        // 添加架构标签
        if ($this->arch) {
            $tags[] = strtolower($this->arch);

            // 添加架构家族标签
            if (in_array($this->arch, ['x86_64', 'amd64'])) {
                $tags[] = 'x86-family';
            } elseif (in_array($this->arch, ['arm', 'arm64', 'aarch64'])) {
                $tags[] = 'arm-family';
            }
        }

        return $tags;
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

    /**
     * {@inheritdoc}
     */
    public function installPackages(array $packages, array $options = [])
    {
        // 默认实现，子类应该重写此方法
        throw new \Exception("包安装功能未在 " . get_class($this) . " 中实现");
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackageCache(array $options = [])
    {
        // 默认实现，子类应该重写此方法
        throw new \Exception("包缓存更新功能未在 " . get_class($this) . " 中实现");
    }

    /**
     * {@inheritdoc}
     */
    public function isPackageInstalled($package)
    {
        // 默认实现，子类应该重写此方法
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSudoAccess()
    {
        // 检查是否是root用户
        if (posix_getuid() === 0) {
            return true;
        }

        // 检查sudo命令是否存在
        if (!$this->commandExists('sudo')) {
            return false;
        }

        // 尝试执行sudo -n true来检查是否有无密码sudo权限
        $output = [];
        $returnCode = 0;
        exec('sudo -n true 2>/dev/null', $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function executeWithPrivileges($command, array $options = [])
    {
        $output = [];
        $returnCode = 0;

        // 如果已经是root用户，直接执行
        if (posix_getuid() === 0) {
            exec($command . ' 2>&1', $output, $returnCode);
            return [$output, $returnCode];
        }

        // 尝试使用sudo执行
        if ($this->commandExists('sudo')) {
            $sudoCommand = 'sudo ' . $command;
            exec($sudoCommand . ' 2>&1', $output, $returnCode);
            return [$output, $returnCode];
        }

        // 如果没有sudo，尝试使用su
        if ($this->commandExists('su')) {
            $suCommand = 'su -c "' . addslashes($command) . '"';
            exec($suCommand . ' 2>&1', $output, $returnCode);
            return [$output, $returnCode];
        }

        // 如果都没有，直接执行（可能会失败）
        exec($command . ' 2>&1', $output, $returnCode);
        return [$output, $returnCode];
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageManager()
    {
        // 默认实现，子类应该重写此方法
        return 'unknown';
    }
}
