<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionDetector;
use VersionManager\Core\VersionSwitcher;
use VersionManager\Core\Config\PvmMirrorConfig;

use VersionManager\Core\Logger\Logger;

/**
 * 状态命令类
 *
 * 用于显示PVM程序本身的状态信息
 */
class StatusCommand implements CommandInterface
{
    /**
     * 版本检测器
     *
     * @var VersionDetector
     */
    private $detector;

    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $switcher;

    /**
     * PVM镜像配置
     *
     * @var PvmMirrorConfig
     */
    private $pvmMirrorConfig;



    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->detector = new VersionDetector();
        $this->switcher = new VersionSwitcher();
        $this->pvmMirrorConfig = new PvmMirrorConfig();
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 解析参数
        $showVerbose = in_array('--verbose', $args) || in_array('-v', $args);
        $showJson = in_array('--json', $args);

        if ($showJson) {
            return $this->showJsonStatus();
        }

        return $this->showTextStatus($showVerbose);
    }

    /**
     * 显示文本格式的状态信息
     *
     * @param bool $verbose 是否显示详细信息
     * @return int
     */
    private function showTextStatus($verbose = false)
    {
        Logger::info("PVM 状态信息", "\033[36m");
        echo "==================\n\n";

        // 1. PVM基本信息
        $this->showBasicInfo();

        // 2. PHP版本信息
        $this->showPhpVersionInfo();

        // 3. 镜像配置信息
        $this->showMirrorInfo($verbose);

        // 4. 目录信息
        $this->showDirectoryInfo();

        // 5. 配置文件信息
        if ($verbose) {
            $this->showConfigFileInfo();
        }

        return 0;
    }

    /**
     * 显示JSON格式的状态信息
     *
     * @return int
     */
    private function showJsonStatus()
    {
        $status = [
            'pvm' => $this->getBasicInfo(),
            'php' => $this->getPhpVersionInfo(),
            'mirror' => $this->getMirrorInfo(),
            'directories' => $this->getDirectoryInfo(),
            'config_files' => $this->getConfigFileInfo()
        ];

        echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        return 0;
    }

    /**
     * 显示基本信息
     */
    private function showBasicInfo()
    {
        Logger::info("基本信息:", "\033[33m");

        $version = defined('VersionManager\Console\Application::VERSION') ?
                   \VersionManager\Console\Application::VERSION : '1.0.0';

        echo "  PVM版本: {$version}\n";
        echo "  运行环境: " . PHP_OS . " " . php_uname('m') . "\n";
        echo "  PHP版本: " . PHP_VERSION . "\n";
        echo "  用户目录: " . getenv('HOME') . "\n";
        echo "  PVM目录: " . getenv('HOME') . "/.pvm\n";

        // 检查PVM是否已初始化
        $pvmDir = getenv('HOME') . '/.pvm';
        $isInitialized = is_dir($pvmDir) && is_dir($pvmDir . '/versions') && is_dir($pvmDir . '/config');
        echo "  初始化状态: " . ($isInitialized ? '已初始化' : '未初始化') . "\n";

        echo "\n";
    }

    /**
     * 显示PHP版本信息
     */
    private function showPhpVersionInfo()
    {
        Logger::info("PHP版本信息:", "\033[33m");

        $installedVersions = $this->detector->getInstalledVersions();
        $currentVersion = $this->switcher->getCurrentVersion();
        $globalVersion = $this->switcher->getGlobalVersion();
        $systemVersion = $this->detector->getCurrentVersion();

        echo "  已安装版本数: " . count($installedVersions) . "\n";

        if (!empty($installedVersions)) {
            echo "  已安装版本: " . implode(', ', $installedVersions) . "\n";
        }

        echo "  当前版本: " . ($currentVersion ?: '未设置') . "\n";
        echo "  全局版本: " . ($globalVersion ?: '未设置') . "\n";
        echo "  系统版本: {$systemVersion}\n";

        echo "\n";
    }

    /**
     * 显示镜像信息
     *
     * @param bool $verbose 是否显示详细信息
     */
    private function showMirrorInfo($verbose = false)
    {
        Logger::info("镜像配置:", "\033[33m");

        // PVM镜像源状态
        $pvmEnabled = $this->pvmMirrorConfig->isEnabled();
        echo "  PVM镜像源: " . ($pvmEnabled ? '启用' : '禁用') . "\n";

        if ($pvmEnabled) {
            echo "  主镜像地址: " . $this->pvmMirrorConfig->getMirrorUrl() . "\n";

            $fallbacks = $this->pvmMirrorConfig->getFallbackMirrors();
            if (!empty($fallbacks)) {
                echo "  备用镜像数: " . count($fallbacks) . "\n";
                if ($verbose) {
                    foreach ($fallbacks as $i => $url) {
                        echo "    " . ($i + 1) . ". {$url}\n";
                    }
                }
            }
        }

        // 注意：传统镜像配置功能已废弃
        echo "  传统镜像配置: 已废弃\n";

        echo "\n";
    }

    /**
     * 显示目录信息
     */
    private function showDirectoryInfo()
    {
        Logger::info("目录信息:", "\033[33m");

        $homeDir = getenv('HOME');
        $pvmDir = $homeDir . '/.pvm';

        $directories = [
            'PVM根目录' => $pvmDir,
            'PHP版本目录' => $pvmDir . '/versions',
            '配置目录' => $pvmDir . '/config',
            '缓存目录' => $pvmDir . '/cache',
            '日志目录' => $pvmDir . '/logs',
        ];

        foreach ($directories as $name => $path) {
            $exists = is_dir($path);
            $status = $exists ? '存在' : '不存在';

            if ($exists) {
                $size = $this->getDirectorySize($path);
                echo "  {$name}: {$status} ({$size})\n";
            } else {
                echo "  {$name}: {$status}\n";
            }
        }

        echo "\n";
    }

    /**
     * 显示配置文件信息
     */
    private function showConfigFileInfo()
    {
        Logger::info("配置文件:", "\033[33m");

        $homeDir = getenv('HOME');
        $configFiles = [
            'PVM镜像配置' => $homeDir . '/.pvm/config/pvm-mirror.php',
            '镜像配置' => $homeDir . '/.pvm/config/mirror.php',
            '全局配置' => $homeDir . '/.pvm/config/global.php',
            '环境配置' => $homeDir . '/.pvm/config/env.php',
        ];

        foreach ($configFiles as $name => $path) {
            $exists = file_exists($path);
            $status = $exists ? '存在' : '不存在';

            if ($exists) {
                $size = $this->formatSize(filesize($path));
                $mtime = date('Y-m-d H:i:s', filemtime($path));
                echo "  {$name}: {$status} ({$size}, 修改时间: {$mtime})\n";
            } else {
                echo "  {$name}: {$status}\n";
            }
        }

        echo "\n";
    }

    /**
     * 获取基本信息（用于JSON输出）
     *
     * @return array
     */
    private function getBasicInfo()
    {
        $version = defined('VersionManager\Console\Application::VERSION') ?
                   \VersionManager\Console\Application::VERSION : '1.0.0';

        $pvmDir = getenv('HOME') . '/.pvm';
        $isInitialized = is_dir($pvmDir) && is_dir($pvmDir . '/versions') && is_dir($pvmDir . '/config');

        return [
            'version' => $version,
            'os' => PHP_OS . ' ' . php_uname('m'),
            'php_version' => PHP_VERSION,
            'home_directory' => getenv('HOME'),
            'pvm_directory' => $pvmDir,
            'initialized' => $isInitialized
        ];
    }

    /**
     * 获取PHP版本信息（用于JSON输出）
     *
     * @return array
     */
    private function getPhpVersionInfo()
    {
        $installedVersions = $this->detector->getInstalledVersions();
        $currentVersion = $this->switcher->getCurrentVersion();
        $globalVersion = $this->switcher->getGlobalVersion();
        $systemVersion = $this->detector->getCurrentVersion();

        return [
            'installed_count' => count($installedVersions),
            'installed_versions' => $installedVersions,
            'current_version' => $currentVersion,
            'global_version' => $globalVersion,
            'system_version' => $systemVersion
        ];
    }

    /**
     * 获取镜像信息（用于JSON输出）
     *
     * @return array
     */
    private function getMirrorInfo()
    {
        $pvmEnabled = $this->pvmMirrorConfig->isEnabled();

        $info = [
            'pvm_mirror' => [
                'enabled' => $pvmEnabled,
                'main_url' => $pvmEnabled ? $this->pvmMirrorConfig->getMirrorUrl() : null,
                'fallback_mirrors' => $pvmEnabled ? $this->pvmMirrorConfig->getFallbackMirrors() : []
            ],
            'traditional_mirrors' => 'deprecated'
        ];

        return $info;
    }

    /**
     * 获取目录信息（用于JSON输出）
     *
     * @return array
     */
    private function getDirectoryInfo()
    {
        $homeDir = getenv('HOME');
        $pvmDir = $homeDir . '/.pvm';

        $directories = [
            'pvm_root' => $pvmDir,
            'versions' => $pvmDir . '/versions',
            'config' => $pvmDir . '/config',
            'cache' => $pvmDir . '/cache',
            'logs' => $pvmDir . '/logs',
        ];

        $info = [];
        foreach ($directories as $key => $path) {
            $exists = is_dir($path);
            $info[$key] = [
                'path' => $path,
                'exists' => $exists,
                'size' => $exists ? $this->getDirectorySize($path, false) : null
            ];
        }

        return $info;
    }

    /**
     * 获取配置文件信息（用于JSON输出）
     *
     * @return array
     */
    private function getConfigFileInfo()
    {
        $homeDir = getenv('HOME');
        $configFiles = [
            'pvm_mirror' => $homeDir . '/.pvm/config/pvm-mirror.php',
            'mirror' => $homeDir . '/.pvm/config/mirror.php',
            'global' => $homeDir . '/.pvm/config/global.php',
            'env' => $homeDir . '/.pvm/config/env.php',
        ];

        $info = [];
        foreach ($configFiles as $key => $path) {
            $exists = file_exists($path);
            $info[$key] = [
                'path' => $path,
                'exists' => $exists,
                'size' => $exists ? filesize($path) : null,
                'modified_time' => $exists ? filemtime($path) : null
            ];
        }

        return $info;
    }

    /**
     * 获取目录大小
     *
     * @param string $directory 目录路径
     * @param bool $formatted 是否格式化输出
     * @return string|int
     */
    private function getDirectorySize($directory, $formatted = true)
    {
        if (!is_dir($directory)) {
            return $formatted ? '0 B' : 0;
        }

        $size = 0;
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            // 如果无法访问目录，返回0
            $size = 0;
        }

        return $formatted ? $this->formatSize($size) : $size;
    }

    /**
     * 格式化文件大小
     *
     * @param int $bytes 字节数
     * @return string
     */
    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '显示PVM程序状态信息';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm status [选项]

显示PVM程序本身的状态信息，包括版本、镜像配置、已安装PHP版本等。

选项:
  --verbose, -v           显示详细信息
  --json                  以JSON格式输出

示例:
  pvm status              显示基本状态信息
  pvm status --verbose    显示详细状态信息
  pvm status --json       以JSON格式输出状态信息
USAGE;
    }
}