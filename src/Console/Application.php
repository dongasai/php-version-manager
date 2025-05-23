<?php

namespace VersionManager\Console;

use Exception;

class Application
{
    /**
     * 应用程序版本
     */
    const VERSION = '1.0.0';

    /**
     * 可用命令列表
     *
     * @var array
     */
    protected $commands = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->registerCommands();
    }

    /**
     * 注册所有可用命令
     */
    protected function registerCommands()
    {
        // 在这里注册所有命令
        $this->commands = [
            'init' => Commands\InitCommand::class,
            'install' => Commands\InstallCommand::class,
            'use' => Commands\UseCommand::class,
            'switch' => Commands\SwitchCommand::class, // 新增临时切换PHP版本命令
            'remove' => Commands\RemoveCommand::class,
            'list' => Commands\ListCommand::class,
            'help' => Commands\HelpCommand::class,
            'version' => Commands\VersionCommand::class,
            'supported' => Commands\SupportedCommand::class,
            'ext' => Commands\ExtensionCommand::class,
            'config' => Commands\ConfigCommand::class,
            'config-menu' => Commands\ConfigMenuCommand::class, // 新增交互式配置菜单命令
            'cache' => Commands\CacheCommand::class,
            'security' => Commands\SecurityCommand::class,
            'env' => Commands\EnvCommand::class,
            'service' => Commands\ServiceCommand::class,
            'monitor' => Commands\MonitorCommand::class,
            'update' => Commands\UpdateCommand::class,
            'web' => Commands\WebCommand::class, // 新增Web管理界面命令
            'composer' => Commands\ComposerCommand::class, // 新增Composer管理命令

            // 保留旧的Composer命令，以保持向后兼容性
            'composer-install' => Commands\ComposerInstallCommand::class,
            'composer-remove' => Commands\ComposerRemoveCommand::class,
            'composer-default' => Commands\ComposerDefaultCommand::class,
            'composer-config' => Commands\ComposerConfigCommand::class,
        ];
    }

    /**
     * 运行应用程序
     *
     * @param array $argv 命令行参数
     * @return int 返回状态码
     */
    public function run($argv = null)
    {
        $args = $argv ?: $_SERVER['argv'];

        // 移除脚本名称
        array_shift($args);

        $command = isset($args[0]) ? $args[0] : 'help';

        // 如果是版本查询
        if ($command === '--version' || $command === '-v') {
            $this->showVersion();
            return 0;
        }

        // 如果是帮助命令
        if ($command === '--help' || $command === '-h') {
            $command = 'help';
        }

        // 检查命令是否存在
        if (!isset($this->commands[$command])) {
            echo "未知命令: {$command}" . PHP_EOL;
            echo "运行 'pvm help' 获取可用命令列表" . PHP_EOL;
            return 1;
        }

        // 移除命令名称
        array_shift($args);

        // 执行命令
        $commandClass = $this->commands[$command];
        $commandInstance = new $commandClass();

        return $commandInstance->execute($args);
    }

    /**
     * 显示版本信息
     */
    protected function showVersion()
    {
        echo "PHP Version Manager " . self::VERSION . PHP_EOL;
    }
}
