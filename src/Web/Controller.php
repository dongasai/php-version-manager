<?php

namespace VersionManager\Web;

use VersionManager\Core\VersionManager;
use VersionManager\Core\ExtensionManager;
use VersionManager\Core\ComposerManager;
use VersionManager\Core\System\MonitorManager;
use VersionManager\Core\Config\PhpConfig;
use VersionManager\Core\Config\MirrorConfig;
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
     * 镜像配置
     *
     * @var MirrorConfig
     */
    private $mirrorConfig;

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
        $this->mirrorConfig = new MirrorConfig();
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
     * 获取权限状态
     *
     * @return string
     */
    private function getPrivilegeStatus()
    {
        $hasRoot = $this->hasAdminPrivileges();
        $canSudo = $this->canUseSudo();

        return $hasRoot ? 'root' : ($canSudo ? 'sudo' : 'limited');
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

            case 'mirrors':
                return $this->showMirrors();

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

            case 'actions/set-mirror':
                return $this->actionSetMirror();

            case 'actions/add-mirror':
                return $this->actionAddMirror();

            case 'install-progress':
                return $this->showInstallProgress();

            case 'api/versions':
                return $this->apiVersions();

            case 'api/extensions':
                return $this->apiExtensions();

            case 'api/monitor':
                return $this->apiMonitor();

            case 'api/install-status':
                return $this->apiInstallStatus();

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
        $privilegeStatus = $this->getPrivilegeStatus();

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
            'privilegeStatus' => $this->getPrivilegeStatus(),
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
            'privilegeStatus' => $this->getPrivilegeStatus(),
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
            'privilegeStatus' => $this->getPrivilegeStatus(),
        ]);
    }

    /**
     * 显示镜像管理页面
     *
     * @return string 响应内容
     */
    private function showMirrors()
    {
        // 获取当前PHP版本
        $currentVersion = $this->versionManager->getCurrentVersion();

        // 获取所有镜像配置
        $phpMirrors = $this->mirrorConfig->getAllPhpMirrors();
        $peclMirrors = $this->mirrorConfig->getAllPeclMirrors();
        $composerMirrors = $this->mirrorConfig->getAllComposerMirrors();

        // 获取当前默认镜像
        $currentPhpMirror = $this->mirrorConfig->getPhpMirror();
        $currentPeclMirror = $this->mirrorConfig->getPeclMirror();
        $currentComposerMirror = $this->mirrorConfig->getComposerMirror();

        // 渲染视图
        return $this->view->render('mirrors', [
            'title' => 'PVM 管理面板 - 镜像管理',
            'currentVersion' => $currentVersion,
            'phpMirrors' => $phpMirrors,
            'peclMirrors' => $peclMirrors,
            'composerMirrors' => $composerMirrors,
            'currentPhpMirror' => $currentPhpMirror,
            'currentPeclMirror' => $currentPeclMirror,
            'currentComposerMirror' => $currentComposerMirror,
            'privilegeStatus' => $this->getPrivilegeStatus(),
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
            'privilegeStatus' => $this->getPrivilegeStatus(),
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
            'privilegeStatus' => $this->getPrivilegeStatus(),
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
                // 检查版本是否为不完整安装
                $installedVersions = $this->versionManager->getInstalledVersions();
                $isIncomplete = false;

                foreach ($installedVersions as $installedVersion) {
                    if ($installedVersion['version'] === $version && $installedVersion['status'] === 'incomplete') {
                        $isIncomplete = true;
                        break;
                    }
                }

                if ($isIncomplete) {
                    // 对于不完整的版本，直接删除目录
                    $homeDir = getenv('HOME') ?: '/root';
                    $versionDir = $homeDir . '/.pvm/versions/' . $version;

                    if (!$versionDir || $versionDir === $homeDir . '/.pvm/versions/') {
                        throw new \Exception('无效的版本目录路径');
                    }

                    if (is_dir($versionDir)) {
                        // 使用系统命令删除目录
                        $command = "rm -rf " . escapeshellarg($versionDir);
                        exec($command, $output, $returnCode);

                        if ($returnCode === 0) {
                            $message = "成功删除不完整的PHP版本 {$version}";
                            $type = 'success';
                        } else {
                            $message = "删除不完整版本 {$version} 失败";
                            $type = 'error';
                        }
                    } else {
                        $message = "版本目录不存在: {$version}";
                        $type = 'warning';
                    }
                } else {
                    // 使用VersionManager删除完整安装的版本
                    $result = $this->versionManager->remove($version);

                    if ($result) {
                        $message = "成功删除PHP版本 {$version}";
                        $type = 'success';
                    } else {
                        $message = "删除PHP版本 {$version} 失败";
                        $type = 'error';
                    }
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

            // 启动异步安装
            $taskId = $this->startAsyncInstall($version);

            if ($taskId) {
                // 重定向到安装进度页面
                header('Location: /install-progress?task_id=' . $taskId . '&version=' . urlencode($version));
                exit;
            } else {
                $message = "启动安装任务失败";
                $type = 'error';
                header('Location: /versions?message=' . urlencode($message) . '&type=' . $type);
                exit;
            }
        } catch (\Exception $e) {
            $message = '版本安装失败: ' . $e->getMessage();
            $type = 'error';
            header('Location: /versions?message=' . urlencode($message) . '&type=' . $type);
            exit;
        }
    }

    /**
     * 启动异步安装任务
     *
     * @param string $version PHP版本
     * @return string|false 任务ID或false
     */
    private function startAsyncInstall($version)
    {
        // 生成任务ID
        $taskId = uniqid('install_', true);

        // 创建任务目录
        $taskDir = sys_get_temp_dir() . '/pvm_tasks';
        if (!is_dir($taskDir)) {
            mkdir($taskDir, 0755, true);
        }

        // 清理旧任务文件（超过24小时的）
        $this->cleanupOldTasks($taskDir);

        // 创建任务状态文件
        $taskFile = $taskDir . '/' . $taskId . '.json';
        $taskData = [
            'id' => $taskId,
            'version' => $version,
            'status' => 'starting',
            'progress' => 0,
            'message' => '正在启动安装任务...',
            'start_time' => time(),
            'log' => []
        ];

        file_put_contents($taskFile, json_encode($taskData, JSON_PRETTY_PRINT));

        // 构建安装命令
        $pvmBin = dirname(dirname(__DIR__)) . '/bin/pvm';
        $logFile = $taskDir . '/' . $taskId . '.log';

        // 使用nohup在后台执行安装命令
        $command = sprintf(
            'nohup %s install %s --yes > %s 2>&1 & echo $!',
            escapeshellarg($pvmBin),
            escapeshellarg($version),
            escapeshellarg($logFile)
        );

        // 执行命令并获取进程ID
        $output = [];
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            $pid = trim($output[0]);

            // 更新任务状态
            $taskData['status'] = 'running';
            $taskData['progress'] = 5;
            $taskData['message'] = '安装任务已启动...';
            $taskData['pid'] = $pid;

            file_put_contents($taskFile, json_encode($taskData, JSON_PRETTY_PRINT));

            return $taskId;
        }

        return false;
    }

    /**
     * 显示安装进度页面
     *
     * @return string 响应内容
     */
    private function showInstallProgress()
    {
        $taskId = $_GET['task_id'] ?? '';
        $version = $_GET['version'] ?? '';

        if (empty($taskId) || empty($version)) {
            header('Location: /versions?message=' . urlencode('任务参数缺失') . '&type=error');
            exit;
        }

        // 渲染安装进度页面
        return $this->view->render('install-progress', [
            'title' => 'PVM 管理面板 - 安装进度',
            'taskId' => $taskId,
            'version' => $version,
        ]);
    }

    /**
     * API: 获取安装状态
     *
     * @return string JSON响应
     */
    private function apiInstallStatus()
    {
        header('Content-Type: application/json');

        $taskId = $_GET['task_id'] ?? '';

        if (empty($taskId)) {
            return json_encode(['error' => '任务ID缺失']);
        }

        // 获取任务状态
        $taskDir = sys_get_temp_dir() . '/pvm_tasks';
        $taskFile = $taskDir . '/' . $taskId . '.json';
        $logFile = $taskDir . '/' . $taskId . '.log';

        if (!file_exists($taskFile)) {
            return json_encode(['error' => '任务不存在']);
        }

        $taskData = json_decode(file_get_contents($taskFile), true);

        // 检查进程是否还在运行
        if (isset($taskData['pid'])) {
            $pid = $taskData['pid'];
            $isRunning = $this->isProcessRunning($pid);

            if (!$isRunning && $taskData['status'] === 'running') {
                // 进程已结束，检查安装结果
                $this->updateTaskStatus($taskId, $taskData);
            }
        }

        // 读取日志文件
        $log = [];
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $log = array_filter(explode("\n", $logContent));

            // 分析日志内容更新进度
            $this->analyzeLogAndUpdateProgress($taskId, $taskData, $log);
        }

        // 重新读取更新后的任务数据
        if (file_exists($taskFile)) {
            $taskData = json_decode(file_get_contents($taskFile), true);
        }

        // 添加日志内容到响应中
        if (!empty($log)) {
            // 只返回最后100行日志，避免响应过大
            $taskData['log_lines'] = array_slice($log, -100);
        } else {
            $taskData['log_lines'] = [];
        }

        return json_encode($taskData);
    }

    /**
     * 检查进程是否正在运行
     *
     * @param int $pid 进程ID
     * @return bool 是否正在运行
     */
    private function isProcessRunning($pid)
    {
        $output = [];
        $returnCode = 0;
        exec("ps -p {$pid}", $output, $returnCode);

        return $returnCode === 0 && count($output) > 1;
    }

    /**
     * 更新任务状态
     *
     * @param string $taskId 任务ID
     * @param array $taskData 任务数据
     */
    private function updateTaskStatus($taskId, &$taskData)
    {
        $taskDir = sys_get_temp_dir() . '/pvm_tasks';
        $taskFile = $taskDir . '/' . $taskId . '.json';
        $logFile = $taskDir . '/' . $taskId . '.log';

        // 检查安装是否成功
        $version = $taskData['version'];
        $isInstalled = $this->versionManager->isVersionInstalled($version);

        if ($isInstalled) {
            $taskData['status'] = 'completed';
            $taskData['progress'] = 100;
            $taskData['message'] = "PHP版本 {$version} 安装成功！";
            $taskData['end_time'] = time();
        } else {
            $taskData['status'] = 'failed';
            $taskData['progress'] = 0;
            $taskData['message'] = "PHP版本 {$version} 安装失败";
            $taskData['end_time'] = time();

            // 尝试从日志中获取错误信息
            if (file_exists($logFile)) {
                $logContent = file_get_contents($logFile);
                $lines = array_filter(explode("\n", $logContent));
                $errorLines = array_filter($lines, function($line) {
                    return stripos($line, 'error') !== false ||
                           stripos($line, 'failed') !== false ||
                           stripos($line, '错误') !== false ||
                           stripos($line, '失败') !== false;
                });

                if (!empty($errorLines)) {
                    $taskData['error'] = implode("\n", array_slice($errorLines, -3));
                }
            }
        }

        file_put_contents($taskFile, json_encode($taskData, JSON_PRETTY_PRINT));
    }

    /**
     * 分析日志并更新进度
     *
     * @param string $taskId 任务ID
     * @param array $taskData 任务数据
     * @param array $log 日志行
     */
    private function analyzeLogAndUpdateProgress($taskId, &$taskData, $log)
    {
        $taskDir = sys_get_temp_dir() . '/pvm_tasks';
        $taskFile = $taskDir . '/' . $taskId . '.json';

        $updated = false;
        $currentProgress = $taskData['progress'];
        $currentMessage = $taskData['message'];

        // 分析日志内容，更新进度
        foreach ($log as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // 根据关键词判断进度，使用更精确的匹配
            if ((stripos($line, '下载PHP') !== false || stripos($line, 'downloading') !== false) && $currentProgress < 10) {
                $currentProgress = 10;
                $currentMessage = '正在下载PHP源码...';
                $updated = true;
            } elseif ((stripos($line, '解压') !== false || stripos($line, 'extracting') !== false) && $currentProgress < 30) {
                $currentProgress = 30;
                $currentMessage = '正在解压源码...';
                $updated = true;
            } elseif ((stripos($line, './configure') !== false || stripos($line, '配置编译') !== false) && $currentProgress < 40) {
                $currentProgress = 40;
                $currentMessage = '正在配置编译选项...';
                $updated = true;
            } elseif ((stripos($line, 'make -j') !== false || stripos($line, '编译PHP') !== false) && $currentProgress < 70) {
                $currentProgress = 70;
                $currentMessage = '正在编译PHP...';
                $updated = true;
            } elseif ((stripos($line, 'make install') !== false || stripos($line, '安装PHP') !== false) && $currentProgress < 90) {
                $currentProgress = 90;
                $currentMessage = '正在安装PHP...';
                $updated = true;
            } elseif ((stripos($line, '安装完成') !== false || stripos($line, 'installation completed') !== false) && $currentProgress < 100) {
                $currentProgress = 100;
                $currentMessage = 'PHP安装完成！';
                $updated = true;
            }

            // 检测下载进度百分比
            if (preg_match('/(\d+(?:\.\d+)?)%/', $line, $matches)) {
                $downloadProgress = (float)$matches[1];
                if ($downloadProgress > 0 && $currentProgress >= 5 && $currentProgress < 30) {
                    // 下载阶段占10-29%，根据实际下载进度动态更新
                    $newProgress = min(29, 10 + ($downloadProgress * 0.19)); // 100% 下载对应 19% 总进度
                    if ($newProgress > $currentProgress || ($currentProgress >= 10 && $currentProgress < 30)) {
                        $currentProgress = $newProgress;
                        $currentMessage = "正在下载PHP源码... {$downloadProgress}%";
                        $updated = true;
                    }
                }
            }
        }

        // 如果有更新，保存到文件
        if ($updated) {
            $taskData['progress'] = $currentProgress;
            $taskData['message'] = $currentMessage;
            file_put_contents($taskFile, json_encode($taskData, JSON_PRETTY_PRINT));
        }
    }

    /**
     * 清理旧任务文件
     *
     * @param string $taskDir 任务目录
     */
    private function cleanupOldTasks($taskDir)
    {
        if (!is_dir($taskDir)) {
            return;
        }

        $files = glob($taskDir . '/*');
        $now = time();
        $maxAge = 24 * 60 * 60; // 24小时

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $maxAge) {
                unlink($file);
            }
        }
    }

    /**
     * 处理设置镜像操作
     */
    public function actionSetMirror()
    {
        $type = $_POST['type'] ?? '';
        $mirror = $_POST['mirror'] ?? '';

        if (empty($type) || empty($mirror)) {
            header('Location: /mirrors?message=' . urlencode('参数缺失') . '&type=error');
            exit;
        }

        try {
            $success = false;
            $message = '';

            switch ($type) {
                case 'php':
                    $success = $this->mirrorConfig->setDefaultPhpMirror($mirror);
                    $message = $success ? "PHP镜像已设置为: {$mirror}" : "设置PHP镜像失败";
                    break;

                case 'pecl':
                    $success = $this->mirrorConfig->setDefaultPeclMirror($mirror);
                    $message = $success ? "PECL镜像已设置为: {$mirror}" : "设置PECL镜像失败";
                    break;

                case 'composer':
                    $success = $this->mirrorConfig->setDefaultComposerMirror($mirror);
                    $message = $success ? "Composer镜像已设置为: {$mirror}" : "设置Composer镜像失败";
                    break;

                default:
                    $message = '不支持的镜像类型';
                    break;
            }

            $messageType = $success ? 'success' : 'error';
        } catch (\Exception $e) {
            $message = '设置镜像失败: ' . $e->getMessage();
            $messageType = 'error';
        }

        header('Location: /mirrors?message=' . urlencode($message) . '&type=' . $messageType);
        exit;
    }

    /**
     * 处理添加镜像操作
     */
    public function actionAddMirror()
    {
        $type = $_POST['type'] ?? '';
        $name = $_POST['name'] ?? '';
        $url = $_POST['url'] ?? '';

        if (empty($type) || empty($name) || empty($url)) {
            header('Location: /mirrors?message=' . urlencode('参数缺失') . '&type=error');
            exit;
        }

        // 验证URL格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            header('Location: /mirrors?message=' . urlencode('无效的URL格式') . '&type=error');
            exit;
        }

        try {
            $success = false;
            $message = '';

            switch ($type) {
                case 'php':
                    $success = $this->mirrorConfig->addPhpMirror($name, $url);
                    $message = $success ? "PHP镜像 {$name} 添加成功" : "添加PHP镜像失败";
                    break;

                case 'pecl':
                    $success = $this->mirrorConfig->addPeclMirror($name, $url);
                    $message = $success ? "PECL镜像 {$name} 添加成功" : "添加PECL镜像失败";
                    break;

                case 'composer':
                    $success = $this->mirrorConfig->addComposerMirror($name, $url);
                    $message = $success ? "Composer镜像 {$name} 添加成功" : "添加Composer镜像失败";
                    break;

                default:
                    $message = '不支持的镜像类型';
                    break;
            }

            $messageType = $success ? 'success' : 'error';
        } catch (\Exception $e) {
            $message = '添加镜像失败: ' . $e->getMessage();
            $messageType = 'error';
        }

        header('Location: /mirrors?message=' . urlencode($message) . '&type=' . $messageType);
        exit;
    }
}
