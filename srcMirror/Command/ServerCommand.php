<?php

namespace Mirror\Command;

use Mirror\Server\ServerManager;

/**
 * 服务器命令类
 */
class ServerCommand extends AbstractCommand
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('server', '管理镜像服务器');
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 退出代码
     */
    public function execute(array $args = [])
    {
        // 如果没有参数，显示帮助信息
        if (empty($args)) {
            return $this->showHelp();
        }

        // 获取操作
        $action = $args[0];

        // 获取端口
        $port = isset($args[1]) ? (int)$args[1] : 8080;

        // 创建服务器管理器
        $serverManager = new ServerManager();

        // 执行操作
        switch ($action) {
            case 'start':
                $serverManager->start($port);
                break;
                
            case 'stop':
                $serverManager->stop();
                break;
                
            case 'status':
                $serverManager->status();
                break;
                
            case 'restart':
                $serverManager->restart($port);
                break;
                
            case 'help':
                return $this->showHelp();
                
            default:
                echo "未知操作: $action\n";
                return $this->showHelp();
        }

        return 0;
    }

    /**
     * 显示帮助信息
     *
     * @return int 退出代码
     */
    private function showHelp()
    {
        echo "PVM 镜像服务器管理\n";
        echo "用法: pvm-mirror server <操作> [端口]\n\n";
        echo "可用操作:\n";
        echo "  start   启动服务器 (默认端口: 8080)\n";
        echo "  stop    停止服务器\n";
        echo "  status  显示服务器状态\n";
        echo "  restart 重启服务器\n";
        echo "  help    显示此帮助信息\n\n";
        echo "示例:\n";
        echo "  pvm-mirror server start       # 在默认端口 8080 上启动服务器\n";
        echo "  pvm-mirror server start 9000  # 在端口 9000 上启动服务器\n";
        echo "  pvm-mirror server stop        # 停止服务器\n";

        return 0;
    }
}
