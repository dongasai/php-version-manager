<?php

namespace Mirror\Web;

use Mirror\Cache\CacheManager;
use Mirror\Config\MirrorConfig;
use Mirror\Mirror\MirrorStatus;
use Mirror\Resource\ResourceManager;
use Mirror\Utils\MirrorUtils;
use Mirror\Security\AccessControl;

/**
 * Web控制器类
 */
class Controller
{
    /**
     * 配置管理器
     *
     * @var MirrorConfig
     */
    private $config;

    /**
     * 镜像状态管理器
     *
     * @var MirrorStatus
     */
    private $status;

    /**
     * 配置管理器
     *
     * @var \Mirror\Config\ConfigManager
     */
    private $configManager;

    /**
     * 访问控制
     *
     * @var AccessControl
     */
    private $accessControl;

    /**
     * 缓存管理器
     *
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * 资源管理器
     *
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->configManager = new \Mirror\Config\ConfigManager();
        $this->config = new MirrorConfig();
        $this->status = new MirrorStatus();
        $this->accessControl = new AccessControl();
        $this->cacheManager = new CacheManager();
        $this->resourceManager = new ResourceManager();
    }

    /**
     * 处理请求
     *
     * @param string $requestPath 请求路径
     */
    public function handleRequest($requestPath)
    {
        // 获取请求方法
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // 获取客户端IP
        $clientIp = $this->getClientIp();

        // 检查访问权限
        if (!$this->accessControl->checkAccess($method, $requestPath)) {
            $this->accessControl->handleAccessDenied($method, $requestPath);
            return;
        }

        // 检查IP请求频率
        if (!$this->resourceManager->checkIpRequestRate($clientIp)) {
            $this->handleRateLimitExceeded($clientIp, $method, $requestPath);
            return;
        }

        // 设置内容类型
        header('Content-Type: text/html; charset=utf-8');

        // 如果是根路径，显示首页
        if ($requestPath === '/' || $requestPath === '/index.php') {
            $this->showHomePage();
            return;
        }

        // 如果是状态页面
        if ($requestPath === '/status/' || $requestPath === '/status') {
            $this->showStatusPage();
            return;
        }

        // 如果是文档页面
        if ($requestPath === '/docs/' || $requestPath === '/docs') {
            $this->showDocsPage();
            return;
        }

        // 处理文件下载请求
        $this->handleFileRequest($requestPath);
    }

    /**
     * 显示首页
     */
    public function showHomePage()
    {
        // 获取镜像状态
        $status = $this->status->getStatus();

        // 获取配置
        $config = $this->config->getConfig();

        // 渲染首页模板
        $view = new View();
        $view->setLayout('layout')
             ->setActivePage('home');

        $view->render('home', [
            'title' => 'PVM 下载站 - 首页',
            'page_title' => 'PHP 版本管理器下载站',
            'use_container' => true,
            'status' => $status,
            'config' => $config,
            'formatSize' => function($size) {
                return MirrorUtils::formatSize($size);
            }
        ]);
    }

    /**
     * 处理文件下载请求
     *
     * @param string $requestPath 请求路径
     */
    public function handleFileRequest($requestPath)
    {
        // 解析请求路径
        $path = parse_url($requestPath, PHP_URL_PATH);

        // 移除前导斜杠
        $path = ltrim($path, '/');

        // 检查路径是否为 API 请求
        if (strpos($path, 'api/') === 0) {
            $this->handleApiRequest($path);
            return;
        }

        // 构建文件路径
        $filePath = ROOT_DIR . '/data/' . $path;

        // 如果是目录，显示目录列表
        if (is_dir($filePath)) {
            $this->showDirectoryListing($path, $filePath);
            return;
        }

        // 如果文件不存在，返回 404
        if (!file_exists($filePath)) {
            $this->show404();
            return;
        }

        // 检查是否可以开始新的下载
        if (!$this->resourceManager->canStartDownload()) {
            $this->handleDownloadLimitExceeded();
            return;
        }

        // 开始下载
        $this->resourceManager->startDownload();

        // 获取下载速度限制
        $speedLimit = $this->resourceManager->getDownloadSpeedLimit();

        // 发送文件
        $contentType = MirrorUtils::getMimeType($filePath);
        $fileSize = filesize($filePath);

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . $fileSize);

        // 如果没有速度限制，直接发送文件
        if ($speedLimit <= 0) {
            readfile($filePath);
        } else {
            // 使用分块传输和速度限制
            $this->sendFileWithSpeedLimit($filePath, $speedLimit);
        }

        // 结束下载
        $this->resourceManager->endDownload();
    }

    /**
     * 显示目录列表
     *
     * @param string $path 请求路径
     * @param string $filePath 文件系统路径
     */
    public function showDirectoryListing($path, $filePath)
    {
        // 获取目录内容
        $files = scandir($filePath);

        // 过滤掉 . 和 ..
        $files = array_filter($files, function($file) {
            return $file !== '.' && $file !== '..';
        });

        // 获取URL参数
        $queryParams = [];
        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
        }

        // 处理版本筛选
        $filteredFiles = $files;
        $filterApplied = false;
        $filterDescription = '';

        if (isset($queryParams['version']) && !empty($queryParams['version'])) {
            $versionFilter = $queryParams['version'];
            $filterApplied = true;

            // 根据不同目录类型应用不同的筛选逻辑
            if (strpos($path, 'php') === 0) {
                // 筛选PHP版本
                $filteredFiles = array_filter($files, function($file) use ($versionFilter) {
                    return preg_match('/php-' . preg_quote($versionFilter, '/') . '(\.|-)/', $file);
                });
                $filterDescription = "PHP {$versionFilter}.x 版本";
            } elseif (strpos($path, 'pecl') === 0) {
                // 筛选PECL扩展版本
                $filteredFiles = array_filter($files, function($file) use ($versionFilter) {
                    return strpos($file, "-{$versionFilter}.") !== false;
                });
                $filterDescription = "PHP {$versionFilter} 兼容的PECL扩展";
            } elseif (strpos($path, 'extensions') === 0) {
                // 筛选扩展版本
                $filteredFiles = array_filter($files, function($file) use ($versionFilter) {
                    return strpos($file, "-{$versionFilter}.") !== false;
                });
                $filterDescription = "PHP {$versionFilter} 兼容的扩展";
            }
        }

        // 处理扩展名筛选
        if (isset($queryParams['ext']) && !empty($queryParams['ext'])) {
            $extFilter = $queryParams['ext'];
            $filterApplied = true;

            $filteredFiles = array_filter($filteredFiles, function($file) use ($extFilter) {
                return pathinfo($file, PATHINFO_EXTENSION) === $extFilter;
            });

            $filterDescription .= ($filterDescription ? '，' : '') . "扩展名: {$extFilter}";
        }

        // 处理名称搜索
        if (isset($queryParams['search']) && !empty($queryParams['search'])) {
            $searchFilter = $queryParams['search'];
            $filterApplied = true;

            $filteredFiles = array_filter($filteredFiles, function($file) use ($searchFilter) {
                return stripos($file, $searchFilter) !== false;
            });

            $filterDescription .= ($filterDescription ? '，' : '') . "搜索: {$searchFilter}";
        }

        // 构建面包屑导航
        $breadcrumbs = [];
        $parts = explode('/', $path);
        $currentPath = '';

        foreach ($parts as $part) {
            if (empty($part)) continue;

            $currentPath .= $part . '/';
            $breadcrumbs[] = [
                'name' => $part,
                'path' => '/' . $currentPath,
            ];
        }

        // 确定活动页面
        $activePage = 'home';
        if (strpos($path, 'php') === 0) {
            $activePage = 'php';
        } elseif (strpos($path, 'pecl') === 0) {
            $activePage = 'pecl';
        } elseif (strpos($path, 'extensions') === 0) {
            $activePage = 'extensions';
        } elseif (strpos($path, 'composer') === 0) {
            $activePage = 'composer';
        }

        // 构建页面标题
        $pageTitle = '目录列表: /' . $path;
        if ($filterApplied) {
            $pageTitle .= ' (' . $filterDescription . ')';
        }

        // 渲染目录列表模板
        $view = new View();
        $view->setLayout('layout')
             ->setActivePage($activePage);

        $view->render('directory', [
            'title' => $pageTitle,
            'page_title' => $pageTitle,
            'use_container' => true,
            'show_breadcrumb' => true,
            'path' => $path,
            'breadcrumbs' => $breadcrumbs,
            'files' => $filterApplied ? $filteredFiles : $files,
            'filePath' => $filePath,
            'filterApplied' => $filterApplied,
            'filterDescription' => $filterDescription,
            'queryParams' => $queryParams,
            'formatSize' => function($size) {
                return MirrorUtils::formatSize($size);
            }
        ]);
    }

    /**
     * 处理API请求
     *
     * @param string $path API路径
     */
    public function handleApiRequest($path)
    {
        // 设置内容类型为 JSON
        header('Content-Type: application/json');

        // 解析 API 路径
        $apiPath = substr($path, 4); // 移除 'api/'
        $apiPath = rtrim($apiPath, '.json');

        // 获取缓存配置
        $cacheConfig = $this->configManager->getCacheConfig();
        $cacheTags = $cacheConfig['cache_tags'] ?? [];
        $defaultTtl = $cacheConfig['default_ttl'] ?? 3600;

        // 根据 API 路径返回不同的数据
        switch ($apiPath) {
            case 'status':
                // 检查是否启用状态缓存
                if ($this->cacheManager->isEnabled() && ($cacheTags['status'] ?? false)) {
                    $cacheKey = 'api_status';
                    $data = $this->cacheManager->get($cacheKey);
                    if ($data === null) {
                        $data = $this->status->getStatus();
                        $this->cacheManager->set($cacheKey, $data, $defaultTtl);
                    }
                } else {
                    $data = $this->status->getStatus();
                }
                echo json_encode($data);
                break;

            case 'php':
                // 检查是否启用PHP缓存
                if ($this->cacheManager->isEnabled() && ($cacheTags['php'] ?? false)) {
                    $cacheKey = 'api_php';
                    $data = $this->cacheManager->get($cacheKey);
                    if ($data === null) {
                        $data = $this->status->getPhpList();
                        $this->cacheManager->set($cacheKey, $data, $defaultTtl);
                    }
                } else {
                    $data = $this->status->getPhpList();
                }
                echo json_encode($data);
                break;

            case 'pecl':
                // 检查是否启用PECL缓存
                if ($this->cacheManager->isEnabled() && ($cacheTags['pecl'] ?? false)) {
                    $cacheKey = 'api_pecl';
                    $data = $this->cacheManager->get($cacheKey);
                    if ($data === null) {
                        $data = $this->status->getPeclList();
                        $this->cacheManager->set($cacheKey, $data, $defaultTtl);
                    }
                } else {
                    $data = $this->status->getPeclList();
                }
                echo json_encode($data);
                break;

            case 'extensions':
                // 检查是否启用扩展缓存
                if ($this->cacheManager->isEnabled() && ($cacheTags['extensions'] ?? false)) {
                    $cacheKey = 'api_extensions';
                    $data = $this->cacheManager->get($cacheKey);
                    if ($data === null) {
                        $data = $this->status->getExtensionsList();
                        $this->cacheManager->set($cacheKey, $data, $defaultTtl);
                    }
                } else {
                    $data = $this->status->getExtensionsList();
                }
                echo json_encode($data);
                break;

            case 'composer':
                // 检查是否启用Composer缓存
                if ($this->cacheManager->isEnabled() && ($cacheTags['composer'] ?? false)) {
                    $cacheKey = 'api_composer';
                    $data = $this->cacheManager->get($cacheKey);
                    if ($data === null) {
                        $data = $this->status->getComposerList();
                        $this->cacheManager->set($cacheKey, $data, $defaultTtl);
                    }
                } else {
                    $data = $this->status->getComposerList();
                }
                echo json_encode($data);
                break;

            default:
                header('HTTP/1.0 404 Not Found');
                echo json_encode(['error' => 'API not found']);
                break;
        }
    }

    /**
     * 显示状态页面
     */
    public function showStatusPage()
    {
        // 获取缓存配置
        $cacheConfig = $this->configManager->getCacheConfig();
        $cacheTags = $cacheConfig['cache_tags'] ?? [];
        $defaultTtl = $cacheConfig['default_ttl'] ?? 3600;

        // 检查是否启用状态缓存
        $cacheKey = 'status_page';
        $statusData = null;
        $systemData = null;

        if ($this->cacheManager->isEnabled() && ($cacheTags['status'] ?? false)) {
            $statusData = $this->cacheManager->get($cacheKey . '_status');
            $systemData = $this->cacheManager->get($cacheKey . '_system');
        }

        // 如果缓存不存在，则获取数据
        if ($statusData === null) {
            // 获取镜像状态
            $status = $this->status->getStatus();

            // 添加各类型的最后更新时间
            $status['php_last_update'] = $status['last_update'];
            $status['pecl_last_update'] = $status['last_update'];
            $status['extension_last_update'] = $status['last_update'];
            $status['composer_last_update'] = $status['last_update'];

            // 添加各类型的大小
            $status['php_size'] = $status['total_size'] * 0.6; // 假设PHP源码占60%
            $status['pecl_size'] = $status['total_size'] * 0.2; // 假设PECL扩展占20%
            $status['extension_size'] = $status['total_size'] * 0.15; // 假设特定扩展占15%
            $status['composer_size'] = $status['total_size'] * 0.05; // 假设Composer包占5%

            $statusData = $status;

            // 缓存状态数据
            if ($this->cacheManager->isEnabled() && ($cacheTags['status'] ?? false)) {
                $this->cacheManager->set($cacheKey . '_status', $statusData, $defaultTtl);
            }
        }

        // 系统状态数据不缓存太久，因为它会变化
        if ($systemData === null) {
            // 获取系统状态
            $system = [
                'hostname' => php_uname('n'),
                'os' => php_uname('s') . ' ' . php_uname('r'),
                'kernel' => php_uname('v'),
                'php_version' => PHP_VERSION,
                'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'uptime' => $this->getSystemUptime(),
                'load' => $this->getSystemLoad(),
                'cpu_usage' => rand(10, 90), // 模拟数据
                'memory_usage' => rand(30, 80), // 模拟数据
                'disk_usage' => rand(40, 95), // 模拟数据
            ];

            $systemData = $system;

            // 缓存系统数据（较短时间）
            if ($this->cacheManager->isEnabled() && ($cacheTags['status'] ?? false)) {
                $this->cacheManager->set($cacheKey . '_system', $systemData, 60); // 只缓存1分钟
            }
        }

        // 渲染状态页面
        $view = new View();
        $view->setLayout('layout')
             ->setActivePage('status');

        $view->render('status', [
            'title' => 'PVM 下载站 - 状态监控',
            'page_title' => '镜像状态监控',
            'use_container' => true,
            'status' => $statusData,
            'system' => $systemData,
            'formatSize' => function($size) {
                return MirrorUtils::formatSize($size);
            }
        ]);
    }

    /**
     * 显示文档页面
     */
    public function showDocsPage()
    {
        // 渲染文档页面
        $view = new View();
        $view->setLayout('layout')
             ->setActivePage('docs');

        $view->render('docs', [
            'title' => 'PVM 下载站 - 文档',
            'page_title' => '使用文档',
            'use_container' => true,
            'show_breadcrumb' => true,
            'breadcrumbs' => [
                ['name' => '文档', 'path' => '/docs/']
            ]
        ]);
    }

    /**
     * 获取系统运行时间
     *
     * @return string
     */
    private function getSystemUptime()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime !== false) {
                $uptime = (float)explode(' ', $uptime)[0];
                // 显式转换避免隐式转换警告
                $days = (int)floor((float)$uptime / 86400);
                $hours = (int)floor((float)($uptime % 86400) / 3600);
                $minutes = (int)floor((float)($uptime % 3600) / 60);

                return "{$days}天 {$hours}小时 {$minutes}分钟";
            }
        }

        return 'Unknown';
    }

    /**
     * 获取系统负载
     *
     * @return string
     */
    private function getSystemLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return sprintf('%.2f, %.2f, %.2f', $load[0], $load[1], $load[2]);
        }

        return 'Unknown';
    }

    /**
     * 显示404页面
     */
    public function show404()
    {
        header('HTTP/1.0 404 Not Found');

        $view = new View();
        $view->setLayout('layout')
             ->setActivePage('home');

        $view->render('404', [
            'title' => '404 Not Found',
            'use_container' => true
        ]);
    }

    /**
     * 获取客户端IP
     *
     * @return string
     */
    private function getClientIp()
    {
        // 尝试从各种可能的服务器变量中获取客户端IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // HTTP_X_FORWARDED_FOR可能包含多个IP，取第一个
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '0.0.0.0';
    }

    /**
     * 处理请求频率超限
     *
     * @param string $ip 客户端IP
     * @param string $method 请求方法
     * @param string $uri 请求URI
     */
    private function handleRateLimitExceeded($ip, $method, $uri)
    {
        // 记录访问被拒绝
        if (method_exists($this->accessControl, 'logDenied')) {
            $this->accessControl->logDenied($ip, $method, $uri, 'Rate limit exceeded');
        }

        header('HTTP/1.0 429 Too Many Requests');
        header('Content-Type: text/html; charset=utf-8');
        header('Retry-After: 60');

        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>429 Too Many Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #d9534f;
        }
    </style>
</head>
<body>
    <h1>429 Too Many Requests</h1>
    <p>您的请求频率过高，请稍后再试。</p>
    <p>您的IP地址: ' . $ip . '</p>
    <p>请等待至少1分钟后再次尝试访问。</p>
</body>
</html>';

        exit;
    }

    /**
     * 处理下载限制超限
     */
    private function handleDownloadLimitExceeded()
    {
        header('HTTP/1.0 503 Service Unavailable');
        header('Content-Type: text/html; charset=utf-8');
        header('Retry-After: 300');

        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 Service Unavailable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #d9534f;
        }
    </style>
</head>
<body>
    <h1>503 Service Unavailable</h1>
    <p>服务器当前下载任务已满，请稍后再试。</p>
    <p>请等待至少5分钟后再次尝试下载。</p>
</body>
</html>';

        exit;
    }

    /**
     * 使用速度限制发送文件
     *
     * @param string $filePath 文件路径
     * @param int $speedLimit 速度限制（字节/秒）
     */
    private function sendFileWithSpeedLimit($filePath, $speedLimit)
    {
        // 打开文件
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return;
        }

        // 设置缓冲区大小
        $chunkSize = 8192; // 8KB

        // 计算每个块的发送间隔（微秒）
        $sleepTime = (int)(($chunkSize / $speedLimit) * 1000000);

        // 禁用输出缓冲
        if (ob_get_level()) {
            ob_end_clean();
        }

        // 设置无限执行时间
        set_time_limit(0);

        // 分块发送文件
        while (!feof($handle)) {
            // 读取一个块
            $buffer = fread($handle, $chunkSize);
            if ($buffer === false) {
                break;
            }

            // 发送块
            echo $buffer;

            // 刷新输出缓冲
            flush();

            // 如果连接已断开，则停止发送
            if (connection_status() != CONNECTION_NORMAL) {
                break;
            }

            // 等待一段时间，以限制速度
            if ($sleepTime > 0) {
                usleep($sleepTime);
            }
        }

        // 关闭文件
        fclose($handle);
    }
}
