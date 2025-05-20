<?php

namespace Mirror;

use Mirror\Command\CacheCommand;
use Mirror\Command\CleanCommand;
use Mirror\Command\CommandInterface;
use Mirror\Command\ConfigCommand;
use Mirror\Command\HelpCommand;
use Mirror\Command\IntegrateCommand;
use Mirror\Command\LogCommand;
use Mirror\Command\MonitorCommand;
use Mirror\Command\ResourceCommand;
use Mirror\Command\SecurityCommand;
use Mirror\Command\ServerCommand;
use Mirror\Command\StatusCommand;
use Mirror\Command\SyncCommand;

/**
 * 应用程序类
 */
class Application
{
    /**
     * 命令列表
     *
     * @var array
     */
    private $commands = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 注册命令
        $this->registerCommands();
    }

    /**
     * 注册命令
     */
    private function registerCommands()
    {
        $this->addCommand(new SyncCommand());
        $this->addCommand(new StatusCommand());
        $this->addCommand(new CleanCommand());
        $this->addCommand(new ServerCommand());
        $this->addCommand(new ConfigCommand());
        $this->addCommand(new SecurityCommand());
        $this->addCommand(new CacheCommand());
        $this->addCommand(new ResourceCommand());
        $this->addCommand(new LogCommand());
        $this->addCommand(new MonitorCommand());
        $this->addCommand(new IntegrateCommand());
        $this->addCommand(new HelpCommand($this));
    }

    /**
     * 添加命令
     *
     * @param CommandInterface $command 命令对象
     */
    public function addCommand(CommandInterface $command)
    {
        $this->commands[$command->getName()] = $command;
    }

    /**
     * 获取命令
     *
     * @param string $name 命令名称
     * @return CommandInterface|null
     */
    public function getCommand($name)
    {
        return isset($this->commands[$name]) ? $this->commands[$name] : null;
    }

    /**
     * 获取所有命令
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * 运行应用程序
     *
     * @param array $args 命令行参数
     * @return int 退出代码
     */
    public function run(array $args = [])
    {
        // 如果没有参数，显示帮助信息
        if (count($args) < 2) {
            return $this->getCommand('help')->execute([]);
        }

        // 获取命令名称
        $commandName = $args[1];

        // 获取命令参数
        $commandArgs = array_slice($args, 2);

        // 获取命令
        $command = $this->getCommand($commandName);

        // 如果命令不存在，显示错误信息
        if ($command === null) {
            echo "未知命令: $commandName\n";
            return $this->getCommand('help')->execute([]);
        }

        // 执行命令
        return $command->execute($commandArgs);
    }
}
