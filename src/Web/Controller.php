<?php

namespace VersionManager\Web;

use VersionManager\Core\VersionManager;
use VersionManager\Core\ExtensionManager;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\System\MonitorManager;
use VersionManager\Core\Config\PhpConfig;
use VersionManager\Core\VersionSwitcher;

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
        $this->versionManager = new VersionManager();
        $this->extensionManager = new ExtensionManager();
        $this->composerManager = new ComposerManager();
        $this->monitorManager = new MonitorManager();
        $this->versionSwitcher = new VersionSwitcher();
        $this->view = new View();
    }

    /**
     * 检查是否有管理员权限
     *
     * @return bool
     */
    private function hasAdminPrivileges()
    {
        // 检查是否为root用户
        if (function_exists('posix_getuid')) {
            return posix_getuid() === 0;
        }

        // 备用检查方法
        $output = [];
        $returnCode = 0;
        exec('id -u 2>/dev/null', $output, $returnCode);

        return $returnCode === 0 && isset($output[0]) && trim($output[0]) === '0';
    }

    /**
     * 检查是否可以执行sudo命令
     *
     * @return bool
     */
    private function canUseSudo()
    {
        $output = [];
        $returnCode = 0;
        exec('sudo -n true 2>/dev/null', $output, $returnCode);

        return $returnCode === 0;
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

            case 'actions/restart-fpm':
                return $this->actionRestartFpm();

            case 'actions/clear-cache':
                return $this->actionClearCache();

            case 'actions/use':
                return $this->actionUse();

            case 'actions/remove':
                return $this->actionRemove();

            case 'actions/install':
                return $this->actionInstall();

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

        // 获取权限信息
        $hasRoot = $this->hasAdminPrivileges();
        $canSudo = $this->canUseSudo();
        $privilegeStatus = $hasRoot ? 'root' : ($canSudo ? 'sudo' : 'limited');

        // 渲染视图
        return $this->view->render('dashboard', [
            'title' => 'PVM 管理面板 - 仪表盘',
            'currentVersion' => $currentVersion,
            'installedVersions' => $installedVersions,
            'installedExtensions' => $installedExtensions,
            'systemInfo' => $systemInfo,
            'privilegeStatus' => $privilegeStatus,
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

    /**
     * 处理重启PHP-FPM操作
     */
    public function actionRestartFpm()
    {
        try {
            // 检查权限
            $hasRoot = $this->hasAdminPrivileges();
            $canSudo = $this->canUseSudo();

            if (!$hasRoot && !$canSudo) {
                $message = '权限不足！请以管理员权限运行 PVM Web 服务：sudo pvm web';
                $type = 'error';
                header('Location: /?message=' . urlencode($message) . '&type=' . $type);
                exit;
            }

            // 检查PHP-FPM是否运行
            $output = [];
            $returnCode = 0;
            exec('pgrep -f php-fpm', $output, $returnCode);

            $command = '';
            if ($returnCode === 0) {
                // PHP-FPM正在运行，重启它
                if ($hasRoot) {
                    $command = 'systemctl restart php-fpm 2>&1';
                } else {
                    $command = 'sudo -n systemctl restart php-fpm 2>&1';
                }
            } else {
                // PHP-FPM未运行，尝试启动
                if ($hasRoot) {
                    $command = 'systemctl start php-fpm 2>&1';
                } else {
                    $command = 'sudo -n systemctl start php-fpm 2>&1';
                }
            }

            // 执行命令
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $message = $returnCode === 0 && count($output) === 0 ?
                    'PHP-FPM 操作成功' :
                    'PHP-FPM 重启成功';
                $type = 'success';
            } else {
                $errorOutput = implode('; ', $output);
                if (strpos($errorOutput, 'not found') !== false) {
                    $message = 'PHP-FPM 服务未安装。请先安装 PHP-FPM：sudo apt install php-fpm';
                } else {
                    $message = 'PHP-FPM 操作失败: ' . $errorOutput;
                }
                $type = 'error';
            }
        } catch (\Exception $e) {
            $message = '操作失败: ' . $e->getMessage();
            $type = 'error';
        }

        // 重定向回仪表盘并显示消息
        header('Location: /?message=' . urlencode($message) . '&type=' . $type);
        exit;
    }

    /**
     * 处理清除缓存操作
     */
    public function actionClearCache()
    {
        try {
            $cacheCleared = false;
            $messages = [];

            // 清除OPcache
            if (function_exists('opcache_reset')) {
                if (opcache_reset()) {
                    $messages[] = 'OPcache 清除成功';
                    $cacheCleared = true;
                } else {
                    $messages[] = 'OPcache 清除失败';
                }
            } else {
                $messages[] = 'OPcache 未启用';
            }

            // 清除APCu缓存
            if (function_exists('apcu_clear_cache')) {
                if (apcu_clear_cache()) {
                    $messages[] = 'APCu 缓存清除成功';
                    $cacheCleared = true;
                } else {
                    $messages[] = 'APCu 缓存清除失败';
                }
            } else {
                $messages[] = 'APCu 未启用';
            }

            // 清除临时文件
            $tempDir = sys_get_temp_dir();
            $phpTempFiles = glob($tempDir . '/php*');
            $deletedFiles = 0;
            foreach ($phpTempFiles as $file) {
                if (is_file($file) && unlink($file)) {
                    $deletedFiles++;
                }
            }
            if ($deletedFiles > 0) {
                $messages[] = "清除了 {$deletedFiles} 个临时文件";
                $cacheCleared = true;
            }

            $message = implode('; ', $messages);
            $type = $cacheCleared ? 'success' : 'warning';
        } catch (\Exception $e) {
            $message = '清除缓存失败: ' . $e->getMessage();
            $type = 'error';
        }

        // 重定向回仪表盘并显示消息
        header('Location: /?message=' . urlencode($message) . '&type=' . $type);
        exit;
    }

    /**
     * 处理版本使用操作
     */
    public function actionUse()
    {
        $version = $_GET['version'] ?? '';

        if (empty($version)) {
            header('Location: /versions?message=' . urlencode('版本参数缺失') . '&type=error');
            exit;
        }

        try {
            // 使用VersionSwitcher切换版本
            $result = $this->versionSwitcher->switchVersion($version);

            if ($result) {
                $message = "成功切换到PHP版本 {$version}";
                $type = 'success';
            } else {
                $message = "切换到PHP版本 {$version} 失败";
                $type = 'error';
            }
        } catch (\Exception $e) {
            $message = '版本切换失败: ' . $e->getMessage();
            $type = 'error';
        }

        header('Location: /versions?message=' . urlencode($message) . '&type=' . $type);
        exit;
    }

    /**
     * 处理版本删除操作
     */
    public function actionRemove()
    {
        $version = $_GET['version'] ?? '';

        if (empty($version)) {
            header('Location: /versions?message=' . urlencode('版本参数缺失') . '&type=error');
            exit;
        }

        try {
            // 检查是否为当前版本
            $currentVersion = $this->versionSwitcher->getCurrentVersion();
            if ($version === $currentVersion) {
                $message = '不能删除当前正在使用的PHP版本';
                $type = 'error';
            } else {
                // 使用VersionManager删除版本
                $result = $this->versionManager->remove($version);

                if ($result) {
                    $message = "成功删除PHP版本 {$version}";
                    $type = 'success';
                } else {
                    $message = "删除PHP版本 {$version} 失败";
                    $type = 'error';
                }
            }
        } catch (\Exception $e) {
            $message = '版本删除失败: ' . $e->getMessage();
            $type = 'error';
        }

        header('Location: /versions?message=' . urlencode($message) . '&type=' . $type);
        exit;
    }

    /**
     * 处理版本安装操作
     */
    public function actionInstall()
    {
        $version = $_GET['version'] ?? '';

        if (empty($version)) {
            header('Location: /versions?message=' . urlencode('版本参数缺失') . '&type=error');
            exit;
        }

        try {
            // 检查权限
            $hasRoot = $this->hasAdminPrivileges();
            $canSudo = $this->canUseSudo();

            if (!$hasRoot && !$canSudo) {
                $message = '权限不足！安装PHP版本需要管理员权限，请以管理员权限运行 PVM Web 服务：sudo pvm web';
                $type = 'error';
                header('Location: /versions?message=' . urlencode($message) . '&type=' . $type);
                exit;
            }

            // 检查版本是否已安装
            $installedVersions = $this->versionManager->getInstalledVersions();
            foreach ($installedVersions as $installedVersion) {
                if ($installedVersion['version'] === $version) {
                    $message = "PHP版本 {$version} 已经安装";
                    $type = 'warning';
                    header('Location: /versions?message=' . urlencode($message) . '&type=' . $type);
                    exit;
                }
            }

            // 使用VersionManager安装版本
            $result = $this->versionManager->install($version);

            if ($result) {
                $message = "成功安装PHP版本 {$version}";
                $type = 'success';
            } else {
                $message = "安装PHP版本 {$version} 失败";
                $type = 'error';
            }
        } catch (\Exception $e) {
            $message = '版本安装失败: ' . $e->getMessage();
            $type = 'error';
        }

        header('Location: /versions?message=' . urlencode($message) . '&type=' . $type);
        exit;
    }
}
