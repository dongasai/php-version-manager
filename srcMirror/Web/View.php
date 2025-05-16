<?php

namespace Mirror\Web;

/**
 * 视图类
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
     * 构造函数
     */
    public function __construct()
    {
        $this->templateDir = ROOT_DIR . '/srcMirror/Web/templates';
    }

    /**
     * 渲染模板
     *
     * @param string $template 模板名称
     * @param array $data 模板数据
     */
    public function render($template, array $data = [])
    {
        // 检查模板文件是否存在
        $templateFile = $this->templateDir . '/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \Exception("模板文件不存在: $templateFile");
        }
        
        // 提取变量
        extract($data);
        
        // 开始输出缓冲
        ob_start();
        
        // 包含模板文件
        include $templateFile;
        
        // 获取缓冲内容并结束缓冲
        $content = ob_get_clean();
        
        // 输出内容
        echo $content;
    }
}
