<?php

namespace VersionManager\Console\Commands;

use VersionManager\Core\VersionManager;
use VersionManager\Core\VersionSwitcher;
use VersionManager\Core\SupportedVersions;

/**
 * PHP版本管理交互式菜单
 */
class VersionMenuCommand extends AbstractMenuCommand
{
    /**
     * 版本管理器
     *
     * @var VersionManager
     */
    private $versionManager;
    
    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $versionSwitcher;
    
    /**
     * 支持的版本
     *
     * @var SupportedVersions
     */
    private $supportedVersions;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->versionManager = new VersionManager();
        $this->versionSwitcher = new VersionSwitcher();
        $this->supportedVersions = new SupportedVersions();
    }
    
    /**
     * 获取命令名称
     *
     * @return string
     */
    public function getName()
    {
        return 'version-menu';
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return 'PHP版本管理';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return 'pvm version-menu';
    }
    
    /**
     * 初始化菜单选项
     */
    protected function initializeMenu()
    {
        $this->menuOptions = [
            'list' => '查看已安装版本',
            'supported' => '查看支持的版本',
            'install' => '安装新版本',
            'use' => '切换版本（永久）',
            'switch' => '切换版本（临时）',
            'remove' => '删除版本',
            'current' => '查看当前版本',
        ];
        
        $this->menuTitle = '请选择版本管理操作:';
        $this->defaultOption = 'list';
    }
    
    /**
     * 显示欢迎信息
     */
    protected function showWelcome()
    {
        parent::showWelcome();
        
        // 显示当前版本信息
        try {
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            $this->ui->info('当前PHP版本: ' . $this->ui->colorize($currentVersion ?: '未安装', 'green'), true);
            $this->ui->info('');
        } catch (\Exception $e) {
            $this->ui->warning('无法获取当前版本信息', true);
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
                return $this->listVersions();
                
            case 'supported':
                return $this->showSupportedVersions();
                
            case 'install':
                return $this->installVersion();
                
            case 'use':
                return $this->useVersion();
                
            case 'switch':
                return $this->switchVersion();
                
            case 'remove':
                return $this->removeVersion();
                
            case 'current':
                return $this->showCurrentVersion();
                
            default:
                $this->ui->error("未知选项: {$option}");
                return 1;
        }
    }
    
    /**
     * 列出已安装版本
     *
     * @return int
     */
    private function listVersions()
    {
        $listCommand = new ListCommand();
        return $listCommand->execute([]);
    }
    
    /**
     * 显示支持的版本
     *
     * @return int
     */
    private function showSupportedVersions()
    {
        $supportedCommand = new SupportedCommand();
        return $supportedCommand->execute([]);
    }
    
    /**
     * 安装版本
     *
     * @return int
     */
    private function installVersion()
    {
        try {
            // 获取支持的版本
            $supportedVersions = $this->supportedVersions->getAvailableVersions();
            
            if (empty($supportedVersions)) {
                $this->ui->error('没有可用的PHP版本');
                return 1;
            }
            
            // 显示版本选择菜单
            $this->ui->info('可安装的PHP版本:', true);
            $versionOptions = [];
            foreach ($supportedVersions as $version) {
                $versionOptions[$version] = "PHP {$version}";
            }
            
            $selectedVersion = $this->ui->menu($versionOptions, '请选择要安装的版本:');
            
            if (!$selectedVersion) {
                $this->ui->info('已取消安装');
                return 0;
            }
            
            // 确认安装
            if (!$this->ui->confirm("确定要安装 PHP {$selectedVersion} 吗？")) {
                $this->ui->info('已取消安装');
                return 0;
            }
            
            // 执行安装
            $installCommand = new InstallCommand();
            return $installCommand->execute([$selectedVersion]);
            
        } catch (\Exception $e) {
            $this->ui->error('安装失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 切换版本（永久）
     *
     * @return int
     */
    private function useVersion()
    {
        return $this->selectAndSwitchVersion('use', '永久切换');
    }
    
    /**
     * 切换版本（临时）
     *
     * @return int
     */
    private function switchVersion()
    {
        return $this->selectAndSwitchVersion('switch', '临时切换');
    }
    
    /**
     * 选择并切换版本
     *
     * @param string $command 命令类型
     * @param string $description 描述
     * @return int
     */
    private function selectAndSwitchVersion($command, $description)
    {
        try {
            // 获取已安装的版本
            $installedVersions = $this->versionManager->getInstalledVersions();
            
            if (empty($installedVersions)) {
                $this->ui->error('没有已安装的PHP版本');
                return 1;
            }
            
            // 显示版本选择菜单
            $this->ui->info("选择要{$description}的PHP版本:", true);
            $versionOptions = [];
            foreach ($installedVersions as $version) {
                $versionOptions[$version] = "PHP {$version}";
            }
            
            $selectedVersion = $this->ui->menu($versionOptions, '请选择版本:');
            
            if (!$selectedVersion) {
                $this->ui->info('已取消操作');
                return 0;
            }
            
            // 执行切换
            if ($command === 'use') {
                $useCommand = new UseCommand();
                return $useCommand->execute([$selectedVersion]);
            } else {
                $switchCommand = new SwitchCommand();
                return $switchCommand->execute([$selectedVersion]);
            }
            
        } catch (\Exception $e) {
            $this->ui->error('切换失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 删除版本
     *
     * @return int
     */
    private function removeVersion()
    {
        try {
            // 获取已安装的版本
            $installedVersions = $this->versionManager->getInstalledVersions();
            
            if (empty($installedVersions)) {
                $this->ui->error('没有已安装的PHP版本');
                return 1;
            }
            
            // 显示版本选择菜单
            $this->ui->warning('选择要删除的PHP版本:', true);
            $versionOptions = [];
            foreach ($installedVersions as $version) {
                $versionOptions[$version] = "PHP {$version}";
            }
            
            $selectedVersion = $this->ui->menu($versionOptions, '请选择要删除的版本:');
            
            if (!$selectedVersion) {
                $this->ui->info('已取消删除');
                return 0;
            }
            
            // 确认删除
            if (!$this->ui->confirm("确定要删除 PHP {$selectedVersion} 吗？此操作不可恢复！", false)) {
                $this->ui->info('已取消删除');
                return 0;
            }
            
            // 执行删除
            $removeCommand = new RemoveCommand();
            return $removeCommand->execute([$selectedVersion]);
            
        } catch (\Exception $e) {
            $this->ui->error('删除失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 显示当前版本
     *
     * @return int
     */
    private function showCurrentVersion()
    {
        try {
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            
            if ($currentVersion) {
                $this->ui->success("当前PHP版本: {$currentVersion}", true);
                
                // 显示详细信息
                $phpPath = $this->versionSwitcher->getPhpPath($currentVersion);
                if ($phpPath) {
                    $this->ui->info("PHP路径: {$phpPath}", true);
                }
                
                // 显示版本信息
                $output = shell_exec('php -v 2>/dev/null');
                if ($output) {
                    $this->ui->info('版本详情:', true);
                    $this->ui->info($output, false);
                }
            } else {
                $this->ui->warning('当前没有设置PHP版本', true);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->ui->error('获取版本信息失败: ' . $e->getMessage());
            return 1;
        }
    }
}
