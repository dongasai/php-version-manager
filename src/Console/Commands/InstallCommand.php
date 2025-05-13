<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionInstaller;
use VersionManager\Core\VersionDetector;
use Exception;

/**
 * 安装命令类
 *
 * 用于处理PHP版本安装命令
 */
class InstallCommand implements CommandInterface
{
    /**
     * 版本安装器
     *
     * @var VersionInstaller
     */
    private $installer;

    /**
     * 版本检测器
     *
     * @var VersionDetector
     */
    private $detector;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->installer = new VersionInstaller();
        $this->detector = new VersionDetector();
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定PHP版本" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }

        // 先解析所有选项，包括第一个参数
        $options = $this->parseOptions($args);

        // 提取非选项参数作为版本
        $version = null;
        foreach ($args as $arg) {
            // 如果参数不是以'-'开头，则认为是版本
            if (substr($arg, 0, 1) !== '-') {
                $version = $arg;
                break;
            }
        }

        // 如果没有找到版本参数
        if ($version === null) {
            echo "错误: 请指定PHP版本" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }

        // 检查版本是否有效
        $availableVersions = $this->detector->getAvailableVersions();

        // 如果版本只指定了主版本和次版本，如"7.1"，则自动匹配到最新的修订版
        if (!in_array($version, $availableVersions)) {
            // 尝试匹配到最新的修订版
            $matchedVersion = $this->matchLatestPatchVersion($version, $availableVersions);

            if ($matchedVersion) {
                echo "自动匹配到PHP版本 {$matchedVersion}" . PHP_EOL;
                $version = $matchedVersion;
            } else {
                echo "错误: 无效的PHP版本 {$version}" . PHP_EOL;
                echo "可用的PHP版本: " . implode(', ', $availableVersions) . PHP_EOL;
                return 1;
            }
        }

        // 检查版本是否已安装
        if ($this->installer->isVersionInstalled($version)) {
            echo "PHP版本 {$version} 已安装" . PHP_EOL;
            return 0;
        }

        try {
            // 安装PHP版本
            $this->installer->install($version, $options);
            echo "PHP版本 {$version} 安装成功" . PHP_EOL;
            return 0;
        } catch (Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
            return 1;
        }
    }

    /**
     * 解析命令选项
     *
     * @param array $args 命令参数
     * @return array 选项数组
     */
    private function parseOptions(array $args)
    {
        $options = [
            'from_source' => false,
            'keep_source' => false,
            'keep_binary' => false,
            'use_cache' => true,
            'use_multi_thread' => true,
            'thread_count' => 4,
            'verify_signature' => true,
            'yes' => false,  // 自动确认选项
            'configure_options' => []
        ];

        foreach ($args as $arg) {
            // 处理长选项 (以--开头)
            if (strpos($arg, '--') === 0) {
                if ($arg === '--from-source') {
                    $options['from_source'] = true;
                } elseif ($arg === '--keep-source') {
                    $options['keep_source'] = true;
                } elseif ($arg === '--keep-binary') {
                    $options['keep_binary'] = true;
                } elseif ($arg === '--no-cache') {
                    $options['use_cache'] = false;
                } elseif ($arg === '--no-multi-thread') {
                    $options['use_multi_thread'] = false;
                } elseif ($arg === '--no-verify') {
                    $options['verify_signature'] = false;
                } elseif ($arg === '--yes') {
                    $options['yes'] = true;
                } elseif (strpos($arg, '--threads=') === 0) {
                    $threadCount = (int) substr($arg, 10);
                    if ($threadCount > 0) {
                        $options['thread_count'] = $threadCount;
                    }
                } elseif (strpos($arg, '--with-') === 0 || strpos($arg, '--enable-') === 0 || strpos($arg, '--disable-') === 0) {
                    $options['configure_options'][] = $arg;
                }
            }
            // 处理短选项 (以-开头)
            elseif (strpos($arg, '-') === 0) {
                $shortOption = substr($arg, 1);
                // 处理组合的短选项，如 -fy
                for ($i = 0; $i < strlen($shortOption); $i++) {
                    $option = $shortOption[$i];
                    switch ($option) {
                        case 'y':
                            $options['yes'] = true;
                            break;
                        case 'f':
                            $options['from_source'] = true;
                            break;
                        case 'k':
                            $options['keep_source'] = true;
                            break;
                        case 'b':
                            $options['keep_binary'] = true;
                            break;
                        case 'n':
                            $options['use_cache'] = false;
                            break;
                        case 'm':
                            $options['use_multi_thread'] = false;
                            break;
                        case 'v':
                            $options['verify_signature'] = false;
                            break;
                    }
                }
            }
        }

        return $options;
    }

    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '安装指定版本的PHP';
    }

    /**
     * 匹配最新的修订版本
     *
     * @param string $version 版本号（可能只有主版本和次版本，如"7.1"）
     * @param array $availableVersions 可用的版本列表
     * @return string|null 匹配到的版本，如果没有匹配则返回null
     */
    private function matchLatestPatchVersion($version, array $availableVersions)
    {
        // 如果版本已经是完整的三段式版本号，则直接返回
        if (preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            return in_array($version, $availableVersions) ? $version : null;
        }

        // 如果版本只有主版本和次版本，如"7.1"
        if (preg_match('/^(\d+)\.(\d+)$/', $version, $matches)) {
            $major = $matches[1];
            $minor = $matches[2];
            $pattern = "/^{$major}\.{$minor}\.\d+$/";

            // 过滤出匹配的版本
            $matchedVersions = [];
            foreach ($availableVersions as $availableVersion) {
                if (preg_match($pattern, $availableVersion)) {
                    $matchedVersions[] = $availableVersion;
                }
            }

            // 如果没有匹配的版本，返回null
            if (empty($matchedVersions)) {
                return null;
            }

            // 按版本号排序，找到最新的版本
            usort($matchedVersions, 'version_compare');
            return end($matchedVersions);
        }

        // 如果只有主版本，如"7"
        if (preg_match('/^(\d+)$/', $version, $matches)) {
            $major = $matches[1];
            $pattern = "/^{$major}\.\d+\.\d+$/";

            // 过滤出匹配的版本
            $matchedVersions = [];
            foreach ($availableVersions as $availableVersion) {
                if (preg_match($pattern, $availableVersion)) {
                    $matchedVersions[] = $availableVersion;
                }
            }

            // 如果没有匹配的版本，返回null
            if (empty($matchedVersions)) {
                return null;
            }

            // 按版本号排序，找到最新的版本
            usort($matchedVersions, 'version_compare');
            return end($matchedVersions);
        }

        return null;
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm install <版本> [选项]

安装指定版本的PHP。

参数:
  <版本>  要安装的PHP版本，例如 7.4.33, 8.1.27
          可以只指定主版本和次版本，如 7.1，将自动匹配到最新的修订版

选项:
  -y, --yes           自动确认所有提示
  --from-source, -f   从源码编译安装
  --keep-source, -k   保留源码
  --keep-binary, -b   保留二进制包
  --no-cache, -n      不使用缓存
  --no-multi-thread, -m 不使用多线程下载
  --no-verify, -v     不验证签名
  --threads=<数量>    设置下载线程数量，默认为4
  --with-*            传递给configure的选项
  --enable-*          传递给configure的选项
  --disable-*         传递给configure的选项

示例:
  pvm install 7.4.33
  pvm install 8.1.27 --from-source
  pvm install 8.1.27 --from-source --with-openssl
  pvm install 8.1.27 --no-cache
  pvm install 8.1.27 --threads=8
  pvm install -y 7.1
  pvm install 7.1 -y
USAGE;
    }
}
