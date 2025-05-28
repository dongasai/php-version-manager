<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Console\UI\ConsoleUI;

/**
 * Web管理界面命令
 */
class WebCommand implements CommandInterface
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $name = 'web';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '启动Web管理界面';

    /**
     * 控制台UI
     *
     * @var ConsoleUI
     */
    protected $ui;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->ui = new ConsoleUI();
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 退出代码
     */
    public function execute(array $args = [])
    {
        // 解析参数
        $options = $this->parseArgs($args);

        // 获取主机和端口
        $host = $options['host'] ?? '127.0.0.1';
        $port = $options['port'] ?? 8000;

        // 获取文档根目录
        $docRoot = dirname(dirname(__DIR__)) . '/Web';

        // 检查PHP内置服务器是否可用
        if (!function_exists('exec')) {
            $this->ui->error('无法启动Web服务器：exec函数不可用');
            return 1;
        }

        // 检查文档根目录是否存在
        if (!is_dir($docRoot)) {
            $this->ui->error("无法启动Web服务器：文档根目录不存在: {$docRoot}");
            return 1;
        }

        // 检查入口文件是否存在
        $indexFile = $docRoot . '/index.php';
        if (!file_exists($indexFile)) {
            $this->ui->error("无法启动Web服务器：入口文件不存在: {$indexFile}");
            return 1;
        }

        // 检查权限
        $this->checkPrivileges();

        // 检查端口是否被占用
        $command = "lsof -i:{$port} | grep LISTEN";
        exec($command, $output, $returnCode);

        if (!empty($output)) {
            $this->ui->error("无法启动Web服务器：端口 {$port} 已被占用");
            return 1;
        }

        // 启动PHP内置服务器
        $command = "php -S {$host}:{$port} -t {$docRoot} {$indexFile}";

        // 显示启动信息
        $this->ui->info("正在启动:".date('Y-m-d H:i:s'));
        $this->ui->info("正在启动Web管理界面...");
        $this->ui->info("访问地址: http://{$host}:{$port}");
        $this->ui->info("按 Ctrl+C 停止服务器");
        $this->ui->info("命令: {$command}");
        $this->ui->info("-----------------------------------");

        // 执行命令
        passthru($command);

        return 0;
    }

    /**
     * 解析命令参数
     *
     * @param array $args 命令参数
     * @return array 解析后的参数
     */
    private function parseArgs(array $args)
    {
        $options = [];

        foreach ($args as $arg) {
            if (strpos($arg, '--host=') === 0) {
                $options['host'] = substr($arg, 7);
            } elseif (strpos($arg, '--port=') === 0) {
                $options['port'] = (int) substr($arg, 7);
            } elseif ($arg === '--help' || $arg === '-h') {
                $this->showHelp();
                exit(0);
            }
        }

        return $options;
    }

    /**
     * 检查权限
     */
    private function checkPrivileges()
    {
        $hasRoot = $this->hasAdminPrivileges();
        $canSudo = $this->canUseSudo();

        if ($hasRoot) {
            $this->ui->success("✓ 检测到管理员权限，Web界面将具有完整的系统管理功能");
        } elseif ($canSudo) {
            $this->ui->warning("⚠ 检测到sudo权限，Web界面将具有部分系统管理功能");
        } else {
            $this->ui->warning("⚠ 未检测到管理员权限");
            $this->ui->info("  某些功能（如重启PHP-FPM）将不可用");
            $this->ui->info("  如需完整功能，请使用管理员权限运行：");
            $this->ui->info("  sudo pvm web");
            $this->ui->info("");
        }
    }

    /**
     * 检查是否有管理员权限
     *
     * @return bool
     */
    private function hasAdminPrivileges()
    {
        // 检查是否为root用户
        if (function_exists('posix_getuid')) {
            return posix_getuid() === 0;
        }

        // 备用检查方法
        $output = [];
        $returnCode = 0;
        exec('id -u 2>/dev/null', $output, $returnCode);

        return $returnCode === 0 && isset($output[0]) && trim($output[0]) === '0';
    }

    /**
     * 检查是否可以执行sudo命令
     *
     * @return bool
     */
    private function canUseSudo()
    {
        // 如果已经是root用户，就不需要sudo
        if ($this->hasAdminPrivileges()) {
            return true;
        }

        // 检查用户是否在sudo组中
        $output = [];
        $returnCode = 0;
        exec('groups 2>/dev/null', $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            $groups = implode(' ', $output);
            if (strpos($groups, 'sudo') !== false || strpos($groups, 'wheel') !== false) {
                return true;
            }
        }

        // 如果用户在sudo组中，假设可以使用sudo
        return false;
    }

    /**
     * 显示帮助信息
     */
    private function showHelp()
    {
        $this->ui->info("用法: pvm web [选项]");
        $this->ui->info("");
        $this->ui->info("选项:");
        $this->ui->info("  --host=HOST    指定主机地址 (默认: 127.0.0.1)");
        $this->ui->info("  --port=PORT    指定端口号 (默认: 8000)");
        $this->ui->info("  --help, -h     显示帮助信息");
        $this->ui->info("");
        $this->ui->info("示例:");
        $this->ui->info("  pvm web");
        $this->ui->info("  pvm web --host=0.0.0.0 --port=8080");
        $this->ui->info("  sudo pvm web  # 以管理员权限运行，获得完整功能");
    }

    /**
     * 获取命令描述
     *
     * @return string 命令描述
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * 获取命令用法
     *
     * @return string 命令用法
     */
    public function getUsage()
    {
        return 'pvm web [选项]';
    }

    /**
     * 获取命令帮助
     *
     * @return string 命令帮助
     */
    public function getHelp()
    {
        return <<<EOT
用法: pvm web [选项]

启动Web管理界面，提供图形化的PHP版本管理功能。

选项:
  --host=HOST    指定主机地址 (默认: 127.0.0.1)
  --port=PORT    指定端口号 (默认: 8000)
  --help, -h     显示帮助信息

示例:
  pvm web
  pvm web --host=0.0.0.0 --port=8080
EOT;
    }
}
