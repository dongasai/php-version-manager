<?php

namespace VersionManager\Console\Commands;

use VersionManager\Core\ComposerManager;
use VersionManager\Core\VersionSwitcher;

/**
 * Composer管理交互式菜单
 */
class ComposerMenuCommand extends AbstractMenuCommand
{
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
        
        $this->composerManager = new ComposerManager();
        $this->versionSwitcher = new VersionSwitcher();
    }
    
    /**
     * 获取命令名称
     *
     * @return string
     */
    public function getName()
    {
        return 'composer-menu';
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Composer管理';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return 'pvm composer-menu';
    }
    
    /**
     * 初始化菜单选项
     */
    protected function initializeMenu()
    {
        $this->menuOptions = [
            'status' => '查看Composer状态',
            'list' => '列出已安装的Composer',
            'install' => '安装Composer',
            'use' => '切换Composer版本',
            'remove' => '删除Composer',
            'update' => '更新Composer',
            'config' => '配置Composer',
            'global' => '全局包管理',
        ];
        
        $this->menuTitle = '请选择Composer管理操作:';
        $this->defaultOption = 'status';
    }
    
    /**
     * 显示欢迎信息
     */
    protected function showWelcome()
    {
        parent::showWelcome();
        
        // 显示当前状态
        try {
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            $this->ui->info('当前PHP版本: ' . $this->ui->colorize($currentVersion ?: '未安装', 'green'), true);
            
            if ($currentVersion) {
                $composerVersion = $this->composerManager->getCurrentVersion();
                $this->ui->info('当前Composer版本: ' . $this->ui->colorize($composerVersion ?: '未安装', 'green'), true);
            }
            
            $this->ui->info('');
        } catch (\Exception $e) {
            $this->ui->warning('无法获取Composer状态', true);
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
                return $this->showComposerStatus();
                
            case 'list':
                return $this->listComposer();
                
            case 'install':
                return $this->installComposer();
                
            case 'use':
                return $this->useComposer();
                
            case 'remove':
                return $this->removeComposer();
                
            case 'update':
                return $this->updateComposer();
                
            case 'config':
                return $this->configureComposer();
                
            case 'global':
                return $this->manageGlobalPackages();
                
            default:
                $this->ui->error("未知选项: {$option}");
                return 1;
        }
    }
    
    /**
     * 显示Composer状态
     *
     * @return int
     */
    private function showComposerStatus()
    {
        try {
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            
            if (!$currentVersion) {
                $this->ui->error('请先安装并切换到一个PHP版本');
                return 1;
            }
            
            $this->ui->info('Composer状态信息:', true);
            $this->ui->info('');
            
            // 显示当前Composer版本
            $composerVersion = $this->composerManager->getCurrentVersion();
            if ($composerVersion) {
                $this->ui->success("当前Composer版本: {$composerVersion}", true);
                
                // 显示Composer路径
                $composerPath = $this->composerManager->getComposerPath();
                if ($composerPath) {
                    $this->ui->info("Composer路径: {$composerPath}", true);
                }
                
                // 显示版本详情
                $output = shell_exec('composer --version 2>/dev/null');
                if ($output) {
                    $this->ui->info('版本详情:', true);
                    $this->ui->info(trim($output), true);
                }
            } else {
                $this->ui->warning('当前没有安装Composer', true);
            }
            
            // 显示已安装的Composer版本
            $installedVersions = $this->composerManager->getInstalledVersions();
            if (!empty($installedVersions)) {
                $this->ui->info('');
                $this->ui->info('已安装的Composer版本:', true);
                foreach ($installedVersions as $version) {
                    $this->ui->info("  - {$version}", true);
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->ui->error('获取Composer状态失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 列出Composer
     *
     * @return int
     */
    private function listComposer()
    {
        $composerListCommand = new ComposerListCommand();
        return $composerListCommand->execute([]);
    }
    
    /**
     * 安装Composer
     *
     * @return int
     */
    private function installComposer()
    {
        try {
            // 检查当前PHP版本
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            if (!$currentVersion) {
                $this->ui->error('请先安装并切换到一个PHP版本');
                return 1;
            }
            
            // 选择Composer版本
            $versionOptions = [
                '2' => 'Composer 2.x (推荐)',
                '1' => 'Composer 1.x (兼容性)',
                'latest' => '最新版本',
            ];
            
            $selectedVersion = $this->ui->menu($versionOptions, '请选择要安装的Composer版本:');
            
            if (!$selectedVersion) {
                $this->ui->info('已取消安装');
                return 0;
            }
            
            // 询问是否为特定PHP版本安装
            $forSpecificPhp = $this->ui->confirm('是否为特定PHP版本安装？', false);
            $phpVersion = null;
            
            if ($forSpecificPhp) {
                $phpVersion = $this->ui->prompt('请输入PHP版本（留空使用当前版本）: ');
                if (empty($phpVersion)) {
                    $phpVersion = $currentVersion;
                }
            }
            
            // 确认安装
            $versionText = $selectedVersion === 'latest' ? '最新版本' : "版本 {$selectedVersion}";
            $phpText = $phpVersion ? " (PHP {$phpVersion})" : '';
            
            if (!$this->ui->confirm("确定要安装 Composer {$versionText}{$phpText} 吗？")) {
                $this->ui->info('已取消安装');
                return 0;
            }
            
            // 执行安装
            $args = [];
            if ($selectedVersion !== 'latest') {
                $args[] = "--version={$selectedVersion}";
            }
            if ($phpVersion) {
                $args[] = "--php={$phpVersion}";
            }
            
            $composerInstallCommand = new ComposerInstallCommand();
            return $composerInstallCommand->execute($args);
            
        } catch (\Exception $e) {
            $this->ui->error('安装失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 切换Composer版本
     *
     * @return int
     */
    private function useComposer()
    {
        try {
            // 获取已安装的Composer版本
            $installedVersions = $this->composerManager->getInstalledVersions();
            
            if (empty($installedVersions)) {
                $this->ui->error('没有已安装的Composer版本');
                return 1;
            }
            
            // 显示版本选择菜单
            $this->ui->info('选择要切换的Composer版本:', true);
            $versionOptions = [];
            foreach ($installedVersions as $version) {
                $versionOptions[$version] = "Composer {$version}";
            }
            
            $selectedVersion = $this->ui->menu($versionOptions, '请选择版本:');
            
            if (!$selectedVersion) {
                $this->ui->info('已取消切换');
                return 0;
            }
            
            // 执行切换
            $composerCommand = new ComposerCommand();
            return $composerCommand->execute(['use', $selectedVersion]);
            
        } catch (\Exception $e) {
            $this->ui->error('切换失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 删除Composer
     *
     * @return int
     */
    private function removeComposer()
    {
        try {
            // 获取已安装的Composer版本
            $installedVersions = $this->composerManager->getInstalledVersions();
            
            if (empty($installedVersions)) {
                $this->ui->error('没有已安装的Composer版本');
                return 1;
            }
            
            // 显示版本选择菜单
            $this->ui->warning('选择要删除的Composer版本:', true);
            $versionOptions = [];
            foreach ($installedVersions as $version) {
                $versionOptions[$version] = "Composer {$version}";
            }
            
            $selectedVersion = $this->ui->menu($versionOptions, '请选择要删除的版本:');
            
            if (!$selectedVersion) {
                $this->ui->info('已取消删除');
                return 0;
            }
            
            // 确认删除
            if (!$this->ui->confirm("确定要删除 Composer {$selectedVersion} 吗？", false)) {
                $this->ui->info('已取消删除');
                return 0;
            }
            
            // 执行删除
            $composerRemoveCommand = new ComposerRemoveCommand();
            return $composerRemoveCommand->execute(["--version={$selectedVersion}"]);
            
        } catch (\Exception $e) {
            $this->ui->error('删除失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 更新Composer
     *
     * @return int
     */
    private function updateComposer()
    {
        try {
            $currentVersion = $this->composerManager->getCurrentVersion();
            
            if (!$currentVersion) {
                $this->ui->error('当前没有安装Composer');
                return 1;
            }
            
            if ($this->ui->confirm("确定要更新当前的 Composer {$currentVersion} 吗？")) {
                // 执行更新
                $this->ui->info('正在更新Composer...', true);
                $output = shell_exec('composer self-update 2>&1');
                
                if ($output) {
                    $this->ui->info($output, false);
                }
                
                $this->ui->success('Composer更新完成', true);
                return 0;
            } else {
                $this->ui->info('已取消更新');
                return 0;
            }
            
        } catch (\Exception $e) {
            $this->ui->error('更新失败: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 配置Composer
     *
     * @return int
     */
    private function configureComposer()
    {
        $composerConfigCommand = new ComposerConfigCommand();
        return $composerConfigCommand->execute([]);
    }
    
    /**
     * 管理全局包
     *
     * @return int
     */
    private function manageGlobalPackages()
    {
        try {
            $currentVersion = $this->composerManager->getCurrentVersion();
            
            if (!$currentVersion) {
                $this->ui->error('当前没有安装Composer');
                return 1;
            }
            
            $globalOptions = [
                'list' => '列出全局包',
                'install' => '安装全局包',
                'remove' => '删除全局包',
                'update' => '更新全局包',
            ];
            
            $selectedAction = $this->ui->menu($globalOptions, '请选择全局包管理操作:');
            
            if (!$selectedAction) {
                $this->ui->info('已取消操作');
                return 0;
            }
            
            switch ($selectedAction) {
                case 'list':
                    $this->ui->info('正在获取全局包列表...', true);
                    $output = shell_exec('composer global show 2>&1');
                    if ($output) {
                        $this->ui->info($output, false);
                    }
                    break;
                    
                case 'install':
                    $package = $this->ui->prompt('请输入要安装的包名: ');
                    if (!empty($package)) {
                        $this->ui->info("正在安装全局包 {$package}...", true);
                        $output = shell_exec("composer global require {$package} 2>&1");
                        if ($output) {
                            $this->ui->info($output, false);
                        }
                    }
                    break;
                    
                case 'remove':
                    $package = $this->ui->prompt('请输入要删除的包名: ');
                    if (!empty($package)) {
                        if ($this->ui->confirm("确定要删除全局包 {$package} 吗？", false)) {
                            $this->ui->info("正在删除全局包 {$package}...", true);
                            $output = shell_exec("composer global remove {$package} 2>&1");
                            if ($output) {
                                $this->ui->info($output, false);
                            }
                        }
                    }
                    break;
                    
                case 'update':
                    if ($this->ui->confirm('确定要更新所有全局包吗？')) {
                        $this->ui->info('正在更新全局包...', true);
                        $output = shell_exec('composer global update 2>&1');
                        if ($output) {
                            $this->ui->info($output, false);
                        }
                    }
                    break;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->ui->error('操作失败: ' . $e->getMessage());
            return 1;
        }
    }
}
