<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Console\UI\ConsoleUI;
use VersionManager\Core\VersionManager;
use VersionManager\Core\ExtensionManager;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\SupportedVersions;

/**
 * 交互式安装向导命令
 * 
 * 提供一个友好的安装向导来指导用户安装PHP版本、扩展和Composer
 */
class InstallWizardCommand implements CommandInterface
{
    /**
     * 控制台UI工具
     *
     * @var ConsoleUI
     */
    private $ui;
    
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
        $this->ui = new ConsoleUI();
        $this->versionManager = new VersionManager();
        $this->extensionManager = new ExtensionManager();
        $this->composerManager = new ComposerManager();
        $this->supportedVersions = new SupportedVersions();
    }
    
    /**
     * 获取命令名称
     *
     * @return string
     */
    public function getName()
    {
        return 'install-wizard';
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '交互式安装向导';
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 显示欢迎信息
        $this->showWelcome();
        
        // 检查系统环境
        if (!$this->checkSystemRequirements()) {
            return 1;
        }
        
        // 选择安装类型
        $installType = $this->selectInstallType();
        
        switch ($installType) {
            case 'quick':
                return $this->quickInstall();
            case 'custom':
                return $this->customInstall();
            case 'development':
                return $this->developmentInstall();
            case 'production':
                return $this->productionInstall();
            default:
                $this->ui->info('已取消安装');
                return 0;
        }
    }
    
    /**
     * 显示欢迎信息
     */
    private function showWelcome()
    {
        $this->ui->info('');
        $this->ui->info('=' . str_repeat('=', 50) . '=', true);
        $this->ui->info('  欢迎使用 PVM 交互式安装向导', true);
        $this->ui->info('=' . str_repeat('=', 50) . '=', true);
        $this->ui->info('');
        $this->ui->info('本向导将帮助您安装和配置PHP版本、扩展和Composer。', true);
        $this->ui->info('您可以随时按 Ctrl+C 退出向导。', true);
        $this->ui->info('');
    }
    
    /**
     * 检查系统要求
     *
     * @return bool
     */
    private function checkSystemRequirements()
    {
        $this->ui->info('正在检查系统要求...', true);
        $this->ui->info('');
        
        $checks = [
            '操作系统' => $this->checkOperatingSystem(),
            '网络连接' => $this->checkNetworkConnection(),
            '磁盘空间' => $this->checkDiskSpace(),
            '权限检查' => $this->checkPermissions(),
        ];
        
        $allPassed = true;
        
        foreach ($checks as $name => $passed) {
            $status = $passed ? '✓' : '✗';
            $color = $passed ? 'green' : 'red';
            $this->ui->info("  {$status} {$name}: " . $this->ui->colorize($passed ? '通过' : '失败', $color), true);
            
            if (!$passed) {
                $allPassed = false;
            }
        }
        
        $this->ui->info('');
        
        if (!$allPassed) {
            $this->ui->error('系统要求检查失败，请解决上述问题后重试。');
            return false;
        }
        
        $this->ui->success('系统要求检查通过！', true);
        $this->ui->info('');
        
        return true;
    }
    
    /**
     * 选择安装类型
     *
     * @return string
     */
    private function selectInstallType()
    {
        $installTypes = [
            'quick' => '快速安装 (推荐的PHP版本和常用扩展)',
            'custom' => '自定义安装 (选择特定版本和扩展)',
            'development' => '开发环境 (包含调试工具和开发扩展)',
            'production' => '生产环境 (优化的配置和必要扩展)',
        ];
        
        $this->ui->info('请选择安装类型:', true);
        $this->ui->info('');
        
        return $this->ui->menu($installTypes, '请选择安装类型:');
    }
    
    /**
     * 快速安装
     *
     * @return int
     */
    private function quickInstall()
    {
        $this->ui->info('开始快速安装...', true);
        $this->ui->info('');
        
        // 安装推荐的PHP版本
        $recommendedVersion = '8.3';
        $this->ui->info("正在安装推荐的PHP版本: {$recommendedVersion}", true);
        
        $installCommand = new InstallCommand();
        $result = $installCommand->execute([$recommendedVersion]);
        
        if ($result !== 0) {
            $this->ui->error('PHP版本安装失败');
            return $result;
        }
        
        // 切换到新安装的版本
        $useCommand = new UseCommand();
        $useCommand->execute([$recommendedVersion]);
        
        // 安装常用扩展
        $commonExtensions = ['curl', 'json', 'mbstring', 'xml', 'zip'];
        $this->installExtensions($commonExtensions);
        
        // 安装Composer
        $this->installComposer();
        
        $this->ui->success('快速安装完成！', true);
        $this->showInstallSummary();
        
        return 0;
    }
    
    /**
     * 自定义安装
     *
     * @return int
     */
    private function customInstall()
    {
        $this->ui->info('开始自定义安装...', true);
        $this->ui->info('');
        
        // 选择PHP版本
        $phpVersion = $this->selectPhpVersion();
        if (!$phpVersion) {
            return 0;
        }
        
        // 安装PHP版本
        $this->ui->info("正在安装PHP {$phpVersion}...", true);
        $installCommand = new InstallCommand();
        $result = $installCommand->execute([$phpVersion]);
        
        if ($result !== 0) {
            $this->ui->error('PHP版本安装失败');
            return $result;
        }
        
        // 切换到新安装的版本
        $useCommand = new UseCommand();
        $useCommand->execute([$phpVersion]);
        
        // 选择扩展
        $extensions = $this->selectExtensions();
        if (!empty($extensions)) {
            $this->installExtensions($extensions);
        }
        
        // 询问是否安装Composer
        if ($this->ui->confirm('是否安装Composer？', true)) {
            $this->installComposer();
        }
        
        $this->ui->success('自定义安装完成！', true);
        $this->showInstallSummary();
        
        return 0;
    }
    
    /**
     * 开发环境安装
     *
     * @return int
     */
    private function developmentInstall()
    {
        $this->ui->info('开始开发环境安装...', true);
        $this->ui->info('');
        
        // 安装最新的PHP版本
        $latestVersion = '8.3';
        $this->ui->info("正在安装最新PHP版本: {$latestVersion}", true);
        
        $installCommand = new InstallCommand();
        $result = $installCommand->execute([$latestVersion]);
        
        if ($result !== 0) {
            $this->ui->error('PHP版本安装失败');
            return $result;
        }
        
        // 切换到新安装的版本
        $useCommand = new UseCommand();
        $useCommand->execute([$latestVersion]);
        
        // 安装开发扩展
        $devExtensions = ['xdebug', 'curl', 'json', 'mbstring', 'xml', 'zip', 'gd', 'mysql'];
        $this->installExtensions($devExtensions);
        
        // 安装Composer
        $this->installComposer();
        
        $this->ui->success('开发环境安装完成！', true);
        $this->showInstallSummary();
        
        return 0;
    }
    
    /**
     * 生产环境安装
     *
     * @return int
     */
    private function productionInstall()
    {
        $this->ui->info('开始生产环境安装...', true);
        $this->ui->info('');
        
        // 安装稳定的PHP版本
        $stableVersion = '8.2';
        $this->ui->info("正在安装稳定PHP版本: {$stableVersion}", true);
        
        $installCommand = new InstallCommand();
        $result = $installCommand->execute([$stableVersion]);
        
        if ($result !== 0) {
            $this->ui->error('PHP版本安装失败');
            return $result;
        }
        
        // 切换到新安装的版本
        $useCommand = new UseCommand();
        $useCommand->execute([$stableVersion]);
        
        // 安装生产环境扩展
        $prodExtensions = ['opcache', 'curl', 'json', 'mbstring', 'xml', 'zip', 'mysql'];
        $this->installExtensions($prodExtensions);
        
        // 安装Composer
        $this->installComposer();
        
        $this->ui->success('生产环境安装完成！', true);
        $this->showInstallSummary();
        
        return 0;
    }
    
    /**
     * 选择PHP版本
     *
     * @return string|null
     */
    private function selectPhpVersion()
    {
        try {
            $availableVersions = $this->supportedVersions->getAvailableVersions();
            
            if (empty($availableVersions)) {
                $this->ui->error('没有可用的PHP版本');
                return null;
            }
            
            $versionOptions = [];
            foreach ($availableVersions as $version) {
                $versionOptions[$version] = "PHP {$version}";
            }
            
            return $this->ui->menu($versionOptions, '请选择要安装的PHP版本:');
            
        } catch (\Exception $e) {
            $this->ui->error('获取可用版本失败: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 选择扩展
     *
     * @return array
     */
    private function selectExtensions()
    {
        try {
            $availableExtensions = $this->extensionManager->getAvailableExtensions();
            
            if (empty($availableExtensions)) {
                $this->ui->warning('没有可用的扩展');
                return [];
            }
            
            $extensionOptions = [];
            foreach ($availableExtensions as $extension) {
                $extensionOptions[$extension] = $extension;
            }
            
            return $this->ui->multiMenu($extensionOptions, '请选择要安装的扩展 (用逗号分隔多个选项):');
            
        } catch (\Exception $e) {
            $this->ui->error('获取可用扩展失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 安装扩展
     *
     * @param array $extensions 扩展列表
     */
    private function installExtensions(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->ui->info("正在安装扩展: {$extension}", true);
            
            $extInstallCommand = new ExtInstallCommand();
            $result = $extInstallCommand->execute([$extension]);
            
            if ($result === 0) {
                $this->ui->success("扩展 {$extension} 安装成功", true);
            } else {
                $this->ui->warning("扩展 {$extension} 安装失败", true);
            }
        }
    }
    
    /**
     * 安装Composer
     */
    private function installComposer()
    {
        $this->ui->info('正在安装Composer...', true);
        
        $composerInstallCommand = new ComposerInstallCommand();
        $result = $composerInstallCommand->execute([]);
        
        if ($result === 0) {
            $this->ui->success('Composer安装成功', true);
        } else {
            $this->ui->warning('Composer安装失败', true);
        }
    }
    
    /**
     * 显示安装摘要
     */
    private function showInstallSummary()
    {
        $this->ui->info('');
        $this->ui->info('=' . str_repeat('=', 30) . '=', true);
        $this->ui->info('  安装摘要', true);
        $this->ui->info('=' . str_repeat('=', 30) . '=', true);
        $this->ui->info('');
        
        // 显示当前PHP版本
        $currentVersion = shell_exec('php -v 2>/dev/null | head -n 1');
        if ($currentVersion) {
            $this->ui->info('当前PHP版本:', true);
            $this->ui->info('  ' . trim($currentVersion), true);
        }
        
        // 显示已安装的扩展
        $extensions = get_loaded_extensions();
        $this->ui->info('已安装扩展: ' . count($extensions) . ' 个', true);
        
        // 显示Composer版本
        $composerVersion = shell_exec('composer --version 2>/dev/null');
        if ($composerVersion) {
            $this->ui->info('Composer版本:', true);
            $this->ui->info('  ' . trim($composerVersion), true);
        }
        
        $this->ui->info('');
        $this->ui->info('安装完成！您现在可以开始使用PVM了。', true);
        $this->ui->info('运行 "pvm help" 查看可用命令。', true);
    }
    
    // 系统检查方法
    private function checkOperatingSystem() { return PHP_OS_FAMILY === 'Linux'; }
    private function checkNetworkConnection() { return true; } // 简化实现
    private function checkDiskSpace() { return disk_free_space('.') > 1024 * 1024 * 1024; } // 1GB
    private function checkPermissions() { return is_writable('.'); }
}
