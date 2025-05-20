<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Console\Application;

class HelpCommand implements CommandInterface
{
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        echo "PHP Version Manager " . Application::VERSION . PHP_EOL;
        echo "用法: pvm [命令] [参数]" . PHP_EOL . PHP_EOL;

        echo "可用命令:" . PHP_EOL;
        echo "  init      初始化PVM运行环境" . PHP_EOL;
        echo "  install   安装指定版本的PHP" . PHP_EOL;
        echo "  use       切换到指定的PHP版本" . PHP_EOL;
        echo "  remove    删除指定的PHP版本" . PHP_EOL;
        echo "  list      列出所有已安装的PHP版本" . PHP_EOL;
        echo "  ext       管理PHP扩展" . PHP_EOL;
        echo "  config    管理PHP配置" . PHP_EOL;
        echo "  composer  管理Composer" . PHP_EOL;
        echo "  cache     管理缓存" . PHP_EOL;
        echo "  security  管理安全相关功能" . PHP_EOL;
        echo "  env       管理环境变量" . PHP_EOL;
        echo "  service   管理PHP服务" . PHP_EOL;
        echo "  monitor   监控PHP进程和PHP-FPM状态" . PHP_EOL;
        echo "  update    更新PVM自身" . PHP_EOL;
        echo "  help      显示帮助信息" . PHP_EOL . PHP_EOL;

        echo "更多信息请访问: https://github.com/dongasai/php-version-manager" . PHP_EOL;

        return 0;
    }

    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '显示帮助信息';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return 'pvm help';
    }
}
