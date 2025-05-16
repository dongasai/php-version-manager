<?php

namespace Mirror\Command;

use Mirror\Mirror\PhpMirror;
use Mirror\Mirror\PeclMirror;
use Mirror\Mirror\ExtensionMirror;
use Mirror\Mirror\ComposerMirror;

/**
 * 同步命令类
 */
class SyncCommand extends AbstractCommand
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('sync', '同步镜像内容');
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 退出代码
     */
    public function execute(array $args = [])
    {
        echo "开始同步镜像内容...\n";
        
        // 加载配置
        $config = $this->loadConfig();
        
        // 同步 PHP 源码包
        $phpMirror = new PhpMirror();
        $phpMirror->sync($config['php']);
        
        // 同步 PECL 扩展包
        $peclMirror = new PeclMirror();
        $peclMirror->sync($config['pecl']);
        
        // 同步特定扩展的 GitHub 源码
        $extensionMirror = new ExtensionMirror();
        $extensionMirror->sync($config['extensions']);
        
        // 同步 Composer 包
        $composerMirror = new ComposerMirror();
        $composerMirror->sync($config['composer']);
        
        echo "镜像同步完成\n";
        
        return 0;
    }
}
