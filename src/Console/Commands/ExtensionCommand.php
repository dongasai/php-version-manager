<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Console\UI\ConsoleUI;

/**
 * 扩展命令类
 *
 * 用于管理PHP扩展
 */
class ExtensionCommand implements CommandInterface
{

    /**
     * 扩展管理器
     *
     * @var \VersionManager\Core\ExtensionManager
     */
    private $manager;

    /**
     * 版本切换器
     *
     * @var \VersionManager\Core\VersionSwitcher
     */
    private $switcher;

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
        $this->switcher = new \VersionManager\Core\VersionSwitcher();
        $phpVersion     = $this->switcher->getCurrentVersion();
        $this->manager  = new \VersionManager\Core\ExtensionManager($phpVersion);
        $this->ui       = new ConsoleUI();
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
            return $this->listExtensions();
        }

        $action = array_shift($args);

        switch ($action) {
            case 'list':
                return $this->listExtensions();
            case 'install':
                return $this->installExtension($args);
            case 'remove':
                return $this->removeExtension($args);
            case 'enable':
                return $this->enableExtension($args);
            case 'disable':
                return $this->disableExtension($args);
            case 'config':
                return $this->configureExtension($args);
            default:
                echo "错误: 未知的操作 '{$action}'" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;

                return 1;
        }
    }

    /**
     * 列出扩展
     *
     * @return int 返回状态码
     */
    private function listExtensions()
    {
        $installedExtensions = $this->manager->getInstalledExtensions();

        $this->ui->info("已安装的PHP扩展:", true);

        if (empty($installedExtensions)) {
            $this->ui->warning("  没有已安装的扩展", true);
        } else {
            foreach ($installedExtensions as $name => $info) {
                $status  = isset($info['enabled']) && $info['enabled'] ? '已启用' : '已禁用';
                $type    = isset($info['type']) ? $info['type'] : '';
                $version = isset($info['version']) ? $info['version'] : '';

                $output = "  * " . $this->ui->colorize($name, ConsoleUI::COLOR_CYAN);

                if (!empty($version)) {
                    $output .= " (" . $this->ui->colorize($version, ConsoleUI::COLOR_YELLOW) . ")";
                }

                if (isset($info['enabled']) && $info['enabled']) {
                    $output .= " [" . $this->ui->colorize($status, ConsoleUI::COLOR_GREEN) . "]";
                } else {
                    $output .= " [" . $this->ui->colorize($status, ConsoleUI::COLOR_RED) . "]";
                }

                if (!empty($type)) {
                    $output .= " [" . $this->ui->colorize($type, ConsoleUI::COLOR_BLUE) . "]";
                }

                echo $output . PHP_EOL;
            }
        }

        echo PHP_EOL;

        return 0;
    }

    /**
     * 安装扩展
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function installExtension(array $args)
    {
        if (empty($args)) {
            $this->ui->error("错误: 请指定要安装的扩展", true);

            return 1;
        }

        $extension = array_shift($args);
        $options   = $this->parseOptions($args);

        try {
            $this->ui->info("正在安装扩展 " . $this->ui->colorize($extension, ConsoleUI::COLOR_CYAN) . "...", true);

            // 模拟安装进度
            if (!isset($options['no-progress'])) {
                for ($i = 0; $i <= 100; $i += 5) {
                    $this->ui->progressBar($i, 100, "安装进度: ", "");
                    usleep(100000); // 延迟0.1秒
                }
            }

            $this->manager->installExtension($extension, $options);
            $this->ui->success("扩展 " . $this->ui->colorize($extension, ConsoleUI::COLOR_CYAN) . " 安装成功", true);

            return 0;
        } catch (\Exception $e) {
            $this->ui->error("错误: " . $e->getMessage(), true);

            return 1;
        }
    }

    /**
     * 删除扩展
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function removeExtension(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定要删除的扩展" . PHP_EOL;

            return 1;
        }

        $extension = array_shift($args);
        $options   = $this->parseOptions($args);

        try {
            echo "正在删除扩展 {$extension}..." . PHP_EOL;
            $this->manager->removeExtension($extension, $options);
            echo "扩展 {$extension} 删除成功" . PHP_EOL;

            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;

            return 1;
        }
    }

    /**
     * 启用扩展
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function enableExtension(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定要启用的扩展" . PHP_EOL;

            return 1;
        }

        $extension = array_shift($args);
        $options   = $this->parseOptions($args);
        $isZend    = isset($options['zend']) && $options['zend'];
        $config    = isset($options['config']) ? $options['config'] : [];

        try {
            echo "正在启用扩展 {$extension}..." . PHP_EOL;
            $this->manager->enableExtension($extension, $config, $isZend);
            echo "扩展 {$extension} 启用成功" . PHP_EOL;

            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;

            return 1;
        }
    }

    /**
     * 禁用扩展
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function disableExtension(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定要禁用的扩展" . PHP_EOL;

            return 1;
        }

        $extension = array_shift($args);

        try {
            echo "正在禁用扩展 {$extension}..." . PHP_EOL;
            $this->manager->disableExtension($extension);
            echo "扩展 {$extension} 禁用成功" . PHP_EOL;

            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;

            return 1;
        }
    }

    /**
     * 配置扩展
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function configureExtension(array $args)
    {
        if (count($args) < 2) {
            echo "错误: 请指定要配置的扩展和配置项" . PHP_EOL;

            return 1;
        }

        $extension = array_shift($args);
        $config    = [];

        // 解析配置项
        foreach ($args as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $config[$key] = $value;
            }
        }

        if (empty($config)) {
            echo "错误: 请指定至少一个配置项" . PHP_EOL;

            return 1;
        }

        try {
            echo "正在配置扩展 {$extension}..." . PHP_EOL;
            $this->manager->configureExtension($extension, $config);
            echo "扩展 {$extension} 配置成功" . PHP_EOL;

            return 0;
        } catch (\Exception $e) {
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
        $options = [];

        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);

                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;
                }
            } elseif (strpos($arg, '-') === 0) {
                $option           = substr($arg, 1);
                $options[$option] = true;
            } elseif (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $options[$key] = $value;
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
        return '管理PHP扩展';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm ext <操作> [参数]

管理PHP扩展。

操作:
  list                    列出已安装的扩展
  install <扩展> [选项]   安装扩展
  remove <扩展> [选项]    删除扩展
  enable <扩展> [选项]    启用扩展
  disable <扩展>           禁用扩展
  config <扩展> <配置项>    配置扩展

选项:
  --version=<版本>         指定扩展版本
  --force                 强制安装或删除
  --zend                  指定为Zend扩展
  --source=<源码URL>        从源码安装
  --pecl                  从 PECL 安装

示例:
  pvm ext list
  pvm ext install mysqli
  pvm ext install redis --version=5.3.7
  pvm ext remove redis
  pvm ext enable mysqli
  pvm ext disable mysqli
  pvm ext config mysqli connect_timeout=5
USAGE;
    }

}