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
        $view->render('home', [
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
        
        // 渲染目录列表模板
        $view = new View();
        $view->render('directory', [
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
     * 显示404页面
     */
    public function show404()
    {
        header('HTTP/1.0 404 Not Found');
        
        $view = new View();
        $view->render('404');
    }
}
