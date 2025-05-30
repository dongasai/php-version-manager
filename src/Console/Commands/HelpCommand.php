<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Console\Application;

class HelpCommand implements CommandInterface
{
    /**
     * 命令描述映射
     *
     * @var array
     */
    private $commandDescriptions = [
        'init' => '初始化PVM运行环境',
        'install' => '安装指定版本的PHP',
        'use' => '永久切换到指定的PHP版本',
        'switch' => '临时切换到指定的PHP版本（仅当前终端会话有效）',
        'remove' => '删除指定的PHP版本',
        'list' => '列出所有已安装的PHP版本',
        'status' => '显示PVM程序状态信息',
        'ext' => '管理PHP扩展',
        'config' => '管理PHP配置',
        'config-menu' => '交互式配置菜单',
        'composer' => '管理Composer',
        'composer-install' => '安装Composer到指定PHP版本',
        'composer-remove' => '从指定PHP版本删除Composer',
        'composer-default' => '设置默认Composer版本',
        'composer-config' => '配置Composer',
        'cache' => '管理缓存',
        'security' => '管理安全相关功能',
        'env' => '管理环境变量',
        'service' => '管理PHP服务',
        'monitor' => '监控PHP进程和PHP-FPM状态',
        'update' => '更新PVM自身',
        'web' => '启动Web管理界面',
        'mirror' => '管理下载源配置（已废弃）',
        'pvm-mirror' => '管理PVM镜像源',
        'help' => '显示帮助信息',
        'version' => '显示版本信息',
        'supported' => '显示支持的PHP版本列表',
    ];

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

        // 动态显示所有已注册的命令
        $application = new Application();
        $commands = $application->getCommands();

        foreach ($commands as $commandName => $commandClass) {
            $description = isset($this->commandDescriptions[$commandName])
                ? $this->commandDescriptions[$commandName]
                : '无描述';

            printf("  %-15s %s" . PHP_EOL, $commandName, $description);
        }

        echo PHP_EOL;
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
