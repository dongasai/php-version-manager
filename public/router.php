<?php

/**
 * PHP 内置服务器路由文件
 * 
 * 这个文件用于处理 PHP 内置服务器的路由，确保所有请求都正确路由到我们的应用
 */

// 获取请求的文件路径
$requestedFile = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestedFile);
$path = $parsedUrl['path'];

// 移除查询参数，只保留路径
$cleanPath = $path;

// 检查是否是静态资源请求（CSS, JS, 图片等）
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot'];
$pathInfo = pathinfo($cleanPath);
$extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';

// 如果是静态资源，检查文件是否存在于 public 目录
if (in_array($extension, $staticExtensions)) {
    $staticFile = __DIR__ . $cleanPath;
    if (file_exists($staticFile)) {
        // 让 PHP 内置服务器处理静态文件
        return false;
    }
}

// 对于所有其他请求，都路由到 index.php
require_once __DIR__ . '/index.php';
