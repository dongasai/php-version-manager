<?php

namespace Mirror\Web;

use Mirror\Config\MirrorConfig;
use Mirror\Mirror\MirrorStatus;
use Mirror\Utils\MirrorUtils;

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
     * 构造函数
     */
    public function __construct()
    {
        $this->config = new MirrorConfig();
        $this->status = new MirrorStatus();
    }

    /**
     * 处理请求
     *
     * @param string $requestPath 请求路径
     */
    public function handleRequest($requestPath)
    {
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
            'title' => 'PVM 镜像站 - 首页',
            'page_title' => 'PHP 版本管理器镜像站',
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

        // 发送文件
        $contentType = MirrorUtils::getMimeType($filePath);

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
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

        // 渲染目录列表模板
        $view = new View();
        $view->setLayout('layout')
             ->setActivePage($activePage);

        $view->render('directory', [
            'title' => '目录列表: /' . $path,
            'page_title' => '目录列表: /' . $path,
            'use_container' => true,
            'show_breadcrumb' => true,
            'path' => $path,
            'breadcrumbs' => $breadcrumbs,
            'files' => $files,
            'filePath' => $filePath,
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

        // 根据 API 路径返回不同的数据
        switch ($apiPath) {
            case 'status':
                echo json_encode($this->status->getStatus());
                break;
            case 'php':
                echo json_encode($this->status->getPhpList());
                break;
            case 'pecl':
                echo json_encode($this->status->getPeclList());
                break;
            case 'extensions':
                echo json_encode($this->status->getExtensionsList());
                break;
            case 'composer':
                echo json_encode($this->status->getComposerList());
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

        // 渲染状态页面
        $view = new View();
        $view->setLayout('layout')
             ->setActivePage('status');

        $view->render('status', [
            'title' => 'PVM 镜像站 - 状态监控',
            'page_title' => '镜像状态监控',
            'use_container' => true,
            'status' => $status,
            'system' => $system,
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
            'title' => 'PVM 镜像站 - 文档',
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
                $uptime = explode(' ', $uptime)[0];
                $days = floor($uptime / 86400);
                $hours = floor(($uptime % 86400) / 3600);
                $minutes = floor(($uptime % 3600) / 60);

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
}
