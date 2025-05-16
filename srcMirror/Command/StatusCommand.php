<?php

namespace Mirror\Command;

use Mirror\Mirror\MirrorStatus;

/**
 * 状态命令类
 */
class StatusCommand extends AbstractCommand
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('status', '显示镜像状态');
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 退出代码
     */
    public function execute(array $args = [])
    {
        echo "镜像状态:\n";
        
        // 获取镜像状态
        $status = new MirrorStatus();
        $stats = $status->getStatus();
        
        // 显示状态信息
        echo "PHP 源码包: " . $stats['php_files'] . " 个文件\n";
        echo "PECL 扩展包: " . $stats['pecl_files'] . " 个文件\n";
        echo "特定扩展源码: " . count($stats['extension_dirs']) . " 个扩展, " . $stats['extension_files'] . " 个文件\n";
        echo "Composer 包: " . $stats['composer_files'] . " 个文件\n";
        echo "总大小: " . $status->formatSize($stats['total_size']) . "\n";
        
        if ($stats['last_update'] > 0) {
            echo "最后更新: " . date('Y-m-d H:i:s', $stats['last_update']) . "\n";
        }
        
        return 0;
    }
}
