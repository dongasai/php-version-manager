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

        $version = $args[0];
        $options = $this->parseOptions(array_slice($args, 1));

        // 检查版本是否有效
        $availableVersions = $this->detector->getAvailableVersions();
        if (!in_array($version, $availableVersions)) {
            echo "错误: 无效的PHP版本 {$version}" . PHP_EOL;
            echo "可用的PHP版本: " . implode(', ', $availableVersions) . PHP_EOL;
            return 1;
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
            'configure_options' => []
        ];

        foreach ($args as $arg) {
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
            } elseif (strpos($arg, '--threads=') === 0) {
                $threadCount = (int) substr($arg, 10);
                if ($threadCount > 0) {
                    $options['thread_count'] = $threadCount;
                }
            } elseif (strpos($arg, '--with-') === 0 || strpos($arg, '--enable-') === 0 || strpos($arg, '--disable-') === 0) {
                $options['configure_options'][] = $arg;
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

选项:
  --from-source       从源码编译安装
  --keep-source       保留源码
  --keep-binary       保留二进制包
  --no-cache          不使用缓存
  --no-multi-thread   不使用多线程下载
  --no-verify         不验证签名
  --threads=<数量>    设置下载线程数量，默认为4
  --with-*            传递给configure的选项
  --enable-*          传阒给configure的选项
  --disable-*         传递给configure的选项

示例:
  pvm install 7.4.33
  pvm install 8.1.27 --from-source
  pvm install 8.1.27 --from-source --with-openssl
  pvm install 8.1.27 --no-cache
  pvm install 8.1.27 --threads=8
USAGE;
    }
}
