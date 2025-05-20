<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionSwitcher;
use VersionManager\Core\VersionDetector;
use Exception;

/**
 * 使用命令类
 *
 * 用于处理PHP版本切换命令
 */
class UseCommand implements CommandInterface
{
    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $switcher;

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
        $this->switcher = new VersionSwitcher();
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
            // 显示当前版本
            $currentVersion = $this->switcher->getCurrentVersion();
            if ($currentVersion) {
                echo "当前PHP版本: {$currentVersion}" . PHP_EOL;
                return 0;
            } else {
                echo "未设置PHP版本" . PHP_EOL;
                return 1;
            }
        }

        $version = $args[0];
        $options = $this->parseOptions(array_slice($args, 1));

        // 检查版本是否已安装
        if (!$this->switcher->isVersionInstalled($version)) {
            echo "错误: PHP版本 {$version} 未安装" . PHP_EOL;
            echo "使用 'pvm install {$version}' 安装此版本" . PHP_EOL;
            return 1;
        }

        try {
            if ($options['project']) {
                // 设置项目级别的PHP版本
                $projectDir = $options['project_dir'] ?: getcwd();
                $this->switcher->setProjectVersion($version, $projectDir);
                echo "已将项目 {$projectDir} 的PHP版本设置为 {$version}" . PHP_EOL;
            } else {
                // 切换PHP版本
                $this->switcher->switchVersion($version, $options['global']);
                echo "已切换到PHP版本 {$version}" . PHP_EOL;

                if ($options['global']) {
                    echo "已将全局PHP版本设置为 {$version}" . PHP_EOL;
                }
            }

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
            'global' => false,
            'project' => false,
            'project_dir' => null
        ];

        foreach ($args as $i => $arg) {
            if ($arg === '--global' || $arg === '-g') {
                $options['global'] = true;
            } elseif ($arg === '--project' || $arg === '-p') {
                $options['project'] = true;

                // 检查下一个参数是否是项目目录
                if (isset($args[$i + 1]) && $args[$i + 1][0] !== '-') {
                    $options['project_dir'] = $args[$i + 1];
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
        return '永久切换PHP版本';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm use [版本] [选项]

永久切换PHP版本。如果不指定版本，则显示当前版本。
此命令会修改系统环境变量和符号链接，切换后的PHP版本将在所有终端会话中生效。

参数:
  [版本]  要切换到的PHP版本，例如 7.4.33, 8.1.27

选项:
  --global, -g     设置为全局PHP版本
  --project, -p    设置为项目级别的PHP版本
  --project=DIR    指定项目目录

示例:
  pvm use                  # 显示当前版本
  pvm use 7.4.33           # 切换到PHP 7.4.33
  pvm use 8.1.27 --global  # 切换到PHP 8.1.27并设置为全局版本
  pvm use 8.1.27 --project # 设置当前项目的PHP版本为8.1.27
USAGE;
    }
}
