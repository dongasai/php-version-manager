<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Console\UI\ConsoleUI;
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
     * 控制台UI工具
     *
     * @var ConsoleUI
     */
    private $ui;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->switcher = new VersionSwitcher();
        $this->detector = new VersionDetector();
        $this->ui = new ConsoleUI();
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
            // 进入交互选择模式
            return $this->interactiveVersionSelection();
        }

        // 检查是否是帮助参数
        if ($args[0] === '--help' || $args[0] === '-h') {
            echo $this->getUsage() . PHP_EOL;
            return 0;
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
     * 交互式版本选择
     *
     * @return int 返回状态码
     */
    private function interactiveVersionSelection()
    {
        try {
            // 显示当前版本信息
            $currentVersion = $this->switcher->getCurrentVersion();
            $this->ui->info('当前PHP版本: ' . $this->ui->colorize($currentVersion ?: '未设置', 'green'), true);
            $this->ui->info('');

            // 获取已安装的版本
            $installedVersions = $this->getSimpleInstalledVersions();

            if (empty($installedVersions)) {
                $this->ui->error('没有已安装的PHP版本');
                $this->ui->info('使用 "pvm install <版本>" 安装PHP版本');
                return 1;
            }

            // 显示版本选择菜单
            $this->ui->info('选择要切换的PHP版本:', true);
            $versionOptions = [];
            foreach ($installedVersions as $version) {
                $isCurrent = ($version === $currentVersion);
                $versionOptions[$version] = "PHP {$version}" . ($isCurrent ? ' (当前)' : '');
            }

            $selectedVersion = $this->ui->select($versionOptions, '请选择版本:', false);

            if (!$selectedVersion) {
                $this->ui->info('已取消操作');
                return 0;
            }

            // 如果选择的是当前版本，提示用户
            if ($selectedVersion === $currentVersion) {
                $this->ui->info("PHP版本 {$selectedVersion} 已经是当前版本");
                return 0;
            }

            // 询问切换类型
            $switchOptions = [
                'local' => '本地切换（仅当前会话）',
                'global' => '全局切换（永久）',
                'project' => '项目切换（当前目录）'
            ];

            $switchType = $this->ui->select($switchOptions, '请选择切换类型:', false);

            if (!$switchType) {
                $this->ui->info('已取消操作');
                return 0;
            }

            // 执行切换
            return $this->performVersionSwitch($selectedVersion, $switchType);

        } catch (Exception $e) {
            $this->ui->error('版本选择失败: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 执行版本切换
     *
     * @param string $version 版本号
     * @param string $type 切换类型
     * @return int 返回状态码
     */
    private function performVersionSwitch($version, $type)
    {
        try {
            switch ($type) {
                case 'global':
                    $this->switcher->switchVersion($version, true);
                    $this->ui->success("已将全局PHP版本切换为 {$version}");
                    break;

                case 'project':
                    $projectDir = getcwd();
                    $this->switcher->setProjectVersion($version, $projectDir);
                    $this->ui->success("已将项目 {$projectDir} 的PHP版本设置为 {$version}");
                    break;

                case 'local':
                default:
                    $this->switcher->switchVersion($version, false);
                    $this->ui->success("已切换到PHP版本 {$version}");
                    break;
            }

            return 0;
        } catch (Exception $e) {
            $this->ui->error('版本切换失败: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 获取简单的已安装版本列表
     *
     * @return array 版本号字符串数组
     */
    private function getSimpleInstalledVersions()
    {
        $versions = [];

        // 获取PVM管理的版本
        $pvmVersions = $this->detector->getInstalledVersions();
        foreach ($pvmVersions as $version) {
            $versions[] = $version;
        }

        // 获取系统版本信息
        $allVersions = $this->switcher->getInstalledVersions();
        foreach ($allVersions as $versionInfo) {
            if ($versionInfo['type'] === 'system' && !in_array($versionInfo['version'], $versions)) {
                $versions[] = $versionInfo['version'];
            }
        }

        // 去重并排序
        $versions = array_unique($versions);
        usort($versions, 'version_compare');

        return $versions;
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

永久切换PHP版本。如果不指定版本，将进入交互选择模式。
此命令会修改系统环境变量和符号链接，切换后的PHP版本将在所有终端会话中生效。

参数:
  [版本]  要切换到的PHP版本，例如 7.4.33, 8.1.27（可选）

选项:
  --global, -g     设置为全局PHP版本
  --project, -p    设置为项目级别的PHP版本
  --project=DIR    指定项目目录

示例:
  pvm use                  # 进入交互选择模式
  pvm use 7.4.33           # 切换到PHP 7.4.33
  pvm use 8.1.27 --global  # 切换到PHP 8.1.27并设置为全局版本
  pvm use 8.1.27 --project # 设置当前项目的PHP版本为8.1.27

交互模式:
  当不指定版本时，将显示已安装版本的选择菜单，并可选择切换类型：
  - 本地切换：仅当前会话有效
  - 全局切换：永久设置为全局版本
  - 项目切换：设置为当前目录的项目版本
USAGE;
    }
}
