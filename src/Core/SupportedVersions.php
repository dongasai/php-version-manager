<?php

namespace VersionManager\Core;

/**
 * 支持的PHP版本管理类
 *
 * 用于管理不同Linux发行版和处理器架构上支持的PHP版本
 */
class SupportedVersions
{
    /**
     * 支持级别常量
     */
    const SUPPORT_FULL = 'full';       // 完全支持
    const SUPPORT_PARTIAL = 'partial'; // 部分支持
    const SUPPORT_NONE = 'none';       // 不支持
    const SUPPORT_UNTESTED = 'untested'; // 待测试

    /**
     * 架构常量
     */
    const ARCH_X86_64 = 'x86_64';  // x86_64 (AMD64)
    const ARCH_ARM64 = 'aarch64';  // ARM64 (AArch64)
    const ARCH_ARMV7 = 'armv7l';   // ARMv7 (armhf)

    /**
     * 发行版常量
     */
    const DISTRO_UBUNTU = 'ubuntu';
    const DISTRO_DEBIAN = 'debian';
    const DISTRO_CENTOS = 'centos';
    const DISTRO_RHEL = 'rhel';
    const DISTRO_FEDORA = 'fedora';
    const DISTRO_ALPINE = 'alpine';
    const DISTRO_RASPBIAN = 'raspbian';

    /**
     * 支持的PHP版本列表
     *
     * 格式：[架构][发行版][发行版版本][PHP版本] = 支持级别
     *
     * @var array
     */
    private $supportedVersions = [];

    /**
     * 已知问题列表
     *
     * 格式：[PHP版本] = 问题描述
     *
     * @var array
     */
    private $knownIssues = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->loadSupportedVersions();
        $this->loadKnownIssues();
    }

    /**
     * 加载支持的版本配置
     */
    private function loadSupportedVersions()
    {
        // 架构映射
        $archMap = [
            self::ARCH_X86_64 => 'x86_64',
            self::ARCH_ARM64 => 'aarch64',
            self::ARCH_ARMV7 => 'armv7l',
        ];

        // 加载每个架构的配置
        foreach ($archMap as $archKey => $archFile) {
            $configFile = __DIR__ . '/../../config/supported_versions/' . $archFile . '.php';
            if (file_exists($configFile)) {
                $config = require $configFile;

                // 将支持级别字符串转换为常量
                foreach ($config as $distro => $distroVersions) {
                    foreach ($distroVersions as $distroVersion => $phpVersions) {
                        foreach ($phpVersions as $phpVersion => $supportLevel) {
                            $supportLevelConstant = $this->getSupportLevelConstant($supportLevel);
                            $this->supportedVersions[$archKey][$distro][$distroVersion][$phpVersion] = $supportLevelConstant;
                        }
                    }
                }
            }
        }
    }

    /**
     * 加载已知问题配置
     */
    private function loadKnownIssues()
    {
        $configFile = __DIR__ . '/../../config/supported_versions/known_issues.php';
        if (file_exists($configFile)) {
            $this->knownIssues = require $configFile;
        }
    }

    /**
     * 获取支持级别常量
     *
     * @param string $level 支持级别字符串
     * @return string 支持级别常量
     */
    private function getSupportLevelConstant($level)
    {
        $map = [
            'full' => self::SUPPORT_FULL,
            'partial' => self::SUPPORT_PARTIAL,
            'none' => self::SUPPORT_NONE,
            'untested' => self::SUPPORT_UNTESTED,
        ];

        return isset($map[$level]) ? $map[$level] : self::SUPPORT_UNTESTED;
    }

    /**
     * 获取当前系统的架构
     *
     * @return string 架构名称
     */
    public function getSystemArchitecture()
    {
        $arch = php_uname('m');

        // 映射架构名称
        $archMap = [
            'x86_64' => self::ARCH_X86_64,
            'amd64' => self::ARCH_X86_64,
            'aarch64' => self::ARCH_ARM64,
            'arm64' => self::ARCH_ARM64,
            'armv7l' => self::ARCH_ARMV7,
            'armv7' => self::ARCH_ARMV7,
        ];

        return isset($archMap[$arch]) ? $archMap[$arch] : $arch;
    }

    /**
     * 获取当前系统的发行版
     *
     * @return array [发行版名称, 发行版版本]
     */
    public function getSystemDistribution()
    {
        $distro = '';
        $version = '';

        // 读取/etc/os-release文件
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID'])) {
                $distro = strtolower($osRelease['ID']);
            }

            if (isset($osRelease['VERSION_ID'])) {
                $version = $osRelease['VERSION_ID'];
            }
        }

        // 检查是否是Raspberry Pi OS
        if ($distro === 'debian' && file_exists('/etc/rpi-issue')) {
            $distro = self::DISTRO_RASPBIAN;

            // 确定Raspberry Pi OS版本
            if (strpos(file_get_contents('/etc/debian_version'), '10') === 0) {
                $version = 'buster';
            } elseif (strpos(file_get_contents('/etc/debian_version'), '11') === 0) {
                $version = 'bullseye';
            }
        }

        // 映射发行版名称
        $distroMap = [
            'ubuntu' => self::DISTRO_UBUNTU,
            'debian' => self::DISTRO_DEBIAN,
            'centos' => self::DISTRO_CENTOS,
            'rhel' => self::DISTRO_RHEL,
            'fedora' => self::DISTRO_FEDORA,
            'alpine' => self::DISTRO_ALPINE,
        ];

        $distro = isset($distroMap[$distro]) ? $distroMap[$distro] : $distro;

        return [$distro, $version];
    }

    /**
     * 获取指定PHP版本在当前系统上的支持级别
     *
     * @param string $phpVersion PHP版本
     * @return string 支持级别
     */
    public function getSupportLevel($phpVersion)
    {
        // 提取主版本号和次版本号
        $versionParts = explode('.', $phpVersion);
        $majorMinor = $versionParts[0] . '.' . $versionParts[1];

        // 获取系统信息
        $arch = $this->getSystemArchitecture();
        list($distro, $version) = $this->getSystemDistribution();

        // 检查是否支持
        if (isset($this->supportedVersions[$arch][$distro][$version][$majorMinor])) {
            return $this->supportedVersions[$arch][$distro][$version][$majorMinor];
        }

        // 默认为待测试
        return self::SUPPORT_UNTESTED;
    }

    /**
     * 获取指定PHP版本的已知问题
     *
     * @param string $phpVersion PHP版本
     * @return array 已知问题列表
     */
    public function getKnownIssues($phpVersion)
    {
        // 提取主版本号和次版本号
        $versionParts = explode('.', $phpVersion);
        $majorMinor = $versionParts[0] . '.' . $versionParts[1];

        // 检查是否有已知问题
        if (isset($this->knownIssues[$majorMinor])) {
            return $this->knownIssues[$majorMinor];
        }

        return [];
    }

    /**
     * 获取当前系统支持的PHP版本列表
     *
     * @return array [版本 => 支持级别]
     */
    public function getSupportedVersionsForCurrentSystem()
    {
        // 获取系统信息
        $arch = $this->getSystemArchitecture();
        list($distro, $version) = $this->getSystemDistribution();

        // 检查是否有支持信息
        if (isset($this->supportedVersions[$arch][$distro][$version])) {
            return $this->supportedVersions[$arch][$distro][$version];
        }

        // 默认支持的版本
        return [
            '8.1' => self::SUPPORT_UNTESTED,
            '8.0' => self::SUPPORT_UNTESTED,
            '7.4' => self::SUPPORT_UNTESTED,
            '7.3' => self::SUPPORT_UNTESTED,
            '7.2' => self::SUPPORT_UNTESTED,
            '7.1' => self::SUPPORT_UNTESTED,
            '5.6' => self::SUPPORT_UNTESTED,
            '5.5' => self::SUPPORT_UNTESTED,
            '5.4' => self::SUPPORT_UNTESTED,
        ];
    }

    /**
     * 获取支持级别的描述
     *
     * @param string $level 支持级别
     * @return string 支持级别描述
     */
    public function getSupportLevelDescription($level)
    {
        $descriptions = [
            self::SUPPORT_FULL => '完全支持',
            self::SUPPORT_PARTIAL => '部分支持',
            self::SUPPORT_NONE => '不支持',
            self::SUPPORT_UNTESTED => '待测试',
        ];

        return isset($descriptions[$level]) ? $descriptions[$level] : '未知';
    }

    /**
     * 检查指定PHP版本是否建议在当前系统上使用
     *
     * @param string $phpVersion PHP版本
     * @return bool 是否建议使用
     */
    public function isRecommended($phpVersion)
    {
        $supportLevel = $this->getSupportLevel($phpVersion);
        return $supportLevel === self::SUPPORT_FULL || $supportLevel === self::SUPPORT_PARTIAL;
    }
}
