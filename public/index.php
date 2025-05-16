<?php

/**
 * PVM 镜像 Web 服务
 *
 * 提供 Web 界面和文件下载服务
 */

// 定义根目录
define('ROOT_DIR', dirname(__DIR__));

// 包含自动加载器
require ROOT_DIR . '/srcMirror/Autoloader.php';

// 注册自动加载器
Autoloader::register();

// 创建控制器
$controller = new Mirror\Web\Controller();

// 获取请求路径
$requestPath = $_SERVER['REQUEST_URI'];

// 处理请求
$controller->handleRequest($requestPath);
