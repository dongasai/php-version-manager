<?php

namespace Mirror\Command;

use Mirror\Log\LogManager;

/**
 * 日志命令类
 */
class LogCommand extends AbstractCommand
{
    /**
     * 日志管理器
     *
     * @var LogManager
     */
    private $logManager;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('log', '管理日志');
        $this->logManager = new LogManager();
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

        // 执行操作
        switch ($action) {
            case 'show':
                if (count($args) < 2) {
                    echo "错误: 缺少日志类型\n";
                    return $this->showHelp();
                }
                $type = $args[1];
                $lines = isset($args[2]) ? (int)$args[2] : 10;
                return $this->showLog($type, $lines);

            case 'clear':
                if (count($args) < 2) {
                    echo "错误: 缺少日志类型\n";
                    return $this->showHelp();
                }
                $type = $args[1];
                return $this->clearLog($type);

            case 'path':
                if (count($args) < 2) {
                    echo "错误: 缺少日志类型\n";
                    return $this->showHelp();
                }
                $type = $args[1];
                return $this->showLogPath($type);

            case 'types':
                return $this->showLogTypes();

            case 'help':
                return $this->showHelp();

            default:
                echo "未知操作: $action\n";
                return $this->showHelp();
        }
    }

    /**
     * 显示日志内容
     *
     * @param string $type 日志类型
     * @param int $lines 行数
     * @return int 退出代码
     */
    private function showLog($type, $lines = 10)
    {
        // 验证日志类型
        if (!$this->isValidLogType($type)) {
            echo "错误: 无效的日志类型: $type\n";
            echo "有效的日志类型: system, access, error, sync, download\n";
            return 1;
        }

        // 获取日志内容
        $logs = $this->logManager->getLogContent($type, $lines);

        if (empty($logs)) {
            echo "日志为空\n";
            return 0;
        }

        echo "最近 " . count($logs) . " 条 $type 日志:\n";
        foreach ($logs as $log) {
            echo $log;
        }

        return 0;
    }

    /**
     * 清空日志
     *
     * @param string $type 日志类型
     * @return int 退出代码
     */
    private function clearLog($type)
    {
        // 验证日志类型
        if (!$this->isValidLogType($type)) {
            echo "错误: 无效的日志类型: $type\n";
            echo "有效的日志类型: system, access, error, sync, download\n";
            return 1;
        }

        // 清空日志
        if ($this->logManager->clearLog($type)) {
            echo "$type 日志已清空\n";
            return 0;
        } else {
            echo "清空 $type 日志失败\n";
            return 1;
        }
    }

    /**
     * 显示日志文件路径
     *
     * @param string $type 日志类型
     * @return int 退出代码
     */
    private function showLogPath($type)
    {
        // 验证日志类型
        if (!$this->isValidLogType($type)) {
            echo "错误: 无效的日志类型: $type\n";
            echo "有效的日志类型: system, access, error, sync, download\n";
            return 1;
        }

        // 获取日志文件路径
        $path = $this->logManager->getLogFile($type);
        echo "$type 日志文件路径: $path\n";

        return 0;
    }

    /**
     * 显示日志类型
     *
     * @return int 退出代码
     */
    private function showLogTypes()
    {
        echo "可用的日志类型:\n";
        echo "  system    - 系统日志\n";
        echo "  access    - 访问日志\n";
        echo "  error     - 错误日志\n";
        echo "  sync      - 同步日志\n";
        echo "  download  - 下载日志\n";

        return 0;
    }

    /**
     * 验证日志类型
     *
     * @param string $type 日志类型
     * @return bool 是否有效
     */
    private function isValidLogType($type)
    {
        $validTypes = ['system', 'access', 'error', 'sync', 'download'];
        return in_array($type, $validTypes);
    }

    /**
     * 显示帮助信息
     *
     * @return int 退出代码
     */
    private function showHelp()
    {
        echo "日志管理\n";
        echo "用法: pvm-mirror log <操作> [参数]\n\n";
        echo "可用操作:\n";
        echo "  show <类型> [行数]  显示指定类型的日志\n";
        echo "  clear <类型>        清空指定类型的日志\n";
        echo "  path <类型>         显示指定类型的日志文件路径\n";
        echo "  types               显示可用的日志类型\n";
        echo "  help                显示此帮助信息\n\n";
        echo "可用的日志类型:\n";
        echo "  system    - 系统日志\n";
        echo "  access    - 访问日志\n";
        echo "  error     - 错误日志\n";
        echo "  sync      - 同步日志\n";
        echo "  download  - 下载日志\n\n";
        echo "示例:\n";
        echo "  pvm-mirror log show system 20\n";
        echo "  pvm-mirror log clear access\n";
        echo "  pvm-mirror log path error\n";
        echo "  pvm-mirror log types\n";

        return 0;
    }
}
