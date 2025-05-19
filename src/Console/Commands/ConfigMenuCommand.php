<?php

namespace VersionManager\Console\Commands;

/**
 * 配置菜单命令类
 * 
 * 提供交互式菜单来管理PHP配置
 */
class ConfigMenuCommand extends AbstractMenuCommand
{
    /**
     * 配置管理器
     *
     * @var \VersionManager\Core\ConfigManager
     */
    private $configManager;
    
    /**
     * 版本切换器
     *
     * @var \VersionManager\Core\VersionSwitcher
     */
    private $versionSwitcher;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->versionSwitcher = new \VersionManager\Core\VersionSwitcher();
        $phpVersion = $this->versionSwitcher->getCurrentVersion();
        $this->configManager = new \VersionManager\Core\ConfigManager($phpVersion);
    }
    
    /**
     * 初始化菜单选项
     */
    protected function initializeMenu()
    {
        $this->menuTitle = '请选择配置操作:';
        $this->menuOptions = [
            'view' => '查看当前配置',
            'edit' => '编辑配置文件',
            'set' => '设置配置项',
            'get' => '获取配置项值',
            'template' => '应用配置模板',
            'backup' => '备份配置文件',
            'restore' => '恢复配置文件',
            'help' => '显示帮助信息',
            'exit' => '退出'
        ];
        $this->defaultOption = 'view';
    }
    
    /**
     * 处理菜单选择
     *
     * @param string $option 选择的选项
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    protected function handleMenuOption($option, array $args)
    {
        switch ($option) {
            case 'view':
                return $this->viewConfig($args);
            case 'edit':
                return $this->editConfig($args);
            case 'set':
                return $this->setConfigValue($args);
            case 'get':
                return $this->getConfigValue($args);
            case 'template':
                return $this->applyTemplate($args);
            case 'backup':
                return $this->backupConfig($args);
            case 'restore':
                return $this->restoreConfig($args);
            case 'help':
                return $this->showHelp();
            case 'exit':
                return 0;
            default:
                $this->ui->error("未知选项: {$option}");
                return 1;
        }
    }
    
    /**
     * 查看当前配置
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function viewConfig(array $args)
    {
        $options = $this->parseOptions($args);
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        $this->ui->info("PHP {$phpVersion} 配置:", true);
        
        // 获取配置文件路径
        $configFile = $this->configManager->getConfigFilePath();
        $this->ui->info("配置文件: " . $this->ui->colorize($configFile, ConsoleUI::COLOR_CYAN), true);
        
        // 获取配置内容
        $config = $this->configManager->getConfig();
        
        if (empty($config)) {
            $this->ui->warning("配置为空或无法读取", true);
            return 1;
        }
        
        // 显示配置分组
        $groups = [
            'PHP' => ['version', 'memory_limit', 'max_execution_time', 'display_errors', 'error_reporting'],
            '路径' => ['extension_dir', 'include_path', 'doc_root', 'upload_tmp_dir'],
            '会话' => ['session.save_path', 'session.name', 'session.cookie_lifetime'],
            '数据库' => ['mysqli.default_host', 'mysqli.default_port', 'pdo_mysql.default_socket']
        ];
        
        foreach ($groups as $groupName => $keys) {
            $this->ui->info("\n" . $this->ui->colorize($groupName . " 设置:", ConsoleUI::COLOR_YELLOW, null, ConsoleUI::STYLE_BOLD), true);
            
            foreach ($keys as $key) {
                if (isset($config[$key])) {
                    echo "  " . $this->ui->colorize($key, ConsoleUI::COLOR_GREEN) . " = " . $config[$key] . PHP_EOL;
                }
            }
        }
        
        // 询问是否查看所有配置
        if ($this->ui->confirm("\n是否查看所有配置?", false)) {
            $this->ui->info("\n" . $this->ui->colorize("所有配置:", ConsoleUI::COLOR_YELLOW, null, ConsoleUI::STYLE_BOLD), true);
            
            foreach ($config as $key => $value) {
                echo "  " . $this->ui->colorize($key, ConsoleUI::COLOR_GREEN) . " = " . $value . PHP_EOL;
            }
        }
        
        return 0;
    }
    
    /**
     * 编辑配置文件
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function editConfig(array $args)
    {
        $options = $this->parseOptions($args);
        $phpVersion = isset($options['php']) ? $options['php'] : $this->versionSwitcher->getCurrentVersion();
        
        $this->ui->info("编辑 PHP {$phpVersion} 配置文件", true);
        
        // 获取配置文件路径
        $configFile = $this->configManager->getConfigFilePath();
        $this->ui->info("配置文件: " . $this->ui->colorize($configFile, ConsoleUI::COLOR_CYAN), true);
        
        // 询问使用哪个编辑器
        $editors = ['vim', 'nano', 'emacs', 'gedit', 'code'];
        $editor = $this->ui->menu($editors, "请选择编辑器:", 'vim');
        
        // 执行编辑命令
        $command = "{$editor} {$configFile}";
        $this->ui->info("执行: {$command}", true);
        
        system($command, $returnCode);
        
        if ($returnCode !== 0) {
            $this->ui->error("编辑器返回错误代码: {$returnCode}", true);
            return 1;
        }
        
        $this->ui->success("配置文件已编辑", true);
        return 0;
    }
    
    /**
     * 设置配置项值
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function setConfigValue(array $args)
    {
        if (count($args) < 2) {
            $directive = $this->ui->prompt("请输入配置项名称:");
            $value = $this->ui->prompt("请输入配置项值:");
        } else {
            $directive = $args[0];
            $value = $args[1];
        }
        
        try {
            $this->configManager->setDirective($directive, $value);
            $this->ui->success("配置项 {$directive} 已设置为 {$value}", true);
            return 0;
        } catch (\Exception $e) {
            $this->ui->error("错误: " . $e->getMessage(), true);
            return 1;
        }
    }
    
    /**
     * 获取配置项值
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function getConfigValue(array $args)
    {
        if (empty($args)) {
            $directive = $this->ui->prompt("请输入配置项名称:");
        } else {
            $directive = $args[0];
        }
        
        try {
            $value = $this->configManager->getDirective($directive);
            $this->ui->info("配置项 " . $this->ui->colorize($directive, ConsoleUI::COLOR_GREEN) . " = " . $this->ui->colorize($value, ConsoleUI::COLOR_CYAN), true);
            return 0;
        } catch (\Exception $e) {
            $this->ui->error("错误: " . $e->getMessage(), true);
            return 1;
        }
    }
    
    /**
     * 应用配置模板
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function applyTemplate(array $args)
    {
        // 获取可用模板
        $templates = [
            'development' => '开发环境配置',
            'production' => '生产环境配置',
            'testing' => '测试环境配置',
            'minimal' => '最小化配置',
            'performance' => '性能优化配置',
            'security' => '安全加固配置'
        ];
        
        // 显示模板选择菜单
        $template = $this->ui->menu($templates, "请选择配置模板:", 'development');
        
        // 确认应用模板
        if (!$this->ui->confirm("确定要应用 {$templates[$template]} 模板吗?", true)) {
            $this->ui->warning("操作已取消", true);
            return 0;
        }
        
        try {
            $this->configManager->applyTemplate($template);
            $this->ui->success("{$templates[$template]} 模板已应用", true);
            return 0;
        } catch (\Exception $e) {
            $this->ui->error("错误: " . $e->getMessage(), true);
            return 1;
        }
    }
    
    /**
     * 备份配置文件
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function backupConfig(array $args)
    {
        try {
            $backupFile = $this->configManager->backupConfig();
            $this->ui->success("配置已备份到: " . $this->ui->colorize($backupFile, ConsoleUI::COLOR_CYAN), true);
            return 0;
        } catch (\Exception $e) {
            $this->ui->error("错误: " . $e->getMessage(), true);
            return 1;
        }
    }
    
    /**
     * 恢复配置文件
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function restoreConfig(array $args)
    {
        // 获取备份文件列表
        $backups = $this->configManager->getBackupFiles();
        
        if (empty($backups)) {
            $this->ui->warning("没有可用的备份文件", true);
            return 1;
        }
        
        // 格式化备份文件列表
        $backupOptions = [];
        foreach ($backups as $backup) {
            $time = date('Y-m-d H:i:s', filemtime($backup));
            $backupOptions[$backup] = basename($backup) . " ({$time})";
        }
        
        // 显示备份文件选择菜单
        $backupFile = $this->ui->menu($backupOptions, "请选择要恢复的备份文件:");
        
        // 确认恢复
        if (!$this->ui->confirm("确定要恢复此备份吗?", true)) {
            $this->ui->warning("操作已取消", true);
            return 0;
        }
        
        try {
            $this->configManager->restoreConfig($backupFile);
            $this->ui->success("配置已从备份恢复", true);
            return 0;
        } catch (\Exception $e) {
            $this->ui->error("错误: " . $e->getMessage(), true);
            return 1;
        }
    }
    
    /**
     * 显示帮助信息
     *
     * @return int 返回状态码
     */
    private function showHelp()
    {
        $this->ui->info($this->getDescription(), true);
        $this->ui->info(str_repeat('-', strlen($this->getDescription())), true);
        $this->ui->info("可用命令:", true);
        
        foreach ($this->menuOptions as $option => $description) {
            echo "  " . $this->ui->colorize($option, ConsoleUI::COLOR_GREEN) . str_repeat(' ', 12 - strlen($option)) . $description . PHP_EOL;
        }
        
        return 0;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return 'PHP配置管理';
    }
}
