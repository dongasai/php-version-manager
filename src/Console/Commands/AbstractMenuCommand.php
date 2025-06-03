<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Console\UI\ConsoleUI;

/**
 * 交互式菜单命令基类
 */
abstract class AbstractMenuCommand implements CommandInterface
{
    /**
     * 控制台UI工具
     *
     * @var ConsoleUI
     */
    protected $ui;

    /**
     * 菜单选项
     *
     * @var array
     */
    protected $menuOptions = [];

    /**
     * 菜单标题
     *
     * @var string
     */
    protected $menuTitle = '请选择一个选项:';

    /**
     * 默认选项
     *
     * @var mixed
     */
    protected $defaultOption = null;

    /**
     * 是否显示返回选项
     *
     * @var bool
     */
    protected $showBackOption = true;

    /**
     * 是否显示退出选项
     *
     * @var bool
     */
    protected $showExitOption = true;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->ui = new ConsoleUI();
        $this->initializeMenu();
    }
    
    /**
     * 初始化菜单选项
     */
    abstract protected function initializeMenu();
    
    /**
     * 处理菜单选择
     *
     * @param string $option 选择的选项
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    abstract protected function handleMenuOption($option, array $args);
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 如果有参数，则直接执行对应的选项
        if (!empty($args) && isset($this->menuOptions[$args[0]])) {
            return $this->handleMenuOption($args[0], array_slice($args, 1));
        }

        // 显示欢迎信息
        $this->showWelcome();

        // 进入交互循环
        return $this->runInteractiveLoop($args);
    }

    /**
     * 显示欢迎信息
     */
    protected function showWelcome()
    {
        $this->ui->info('');
        $this->ui->info('=' . str_repeat('=', strlen($this->getDescription()) + 2) . '=', true);
        $this->ui->info(' ' . $this->getDescription() . ' ', true);
        $this->ui->info('=' . str_repeat('=', strlen($this->getDescription()) + 2) . '=', true);
        $this->ui->info('');
    }

    /**
     * 运行交互循环
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    protected function runInteractiveLoop(array $args)
    {
        while (true) {
            // 准备菜单选项
            $menuOptions = $this->prepareMenuOptions();

            // 显示菜单并获取用户选择
            $option = $this->ui->menu($menuOptions, $this->menuTitle, $this->defaultOption);

            // 处理特殊选项
            if ($option === 'back') {
                return 0;
            }

            if ($option === 'exit') {
                $this->ui->info('感谢使用 PVM！', true);
                return 0;
            }

            // 处理用户选择
            $result = $this->handleMenuOption($option, $args);

            // 如果返回非0状态码，询问是否继续
            if ($result !== 0) {
                if (!$this->ui->confirm('操作失败，是否继续？', true)) {
                    return $result;
                }
            }

            // 显示分隔线
            $this->ui->info('');
            $this->ui->info(str_repeat('-', 50), true);
            $this->ui->info('');
        }
    }

    /**
     * 准备菜单选项
     *
     * @return array 菜单选项
     */
    protected function prepareMenuOptions()
    {
        $options = $this->menuOptions;

        // 添加返回和退出选项
        if ($this->showBackOption) {
            $options['back'] = '返回上级菜单';
        }

        if ($this->showExitOption) {
            $options['exit'] = '退出程序';
        }

        return $options;
    }
    
    /**
     * 解析命令行选项
     *
     * @param array $args 命令参数
     * @return array 解析后的选项
     */
    protected function parseOptions(array $args)
    {
        $options = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                
                if (strpos($option, '=') !== false) {
                    list($name, $value) = explode('=', $option, 2);
                    $options[$name] = $value;
                } else {
                    $options[$option] = true;
                }
            } elseif (strpos($arg, '-') === 0) {
                $option = substr($arg, 1);
                $options[$option] = true;
            }
        }
        
        return $options;
    }
    
    /**
     * 获取非选项参数
     *
     * @param array $args 命令参数
     * @return array 非选项参数
     */
    protected function getNonOptionArgs(array $args)
    {
        $nonOptionArgs = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '-') !== 0) {
                $nonOptionArgs[] = $arg;
            }
        }
        
        return $nonOptionArgs;
    }
}
