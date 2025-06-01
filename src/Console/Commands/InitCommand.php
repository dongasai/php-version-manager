<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\System\EnvironmentChecker;

/**
 * 初始化命令类
 *
 * 用于初始化PVM运行环境
 */
class InitCommand implements CommandInterface
{
    /**
     * 环境检查器
     *
     * @var EnvironmentChecker
     */
    private $environmentChecker;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->environmentChecker = new EnvironmentChecker();
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);

        echo "正在初始化PHP版本管理器(PVM)环境...\n\n";

        // 检查基础环境
        $checkResult = $this->environmentChecker->check(false, $options['skip_composer']);

        if ($checkResult['is_ok']) {
            echo "✅ 基础PHP环境检查通过\n";
        } else {
            echo "❌ 基础PHP环境检查失败\n\n";
            echo $this->environmentChecker->getDetailedInfo($options['skip_composer']) . "\n";

            // 如果指定了--fix选项，尝试修复环境问题
            if (isset($options['fix']) && $options['fix']) {
                echo "正在尝试修复环境问题...\n";
                $fixResult = $this->fixEnvironment($checkResult);

                if ($fixResult) {
                    echo "✅ 环境问题已修复\n";
                } else {
                    echo "❌ 无法自动修复环境问题，请手动安装缺失的扩展\n";
                    return 1;
                }
            } else {
                echo "使用 'pvm init --fix' 命令尝试自动修复环境问题\n";
                return 1;
            }
        }

        // 创建必要的目录结构
        $this->createDirectories();

        // 设置基本配置
        $this->setupConfiguration($options);

        echo "\nPVM环境初始化完成！\n";
        echo "您现在可以使用 'pvm install <版本>' 命令安装PHP版本\n";

        return 0;
    }

    /**
     * 解析命令选项
     *
     * @param array $args 命令参数
     * @return array 选项数组
     */
    private function parseOptions(array $args)
    {
        $options = [
            'fix' => false,
            'mirror' => null,
            'prefix' => getenv('HOME') . '/.pvm',
            'force' => false,
            'skip_composer' => false
        ];

        foreach ($args as $i => $arg) {
            if ($arg === '--fix' || $arg === '-f') {
                $options['fix'] = true;
            } elseif ($arg === '--force') {
                $options['force'] = true;
            } elseif ($arg === '--skip-composer') {
                $options['skip_composer'] = true;
            } elseif (strpos($arg, '--mirror=') === 0) {
                $options['mirror'] = substr($arg, 9);
            } elseif (strpos($arg, '--prefix=') === 0) {
                $options['prefix'] = substr($arg, 9);
            } elseif ($arg === '--mirror' && isset($args[$i + 1]) && $args[$i + 1][0] !== '-') {
                $options['mirror'] = $args[$i + 1];
            } elseif ($arg === '--prefix' && isset($args[$i + 1]) && $args[$i + 1][0] !== '-') {
                $options['prefix'] = $args[$i + 1];
            }
        }

        return $options;
    }

    /**
     * 修复环境问题
     *
     * @param array $checkResult 环境检查结果
     * @return bool 是否修复成功
     */
    private function fixEnvironment(array $checkResult)
    {
        // 检测包管理器
        $packageManager = $this->detectPackageManager();

        if (!$packageManager) {
            echo "❌ 无法检测到包管理器\n";
            return false;
        }

        // 获取缺失的扩展和命令
        $missingExtensions = $checkResult['missing_required_extensions'];
        $missingCommands = $checkResult['missing_required_commands'];
        $composerInstalled = $checkResult['composer_installed'];

        // 如果没有缺失的组件，直接返回成功
        if (empty($missingExtensions) && empty($missingCommands) && $composerInstalled) {
            return true;
        }

        // 安装缺失的PHP扩展
        if (!empty($missingExtensions)) {
            echo "\n正在安装缺失的PHP扩展...\n";

            // 构建安装命令
            $extensionPackages = [];
            foreach ($missingExtensions as $extension) {
                $extensionPackages[] = "php-{$extension}";
            }

            // 根据包管理器构建命令
            $installExtensionsCommand = "";
            switch ($packageManager) {
                case 'apt':
                    $installExtensionsCommand = "sudo apt-get update && sudo apt-get install -y " . implode(' ', $extensionPackages);
                    break;
                case 'yum':
                    $installExtensionsCommand = "sudo yum install -y " . implode(' ', $extensionPackages);
                    break;
                case 'dnf':
                    $installExtensionsCommand = "sudo dnf install -y " . implode(' ', $extensionPackages);
                    break;
                case 'apk':
                    $installExtensionsCommand = "sudo apk add " . implode(' ', $extensionPackages);
                    break;
                default:
                    echo "❌ 不支持的包管理器: {$packageManager}\n";
                    return false;
            }

            // 执行安装命令
            $success = $this->executeCommand($installExtensionsCommand);

            // 如果使用sudo失败，尝试不使用sudo
            if (!$success) {
                echo "\n尝试不使用sudo执行命令...\n";
                $installExtensionsCommand = str_replace("sudo ", "", $installExtensionsCommand);
                $success = $this->executeCommand($installExtensionsCommand);
            }

            if (!$success) {
                echo "\n❌ 安装PHP扩展失败\n";
                echo "请尝试手动执行以下命令安装扩展：\n";
                echo "sudo apt-get update\n";
                echo "sudo apt-get install -y " . implode(' ', $extensionPackages) . "\n";
                return false;
            }

            echo "\n✅ PHP扩展安装完成\n";
        }

        // 安装缺失的系统命令
        if (!empty($missingCommands)) {
            echo "\n正在安装缺失的系统命令...\n";

            $success = false;

            // 根据包管理器执行安装命令
            switch ($packageManager) {
                case 'apt':
                    // 先执行 apt-get update
                    echo "正在更新软件包列表...\n";
                    $updateSuccess = $this->executeCommand("sudo apt-get update");

                    if (!$updateSuccess) {
                        echo "\n尝试不使用sudo更新软件包列表...\n";
                        $updateSuccess = $this->executeCommand("apt-get update");
                    }

                    if ($updateSuccess) {
                        // 然后安装软件包
                        $installCommand = "sudo apt-get install -y " . implode(' ', $missingCommands);
                        $success = $this->executeCommand($installCommand);

                        if (!$success) {
                            echo "\n尝试不使用sudo安装软件包...\n";
                            $installCommand = "apt-get install -y " . implode(' ', $missingCommands);
                            $success = $this->executeCommand($installCommand);
                        }
                    }
                    break;
                case 'yum':
                    $installCommand = "sudo yum install -y " . implode(' ', $missingCommands);
                    $success = $this->executeCommand($installCommand);

                    if (!$success) {
                        echo "\n尝试不使用sudo执行命令...\n";
                        $installCommand = "yum install -y " . implode(' ', $missingCommands);
                        $success = $this->executeCommand($installCommand);
                    }
                    break;
                case 'dnf':
                    $installCommand = "sudo dnf install -y " . implode(' ', $missingCommands);
                    $success = $this->executeCommand($installCommand);

                    if (!$success) {
                        echo "\n尝试不使用sudo执行命令...\n";
                        $installCommand = "dnf install -y " . implode(' ', $missingCommands);
                        $success = $this->executeCommand($installCommand);
                    }
                    break;
                case 'apk':
                    $installCommand = "sudo apk add " . implode(' ', $missingCommands);
                    $success = $this->executeCommand($installCommand);

                    if (!$success) {
                        echo "\n尝试不使用sudo执行命令...\n";
                        $installCommand = "apk add " . implode(' ', $missingCommands);
                        $success = $this->executeCommand($installCommand);
                    }
                    break;
                default:
                    echo "❌ 不支持的包管理器: {$packageManager}\n";
                    return false;
            }

            if (!$success) {
                echo "\n❌ 安装系统命令失败\n";
                echo "请尝试手动执行以下命令安装系统命令：\n";
                if ($packageManager === 'apt') {
                    echo "sudo apt-get update\n";
                    echo "sudo apt-get install -y " . implode(' ', $missingCommands) . "\n";
                } else {
                    echo "sudo {$packageManager} install -y " . implode(' ', $missingCommands) . "\n";
                }
                return false;
            }

            echo "\n✅ 系统命令安装完成\n";
        }

        // 安装Composer
        if (!$composerInstalled) {
            echo "\n正在安装Composer...\n";

            // 检查curl是否已安装
            $curlInstalled = $this->commandExists('curl');

            if (!$curlInstalled) {
                echo "❌ 安装Composer需要curl，但curl未安装\n";
                echo "请先安装curl后再尝试安装Composer\n";
                return false;
            }

            // 创建Composer目录
            $homeDir = getenv('HOME');
            $pvmDir = $homeDir . '/.pvm';
            $phpVersion = PHP_VERSION;
            $composerDir = $pvmDir . '/versions/' . $phpVersion . '/composer/2';

            if (!is_dir($composerDir)) {
                mkdir($composerDir, 0755, true);
            }

            // 安装Composer到PHP版本目录
            // 步骤1: 下载并安装Composer
            echo "正在下载Composer安装程序...\n";
            $downloadSuccess = $this->executeCommand("curl -sS https://getcomposer.org/installer | php");

            if (!$downloadSuccess) {
                echo "\n❌ 下载Composer安装程序失败\n";
                echo "请尝试手动执行以下命令安装Composer：\n";
                echo "curl -sS https://getcomposer.org/installer | php\n";
                echo "mkdir -p " . $composerDir . "\n";
                echo "mv composer.phar " . $composerDir . "/composer.phar\n";
                echo "chmod +x " . $composerDir . "/composer.phar\n";
                return false;
            }

            // 步骤2: 移动composer.phar到目标目录
            echo "正在移动Composer到目标目录...\n";
            $targetPath = escapeshellarg($composerDir . '/composer.phar');
            $moveSuccess = $this->executeCommand("mv composer.phar " . $targetPath);

            if (!$moveSuccess) {
                echo "\n❌ 移动Composer文件失败\n";
                echo "请尝试手动执行以下命令：\n";
                echo "mv composer.phar " . $composerDir . "/composer.phar\n";
                echo "chmod +x " . $composerDir . "/composer.phar\n";
                return false;
            }

            // 步骤3: 设置执行权限
            echo "正在设置Composer执行权限...\n";
            $chmodSuccess = $this->executeCommand("chmod +x " . $targetPath);

            if (!$chmodSuccess) {
                echo "\n❌ 设置Composer执行权限失败\n";
                echo "请尝试手动执行以下命令：\n";
                echo "chmod +x " . $composerDir . "/composer.phar\n";
                return false;
            }

            // 创建Composer包装脚本
            $wrapperPath = $composerDir . '/composer';
            $phpBin = PHP_BINARY;
            $composerPhar = $composerDir . '/composer.phar';

            $wrapperContent = "#!/bin/bash\n\n";
            $wrapperContent .= "{$phpBin} {$composerPhar} \"\$@\"\n";

            file_put_contents($wrapperPath, $wrapperContent);
            chmod($wrapperPath, 0755);

            // 创建符号链接到bin目录
            $binDir = $pvmDir . '/bin';
            if (!is_dir($binDir)) {
                mkdir($binDir, 0755, true);
            }

            $defaultComposerPath = $binDir . '/composer';
            if (file_exists($defaultComposerPath)) {
                unlink($defaultComposerPath);
            }

            symlink($wrapperPath, $defaultComposerPath);

            // 保存默认Composer配置
            $configDir = $pvmDir . '/config';
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }

            $configFile = $configDir . '/composer.php';
            $config = [
                'php_version' => $phpVersion,
                'composer_version' => '2',
            ];

            $content = "<?php\n\n// 默认Composer配置\n// 由 PVM 自动生成，可以手动修改\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configFile, $content);

            echo "\n✅ Composer安装完成\n";
        }

        // 重新检查环境
        $newCheckResult = $this->environmentChecker->check();
        return $newCheckResult['is_ok'];
    }

    /**
     * 检测包管理器
     *
     * @return string|null 包管理器名称，如果未检测到则返回null
     */
    private function detectPackageManager()
    {
        $packageManagers = [
            'apt' => 'apt-get',
            'yum' => 'yum',
            'dnf' => 'dnf',
            'apk' => 'apk'
        ];

        foreach ($packageManagers as $name => $command) {
            $output = [];
            exec("which {$command} 2>/dev/null", $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * 检查命令是否存在
     *
     * @param string $command 命令名称
     * @return bool 是否存在
     */
    private function commandExists($command)
    {
        $output = [];
        $returnCode = 0;
        exec("which {$command} 2>/dev/null", $output, $returnCode);
        return $returnCode === 0 && !empty($output);
    }

    /**
     * 执行命令
     *
     * @param string $command 要执行的命令
     * @return bool 是否执行成功
     */
    private function executeCommand($command)
    {
        echo "\n正在执行: {$command}\n\n";

        // 使用proc_open实现实时输出
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            // 关闭stdin
            fclose($pipes[0]);

            // 设置非阻塞模式
            stream_set_blocking($pipes[1], 0);
            stream_set_blocking($pipes[2], 0);

            $output = '';
            $error = '';

            // 循环读取输出，直到进程结束
            while (true) {
                $status = proc_get_status($process);

                // 读取stdout
                $stdout = fread($pipes[1], 4096);
                if ($stdout) {
                    echo $stdout;
                    $output .= $stdout;
                }

                // 读取stderr
                $stderr = fread($pipes[2], 4096);
                if ($stderr) {
                    echo $stderr;
                    $error .= $stderr;
                }

                // 如果进程已结束，则退出循环
                if (!$status['running']) {
                    // 读取剩余输出
                    $stdout = stream_get_contents($pipes[1]);
                    if ($stdout) {
                        echo $stdout;
                        $output .= $stdout;
                    }

                    $stderr = stream_get_contents($pipes[2]);
                    if ($stderr) {
                        echo $stderr;
                        $error .= $stderr;
                    }

                    break;
                }

                // 避免 CPU 占用过高
                usleep(100000); // 100ms
            }

            // 关闭管道
            fclose($pipes[1]);
            fclose($pipes[2]);

            // 关闭进程
            $returnCode = proc_close($process);

            return $returnCode === 0;
        } else {
            echo "❌ 无法启动安装进程\n";
            return false;
        }
    }

    /**
     * 创建必要的目录结构
     */
    private function createDirectories()
    {
        $homeDir = getenv('HOME');
        $pvmDir = $homeDir . '/.pvm';

        $directories = [
            $pvmDir,
            $pvmDir . '/versions',
            $pvmDir . '/tmp',
            $pvmDir . '/cache',
            $pvmDir . '/config',
            $pvmDir . '/log',
            $pvmDir . '/bin'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                echo "创建目录: {$dir}\n";
                mkdir($dir, 0755, true);
            }
        }

        // 创建版本文件
        $versionFile = $pvmDir . '/version';
        if (!file_exists($versionFile)) {
            file_put_contents($versionFile, '');
        }

        // 创建配置文件
        $configFile = $pvmDir . '/config/config.php';
        if (!file_exists($configFile)) {
            $configContent = "<?php\n\nreturn [\n    'mirror' => null,\n    'auto_switch' => true,\n    'verify_signature' => true,\n];\n";
            file_put_contents($configFile, $configContent);
        }
    }

    /**
     * 设置基本配置
     *
     * @param array $options 选项数组
     */
    private function setupConfiguration(array $options)
    {
        $homeDir = getenv('HOME');
        $pvmDir = $homeDir . '/.pvm';
        $configFile = $pvmDir . '/config/config.php';

        // 如果指定了镜像，更新配置
        if (isset($options['mirror']) && $options['mirror']) {
            $config = include $configFile;
            $config['mirror'] = $options['mirror'];

            $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configFile, $configContent);

            echo "已设置默认镜像: {$options['mirror']}\n";
        }

        // 设置shell集成
        $this->setupShellIntegration();
    }

    /**
     * 设置shell集成
     */
    private function setupShellIntegration()
    {
        $homeDir = getenv('HOME');
        $pvmDir = $homeDir . '/.pvm';
        $shellScript = $pvmDir . '/shell/pvm.sh';

        // 确保shell目录存在
        if (!is_dir($pvmDir . '/shell')) {
            mkdir($pvmDir . '/shell', 0755, true);
        }

        // 复制shell脚本
        if (file_exists(__DIR__ . '/../../../shell/pvm.sh')) {
            copy(__DIR__ . '/../../../shell/pvm.sh', $shellScript);
            chmod($shellScript, 0755);

            // 检查shell配置文件
            $shellFiles = [
                $homeDir . '/.bashrc',
                $homeDir . '/.zshrc',
                $homeDir . '/.profile'
            ];

            $shellIntegrationLine = "\n# PVM Shell集成\n[ -s \"$pvmDir/shell/pvm.sh\" ] && . \"$pvmDir/shell/pvm.sh\"\n";

            foreach ($shellFiles as $shellFile) {
                if (file_exists($shellFile)) {
                    $content = file_get_contents($shellFile);

                    // 检查是否已经添加了PVM集成
                    if (strpos($content, 'PVM Shell集成') === false) {
                        file_put_contents($shellFile, $content . $shellIntegrationLine);
                        echo "已添加PVM Shell集成到 {$shellFile}\n";
                    }
                }
            }

            echo "Shell集成设置完成，请重新加载Shell配置或重新启动终端以生效\n";
        }
    }

    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '初始化PVM运行环境';
    }

    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm init [选项]

初始化PVM运行环境，包括检查基础PHP环境、创建必要的目录结构和设置基本配置。

选项:
  --fix, -f            尝试自动修复环境问题
  --mirror=<镜像地址>   设置默认镜像地址
  --prefix=<安装路径>   设置PVM安装路径，默认为~/.pvm
  --force              强制重新初始化，即使环境已经初始化
  --skip-composer      跳过Composer检查

示例:
  pvm init
  pvm init --fix
  pvm init --mirror=https://mirrors.aliyun.com/php
  pvm init --prefix=/opt/pvm
  pvm init --skip-composer
USAGE;
    }
}
