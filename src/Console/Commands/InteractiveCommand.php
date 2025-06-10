<?php

namespace VersionManager\Console\Commands;

use VersionManager\Core\VersionManager;
use VersionManager\Core\ExtensionManager;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\VersionSwitcher;

/**
 * 交互式主菜单命令
 * 
 * 提供一个统一的交互式界面来管理所有PVM功能
 */
class InteractiveCommand extends AbstractMenuCommand
{
    /**
     * 版本管理器
     *
     * @var VersionManager
     */
    private $versionManager;
    
    /**
     * 扩展管理器
     *
     * @var ExtensionManager
     */
    private $extensionManager;
    
    /**
     * Composer管理器
     *
     * @var ComposerManager
     */
    private $composerManager;
    
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
        
        $this->versionManager = new VersionManager();
        $this->extensionManager = new ExtensionManager();
        $this->composerManager = new ComposerManager();
        $this->versionSwitcher = new VersionSwitcher();
        
        // 设置菜单选项
        $this->showBackOption = false; // 主菜单不显示返回选项
    }
    
    /**
     * 获取命令名称
     *
     * @return string
     */
    public function getName()
    {
        return 'interactive';
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return 'PVM 交互式管理界面';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return 'pvm interactive';
    }
    
    /**
     * 初始化菜单选项
     */
    protected function initializeMenu()
    {
        $this->menuOptions = [
            'status' => '查看系统状态',
            'versions' => 'PHP版本管理',
            'extensions' => 'PHP扩展管理',
            'composer' => 'Composer管理',
            'config' => '配置管理',
            'service' => '服务管理',
            'cache' => '缓存管理',
            'monitor' => '系统监控',
            'update' => '更新PVM',
            'help' => '帮助信息',
        ];
        
        $this->menuTitle = '请选择要执行的操作:';
        $this->defaultOption = 'status';
    }
    
    /**
     * 显示欢迎信息
     */
    protected function showWelcome()
    {
        parent::showWelcome();
        
        // 显示当前状态信息
        $this->showCurrentStatus();
    }
    
    /**
     * 显示当前状态信息
     */
    private function showCurrentStatus()
    {
        try {
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            $installedVersions = $this->versionManager->getInstalledVersions();
            
            $this->ui->info('当前状态:', true);
            $this->ui->info('  当前PHP版本: ' . $this->ui->colorize($currentVersion ?: '未安装', \VersionManager\Console\UI\ConsoleUI::COLOR_GREEN), true);
            $this->ui->info('  已安装版本: ' . count($installedVersions) . ' 个', true);
            
            if (!empty($installedVersions)) {
                // 提取版本号字符串
                $versionStrings = array_map(function($versionInfo) {
                    return is_array($versionInfo) ? $versionInfo['version'] : $versionInfo;
                }, $installedVersions);

                $versionList = implode(', ', array_slice($versionStrings, 0, 5));
                if (count($versionStrings) > 5) {
                    $versionList .= ' ...';
                }
                $this->ui->info('  版本列表: ' . $versionList, true);
            }
            
            $this->ui->info('');
        } catch (\Exception $e) {
            $this->ui->warning('无法获取状态信息: ' . $e->getMessage(), true);
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
            case 'status':
                return $this->showDetailedStatus();
                
            case 'versions':
                return $this->manageVersions();
                
            case 'extensions':
                return $this->manageExtensions();
                
            case 'composer':
                return $this->manageComposer();
                
            case 'config':
                return $this->manageConfig();
                
            case 'service':
                return $this->manageServices();
                
            case 'cache':
                return $this->manageCache();
                
            case 'monitor':
                return $this->showMonitor();
                
            case 'update':
                return $this->updatePvm();
                
            case 'help':
                return $this->showHelp();
                
            default:
                $this->ui->error("未知选项: {$option}");
                return 1;
        }
    }
    
    /**
     * 显示详细状态
     *
     * @return int
     */
    private function showDetailedStatus()
    {
        $statusCommand = new StatusCommand();
        return $statusCommand->execute([]);
    }
    
    /**
     * 管理PHP版本
     *
     * @return int
     */
    private function manageVersions()
    {
        $versionMenu = new VersionMenuCommand();
        return $versionMenu->execute([]);
    }
    
    /**
     * 管理PHP扩展
     *
     * @return int
     */
    private function manageExtensions()
    {
        $extensionMenu = new ExtensionMenuCommand();
        return $extensionMenu->execute([]);
    }
    
    /**
     * 管理Composer
     *
     * @return int
     */
    private function manageComposer()
    {
        $composerMenu = new ComposerMenuCommand();
        return $composerMenu->execute([]);
    }
    
    /**
     * 管理配置
     *
     * @return int
     */
    private function manageConfig()
    {
        $configMenu = new ConfigMenuCommand();
        return $configMenu->execute([]);
    }
    
    /**
     * 管理服务
     *
     * @return int
     */
    private function manageServices()
    {
        $serviceMenu = new ServiceMenuCommand();
        return $serviceMenu->execute([]);
    }
    
    /**
     * 管理缓存
     *
     * @return int
     */
    private function manageCache()
    {
        $cacheCommand = new CacheCommand();
        return $cacheCommand->execute(['clear']);
    }
    
    /**
     * 显示监控信息
     *
     * @return int
     */
    private function showMonitor()
    {
        $monitorCommand = new MonitorCommand();
        return $monitorCommand->execute([]);
    }
    
    /**
     * 更新PVM
     *
     * @return int
     */
    private function updatePvm()
    {
        $updateCommand = new UpdateCommand();
        return $updateCommand->execute([]);
    }
    
    /**
     * 显示帮助信息
     *
     * @return int
     */
    private function showHelp()
    {
        $helpCommand = new HelpCommand();
        return $helpCommand->execute([]);
    }
}
