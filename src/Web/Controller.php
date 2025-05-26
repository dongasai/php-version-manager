<?php

namespace VersionManager\Web;

use VersionManager\Core\VersionManager;
use VersionManager\Core\ExtensionManager;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\System\MonitorManager;
use VersionManager\Core\Config\PhpConfig;

/**
 * Web控制器类
 *
 * 处理Web请求并返回响应
 */
class Controller
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
     * 监控管理器
     *
     * @var MonitorManager
     */
    private $monitorManager;

    /**
     * 视图
     *
     * @var View
     */
    private $view;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->versionManager = new VersionManager();
        $this->extensionManager = new ExtensionManager();
        $this->composerManager = new ComposerManager();
        $this->monitorManager = new MonitorManager();
        $this->view = new View();
    }

    /**
     * 处理请求
     *
     * @param string $uri 请求URI
     * @return string 响应内容
     */
    public function handleRequest($uri)
    {
        // 解析URI
        $path = parse_url($uri, PHP_URL_PATH);
        $path = trim($path, '/');

        // 路由请求
        switch ($path) {
            case '':
            case 'index':
            case 'home':
                return $this->showDashboard();

            case 'versions':
                return $this->showVersions();

            case 'extensions':
                return $this->showExtensions();

            case 'composer':
                return $this->showComposer();

            case 'monitor':
                return $this->showMonitor();

            case 'settings':
                return $this->showSettings();

            case 'api/versions':
                return $this->apiVersions();

            case 'api/extensions':
                return $this->apiExtensions();

            case 'api/monitor':
                return $this->apiMonitor();

            default:
                return $this->show404();
        }
    }

    /**
     * 显示仪表盘
     *
     * @return string 响应内容
     */
    private function showDashboard()
    {
        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取已安装的PHP版本
        $installedVersions = $this->versionManager->getInstalledVersions();

        // 获取已安装的扩展
        $installedExtensions = $this->extensionManager->getInstalledExtensions();

        // 获取系统信息
        $systemInfo = $this->monitorManager->getSystemInfo();

        // 渲染视图
        return $this->view->render('dashboard', [
            'title' => 'PVM 管理面板 - 仪表盘',
            'currentVersion' => $currentVersion,
            'installedVersions' => $installedVersions,
            'installedExtensions' => $installedExtensions,
            'systemInfo' => $systemInfo,
        ]);
    }

    /**
     * 显示版本管理页面
     *
     * @return string 响应内容
     */
    private function showVersions()
    {
        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取已安装的PHP版本
        $installedVersions = $this->versionManager->getInstalledVersions();

        // 获取可用的PHP版本
        $availableVersions = $this->versionManager->getAvailableVersions();

        // 渲染视图
        return $this->view->render('versions', [
            'title' => 'PVM 管理面板 - 版本管理',
            'currentVersion' => $currentVersion,
            'installedVersions' => $installedVersions,
            'availableVersions' => $availableVersions,
        ]);
    }

    /**
     * 显示扩展管理页面
     *
     * @return string 响应内容
     */
    private function showExtensions()
    {
        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取已安装的扩展
        $installedExtensions = $this->extensionManager->getInstalledExtensions();

        // 获取可用的扩展
        $availableExtensions = $this->extensionManager->getAvailableExtensions();

        // 渲染视图
        return $this->view->render('extensions', [
            'title' => 'PVM 管理面板 - 扩展管理',
            'currentVersion' => $currentVersion,
            'installedExtensions' => $installedExtensions,
            'availableExtensions' => $availableExtensions,
        ]);
    }

    /**
     * 显示Composer管理页面
     *
     * @return string 响应内容
     */
    private function showComposer()
    {
        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取Composer版本
        $composerVersion = $this->composerManager->getComposerVersion($currentVersion);

        // 获取可用的Composer版本
        $availableComposerVersions = $this->composerManager->getAvailableComposerVersions();

        // 渲染视图
        return $this->view->render('composer', [
            'title' => 'PVM 管理面板 - Composer管理',
            'currentVersion' => $currentVersion,
            'composerVersion' => $composerVersion,
            'availableComposerVersions' => $availableComposerVersions,
        ]);
    }

    /**
     * 显示监控页面
     *
     * @return string 响应内容
     */
    private function showMonitor()
    {
        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取PHP进程
        $phpProcesses = $this->monitorManager->getPhpProcesses();

        // 获取PHP-FPM进程
        $fpmProcesses = $this->monitorManager->getFpmProcesses();

        // 获取内存使用情况
        $memoryUsage = $this->monitorManager->getMemoryUsage();

        // 获取CPU使用情况
        $cpuUsage = $this->monitorManager->getCpuUsage();

        // 渲染视图
        return $this->view->render('monitor', [
            'title' => 'PVM 管理面板 - 状态监控',
            'currentVersion' => $currentVersion,
            'phpProcesses' => $phpProcesses,
            'fpmProcesses' => $fpmProcesses,
            'memoryUsage' => $memoryUsage,
            'cpuUsage' => $cpuUsage,
        ]);
    }

    /**
     * 显示设置页面
     *
     * @return string 响应内容
     */
    private function showSettings()
    {
        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取PHP配置
        $phpConfig = new PhpConfig($currentVersion);
        $phpIniPath = $phpConfig->getPhpIniPath();
        $phpIniValues = $phpConfig->getPhpIniValues();

        // 渲染视图
        return $this->view->render('settings', [
            'title' => 'PVM 管理面板 - 设置',
            'currentVersion' => $currentVersion,
            'phpIniPath' => $phpIniPath,
            'phpIniValues' => $phpIniValues,
        ]);
    }

    /**
     * 显示404页面
     *
     * @return string 响应内容
     */
    private function show404()
    {
        // 设置HTTP状态码
        http_response_code(404);

        // 渲染视图
        return $this->view->render('404', [
            'title' => 'PVM 管理面板 - 页面未找到',
        ]);
    }

    /**
     * API: 获取版本信息
     *
     * @return string JSON响应
     */
    private function apiVersions()
    {
        // 设置内容类型
        header('Content-Type: application/json');

        // 获取数据
        $data = [
            'current' => $this->versionManager->getCurrentVersion(),
            'installed' => $this->versionManager->getInstalledVersions(),
            'available' => $this->versionManager->getAvailableVersions(),
        ];

        // 返回JSON
        return json_encode($data);
    }

    /**
     * API: 获取扩展信息
     *
     * @return string JSON响应
     */
    private function apiExtensions()
    {
        // 设置内容类型
        header('Content-Type: application/json');

        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取数据
        $data = [
            'installed' => $this->extensionManager->getInstalledExtensions(),
            'available' => $this->extensionManager->getAvailableExtensions(),
        ];

        // 返回JSON
        return json_encode($data);
    }

    /**
     * API: 获取监控信息
     *
     * @return string JSON响应
     */
    private function apiMonitor()
    {
        // 设置内容类型
        header('Content-Type: application/json');

        // 获取数据
        $data = [
            'php_processes' => $this->monitorManager->getPhpProcesses(),
            'fpm_processes' => $this->monitorManager->getFpmProcesses(),
            'memory_usage' => $this->monitorManager->getMemoryUsage(),
            'cpu_usage' => $this->monitorManager->getCpuUsage(),
            'system_info' => $this->monitorManager->getSystemInfo(),
        ];

        // 返回JSON
        return json_encode($data);
    }
}
