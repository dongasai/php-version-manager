<?php

namespace VersionManager\Web;

/**
 * 视图类
 * 
 * 负责渲染HTML模板
 */
class View
{
    /**
     * 模板目录
     *
     * @var string
     */
    private $templateDir;
    
    /**
     * 布局模板
     *
     * @var string
     */
    private $layout = 'layout';
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 设置模板目录
        $this->templateDir = dirname(__DIR__) . '/Web/templates';
        
        // 确保模板目录存在
        if (!is_dir($this->templateDir)) {
            mkdir($this->templateDir, 0755, true);
        }
    }
    
    /**
     * 设置布局模板
     *
     * @param string $layout 布局模板名称
     * @return self
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * 渲染模板
     *
     * @param string $template 模板名称
     * @param array $data 模板数据
     * @return string 渲染后的HTML
     */
    public function render($template, array $data = [])
    {
        // 检查模板文件是否存在
        $templateFile = $this->templateDir . '/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \RuntimeException("模板文件不存在: {$templateFile}");
        }
        
        // 提取变量
        extract($data);
        
        // 开始输出缓冲
        ob_start();
        
        // 包含模板文件
        include $templateFile;
        
        // 获取渲染后的内容
        $content = ob_get_clean();
        
        // 如果设置了布局，则使用布局模板
        if ($this->layout !== null) {
            $layoutFile = $this->templateDir . '/' . $this->layout . '.php';
            
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("布局模板文件不存在: {$layoutFile}");
            }
            
            // 将内容传递给布局模板
            $data['content'] = $content;
            
            // 提取变量
            extract($data);
            
            // 开始输出缓冲
            ob_start();
            
            // 包含布局模板文件
            include $layoutFile;
            
            // 获取渲染后的内容
            $content = ob_get_clean();
        }
        
        return $content;
    }
    
    /**
     * 转义HTML
     *
     * @param string $string 要转义的字符串
     * @return string 转义后的字符串
     */
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * 格式化文件大小
     *
     * @param int $bytes 字节数
     * @return string 格式化后的文件大小
     */
    public function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max(0, (int) $bytes);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 格式化时间
     *
     * @param int $timestamp 时间戳
     * @param string $format 格式
     * @return string 格式化后的时间
     */
    public function formatTime($timestamp, $format = 'Y-m-d H:i:s')
    {
        return date($format, $timestamp);
    }
    
    /**
     * 生成URL
     *
     * @param string $path 路径
     * @param array $params 参数
     * @return string URL
     */
    public function url($path, array $params = [])
    {
        $url = '/' . ltrim($path, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * 生成资源URL
     *
     * @param string $path 资源路径
     * @return string 资源URL
     */
    public function asset($path)
    {
        return '/assets/' . ltrim($path, '/');
    }
    
    /**
     * 生成分页HTML
     *
     * @param int $page 当前页码
     * @param int $totalPages 总页数
     * @param string $url 基础URL
     * @param array $params 其他参数
     * @return string 分页HTML
     */
    public function pagination($page, $totalPages, $url, array $params = [])
    {
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="分页导航"><ul class="pagination">';
        
        // 上一页
        if ($page > 1) {
            $prevParams = array_merge($params, ['page' => $page - 1]);
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->url($url, $prevParams) . '">&laquo; 上一页</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo; 上一页</span></li>';
        }
        
        // 页码
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        
        if ($start > 1) {
            $firstParams = array_merge($params, ['page' => 1]);
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->url($url, $firstParams) . '">1</a></li>';
            
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $page) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $pageParams = array_merge($params, ['page' => $i]);
                $html .= '<li class="page-item"><a class="page-link" href="' . $this->url($url, $pageParams) . '">' . $i . '</a></li>';
            }
        }
        
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            
            $lastParams = array_merge($params, ['page' => $totalPages]);
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->url($url, $lastParams) . '">' . $totalPages . '</a></li>';
        }
        
        // 下一页
        if ($page < $totalPages) {
            $nextParams = array_merge($params, ['page' => $page + 1]);
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->url($url, $nextParams) . '">下一页 &raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">下一页 &raquo;</span></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
}
