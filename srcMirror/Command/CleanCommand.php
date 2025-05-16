<?php

namespace Mirror\Command;

use Mirror\Mirror\PhpMirror;
use Mirror\Mirror\PeclMirror;
use Mirror\Mirror\ExtensionMirror;
use Mirror\Mirror\ComposerMirror;

/**
 * 清理命令类
 */
class CleanCommand extends AbstractCommand
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('clean', '清理过期镜像');
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 退出代码
     */
    public function execute(array $args = [])
    {
        echo "清理过期镜像...\n";
        
        // 加载配置
        $config = $this->loadConfig();
        
        // 清理 PHP 源码包
        $phpMirror = new PhpMirror();
        $phpMirror->clean($config['php']);
        
        // 清理 PECL 扩展包
        $peclMirror = new PeclMirror();
        $peclMirror->clean($config['pecl']);
        
        // 清理特定扩展的 GitHub 源码
        $extensionMirror = new ExtensionMirror();
        $extensionMirror->clean($config['extensions']);
        
        // 清理 Composer 包
        $composerMirror = new ComposerMirror();
        $composerMirror->clean($config['composer']);
        
        echo "清理完成\n";
        
        return 0;
    }
}
