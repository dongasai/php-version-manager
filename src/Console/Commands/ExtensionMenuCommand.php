<?php

namespace VersionManager\Console\Commands;

use VersionManager\Core\ExtensionManager;
use VersionManager\Core\VersionSwitcher;

/**
 * PHP扩展管理交互式菜单
 */
class ExtensionMenuCommand extends AbstractMenuCommand
{
    /**
     * 扩展管理器
     *
     * @var ExtensionManager
     */
    private $extensionManager;
    
    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $versionSwitcher;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->extensionManager = new ExtensionManager();
        $this->versionSwitcher = new VersionSwitcher();
    }
    
    /**
     * 获取命令名称
     *
     * @return string
     */
    public function getName()
    {
        return 'extension-menu';
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return 'PHP扩展管理';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return 'pvm extension-menu';
    }
    
    /**
     * 初始化菜单选项
     */
    protected function initializeMenu()
    {
        $this->menuOptions = [
            'list' => '查看已安装扩展',
            'available' => '查看可用扩展',
            'install' => '安装扩展',
            'enable' => '启用扩展',
            'disable' => '禁用扩展',
            'remove' => '删除扩展',
            'info' => '查看扩展信息',
            'config' => '配置扩展',
        ];
        
        $this->menuTitle = '请选择扩展管理操作:';
        $this->defaultOption = 'list';
    }
    
    /**
     * 显示欢迎信息
     */
    protected function showWelcome()
    {
        parent::showWelcome();
        
        // 显示当前PHP版本
        try {
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            $this->ui->info('当前PHP版本: ' . $this->ui->colorize($currentVersion ?: '未安装', 'green'), true);
            
            if ($currentVersion) {
                $installedExtensions = $this->extensionManager->getInstalledExtensions();
                $this->ui->info('已安装扩展: ' . count($installedExtensions) . ' 个', true);
            }
            
            $this->ui->info('');
        } catch (\Exception $e) {
            $this->ui->warning('无法获取扩展信息', true);
            $this->ui->info('');
        }
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
            case 'list':
                return $this->listExtensions();
                
            case 'available':
                return $this->showAvailableExtensions();
                
            case 'install':
                return $this->installExtension();
                
            case 'enable':
                return $this->enableExtension();
                
            case 'disable':
                return $this->disableExtension();
                
            case 'remove':
                return $this->removeExtension();
                
            case 'info':
                return $this->showExtensionInfo();
                
            case 'config':
                return $this->configureExtension();
                
            default:
                $this->ui->error("未知选项: {$option}");
                return 1;
        }
    }
    
    /**
     * 列出已安装扩展
     *
     * @return int
     */
    private function listExtensions()
    {
        $extListCommand = new ExtListCommand();
        return $extListCommand->execute([]);
    }
    
    /**
     * 显示可用扩展
     *
     * @return int
     */
    private function showAvailableExtensions()
    {
        $extListCommand = new ExtListCommand();
        return $extListCommand->execute(['--available']);
    }
    
    /**
     * 安装扩展
     *
     * @return int
     */
    private function installExtension()
    {
        try {
            // 检查当前PHP版本
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            if (!$currentVersion) {
                $this->ui->error('请先安装并切换到一个PHP版本');
                return 1;
            }
            
            // 获取可用扩展
            $availableExtensions = $this->extensionManager->getAvailableExtensions();
            
            if (empty($availableExtensions)) {
                $this->ui->error('没有可用的扩展');
                return 1;
            }
            
            // 显示扩展选择菜单
            $this->ui->info('可安装的PHP扩展:', true);
            $extensionOptions = [];
            foreach ($availableExtensions as $extension) {
                $extensionOptions[$extension] = $extension;
            }
            
            $selectedExtension = $this->ui->menu($extensionOptions, '请选择要安装的扩展:');
            
            if (!$selectedExtension) {
                $this->ui->info('已取消安装');
                return 0;
            }
            
            // 询问是否指定版本
            $specifyVersion = $this->ui->confirm('是否要指定扩展版本？', false);
            $version = null;
            
            if ($specifyVersion) {
                $version = $this->ui->prompt('请输入扩展版本（留空使用最新版本）: ');
                if (empty($version)) {
                    $version = null;
                }
            }
            
            // 确认安装
            $versionText = $version ? " (版本: {$version})" : '';
            if (!$this->ui->confirm("确定要安装扩展 {$selectedExtension}{$versionText} 吗？")) {
                $this->ui->info('已取消安装');
                return 0;
            }
            
            // 执行安装
            $args = [$selectedExtension];
            if ($version) {
                $args[] = "--version={$version}";
            }
            
            $extInstallCommand = new ExtInstallCommand();
            return $extInstallCommand->execute($args);
            
        } catch (\Exception $e) {
            $this->ui->error('安装失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 启用扩展
     *
     * @return int
     */
    private function enableExtension()
    {
        return $this->selectAndManageExtension('enable', '启用');
    }
    
    /**
     * 禁用扩展
     *
     * @return int
     */
    private function disableExtension()
    {
        return $this->selectAndManageExtension('disable', '禁用');
    }
    
    /**
     * 删除扩展
     *
     * @return int
     */
    private function removeExtension()
    {
        return $this->selectAndManageExtension('remove', '删除', true);
    }
    
    /**
     * 选择并管理扩展
     *
     * @param string $action 操作类型
     * @param string $description 描述
     * @param bool $needConfirm 是否需要确认
     * @return int
     */
    private function selectAndManageExtension($action, $description, $needConfirm = false)
    {
        try {
            // 检查当前PHP版本
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            if (!$currentVersion) {
                $this->ui->error('请先安装并切换到一个PHP版本');
                return 1;
            }
            
            // 获取已安装的扩展
            $installedExtensions = $this->extensionManager->getInstalledExtensions();
            
            if (empty($installedExtensions)) {
                $this->ui->error('没有已安装的扩展');
                return 1;
            }
            
            // 显示扩展选择菜单
            $this->ui->info("选择要{$description}的扩展:", true);
            $extensionOptions = [];
            foreach ($installedExtensions as $extension) {
                $extensionOptions[$extension] = $extension;
            }
            
            $selectedExtension = $this->ui->menu($extensionOptions, '请选择扩展:');
            
            if (!$selectedExtension) {
                $this->ui->info('已取消操作');
                return 0;
            }
            
            // 确认操作
            if ($needConfirm) {
                if (!$this->ui->confirm("确定要{$description}扩展 {$selectedExtension} 吗？", false)) {
                    $this->ui->info('已取消操作');
                    return 0;
                }
            }
            
            // 执行操作
            switch ($action) {
                case 'enable':
                    $command = new ExtEnableCommand();
                    break;
                case 'disable':
                    $command = new ExtDisableCommand();
                    break;
                case 'remove':
                    $command = new ExtRemoveCommand();
                    break;
                default:
                    $this->ui->error("未知操作: {$action}");
                    return 1;
            }
            
            return $command->execute([$selectedExtension]);
            
        } catch (\Exception $e) {
            $this->ui->error("{$description}失败: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 显示扩展信息
     *
     * @return int
     */
    private function showExtensionInfo()
    {
        try {
            // 获取已安装的扩展
            $installedExtensions = $this->extensionManager->getInstalledExtensions();
            
            if (empty($installedExtensions)) {
                $this->ui->error('没有已安装的扩展');
                return 1;
            }
            
            // 显示扩展选择菜单
            $this->ui->info('选择要查看信息的扩展:', true);
            $extensionOptions = [];
            foreach ($installedExtensions as $extension) {
                $extensionOptions[$extension] = $extension;
            }
            
            $selectedExtension = $this->ui->menu($extensionOptions, '请选择扩展:');
            
            if (!$selectedExtension) {
                $this->ui->info('已取消操作');
                return 0;
            }
            
            // 显示扩展信息
            $extInfoCommand = new ExtInfoCommand();
            return $extInfoCommand->execute([$selectedExtension]);
            
        } catch (\Exception $e) {
            $this->ui->error('获取扩展信息失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 配置扩展
     *
     * @return int
     */
    private function configureExtension()
    {
        try {
            // 获取已安装的扩展
            $installedExtensions = $this->extensionManager->getInstalledExtensions();
            
            if (empty($installedExtensions)) {
                $this->ui->error('没有已安装的扩展');
                return 1;
            }
            
            // 显示扩展选择菜单
            $this->ui->info('选择要配置的扩展:', true);
            $extensionOptions = [];
            foreach ($installedExtensions as $extension) {
                $extensionOptions[$extension] = $extension;
            }
            
            $selectedExtension = $this->ui->menu($extensionOptions, '请选择扩展:');
            
            if (!$selectedExtension) {
                $this->ui->info('已取消操作');
                return 0;
            }
            
            // 配置扩展
            $extConfigCommand = new ExtConfigCommand();
            return $extConfigCommand->execute([$selectedExtension]);
            
        } catch (\Exception $e) {
            $this->ui->error('配置扩展失败: ' . $e->getMessage());
            return 1;
        }
    }
}
