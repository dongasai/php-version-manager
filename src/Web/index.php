<?php

/**
 * PVM Web管理界面入口文件
 */

// 设置错误显示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 定义根目录
define('ROOT_DIR', dirname(dirname(__DIR__)));

// 包含自动加载器
require ROOT_DIR . '/vendor/autoload.php';

// 创建控制器
$controller = new \VersionManager\Web\Controller();

// 获取请求URI
$uri = $_SERVER['REQUEST_URI'];

try {
    // 处理请求
    $response = $controller->handleRequest($uri);
    
    // 输出响应
    echo $response;
} catch (Exception $e) {
    // 显示错误信息
    http_response_code(500);
    
    echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>错误 - PVM 管理面板</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">发生错误</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">错误信息</h5>
                        <p class="card-text">' . htmlspecialchars($e->getMessage()) . '</p>
                        
                        <h5 class="card-title mt-4">错误详情</h5>
                        <pre class="bg-light p-3">' . htmlspecialchars($e->getTraceAsString()) . '</pre>
                        
                        <a href="/" class="btn btn-primary mt-3">返回首页</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
}
