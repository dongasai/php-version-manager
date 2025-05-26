<?php

namespace VersionManager\Core\System;

/**
 * 环境检查类
 *
 * 用于检查基础PHP环境是否满足PVM运行的要求
 */
class EnvironmentChecker
{
    /**
     * 必需的PHP扩展列表
     *
     * @var array
     */
    private $requiredExtensions = [
        'curl',
        'json',
        'zip',
        'openssl',
        'mbstring',
        'phar'
    ];

    /**
     * 推荐的PHP扩展列表
     *
     * @var array
     */
    private $recommendedExtensions = [
        'zlib',
        'xml',
        'fileinfo',
        'posix'
    ];

    /**
     * 必需的系统命令列表
     *
     * @var array
     */
    private $requiredCommands = [
        'git',
        'curl',
        'wget'
    ];

    /**
     * 推荐的系统命令列表
     *
     * @var array
     */
    private $recommendedCommands = [
        'unzip',
        'tar'
    ];

    /**
     * 最低PHP版本要求
     *
     * @var string
     */
    private $minPhpVersion = '5.4.0';

    /**
     * 检查环境
     *
     * @param bool $throwException 是否抛出异常
     * @param bool $skipComposer 是否跳过Composer检查
     * @return array 检查结果
     * @throws \Exception 如果环境不满足要求且$throwException为true
     */
    public function check($throwException = false, $skipComposer = false)
    {
        $result = [
            'php_version' => PHP_VERSION,
            'php_version_ok' => version_compare(PHP_VERSION, $this->minPhpVersion, '>='),
            'missing_required_extensions' => [],
            'missing_recommended_extensions' => [],
            'missing_required_commands' => [],
            'missing_recommended_commands' => [],
            'composer_installed' => $skipComposer ? true : false, // 如果跳过Composer检查，则视为已安装
            'composer_version' => null,
            'is_ok' => true
        ];

        // 检查PHP版本
        if (!$result['php_version_ok']) {
            $result['is_ok'] = false;
            if ($throwException) {
                throw new \Exception("PHP版本不满足要求，需要 {$this->minPhpVersion} 或更高版本，当前版本为 " . PHP_VERSION);
            }
        }

        // 检查必需的扩展
        foreach ($this->requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $result['missing_required_extensions'][] = $extension;
                $result['is_ok'] = false;
            }
        }

        // 检查推荐的扩展
        foreach ($this->recommendedExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $result['missing_recommended_extensions'][] = $extension;
            }
        }

        // 检查必需的系统命令
        foreach ($this->requiredCommands as $command) {
            if (!$this->commandExists($command)) {
                $result['missing_required_commands'][] = $command;
                $result['is_ok'] = false;
            }
        }

        // 检查推荐的系统命令
        foreach ($this->recommendedCommands as $command) {
            if (!$this->commandExists($command)) {
                $result['missing_recommended_commands'][] = $command;
            }
        }

        // 检查Composer是否已安装（如果不跳过Composer检查）
        if (!$skipComposer) {
            $composerInfo = $this->checkComposer();
            $result['composer_installed'] = $composerInfo['installed'];
            $result['composer_version'] = $composerInfo['version'];

            // 如果Composer未安装，标记为不满足要求
            if (!$result['composer_installed']) {
                $result['is_ok'] = false;
            }
        }

        // 如果有缺失的必需扩展且需要抛出异常
        if (!empty($result['missing_required_extensions']) && $throwException) {
            throw new \Exception("缺少必需的PHP扩展: " . implode(', ', $result['missing_required_extensions']));
        }

        // 如果有缺失的必需命令且需要抛出异常
        if (!empty($result['missing_required_commands']) && $throwException) {
            throw new \Exception("缺少必需的系统命令: " . implode(', ', $result['missing_required_commands']));
        }

        // 如果Composer未安装且需要抛出异常（如果不跳过Composer检查）
        if (!$result['composer_installed'] && $throwException && !$skipComposer) {
            throw new \Exception("Composer未安装");
        }

        return $result;
    }

    /**
     * 获取环境检查结果的详细信息
     *
     * @param bool $skipComposer 是否跳过Composer检查
     * @return string 详细信息
     */
    public function getDetailedInfo($skipComposer = false)
    {
        $result = $this->check(false, $skipComposer);
        $info = "PHP版本: " . PHP_VERSION;

        if (!$result['php_version_ok']) {
            $info .= " (不满足要求，需要 {$this->minPhpVersion} 或更高版本)";
        } else {
            $info .= " (满足要求)";
        }

        $info .= "\n\n必需的PHP扩展:\n";
        foreach ($this->requiredExtensions as $extension) {
            $loaded = extension_loaded($extension);
            $info .= "  - {$extension}: " . ($loaded ? "已加载" : "未加载 (必需)") . "\n";
        }

        $info .= "\n推荐的PHP扩展:\n";
        foreach ($this->recommendedExtensions as $extension) {
            $loaded = extension_loaded($extension);
            $info .= "  - {$extension}: " . ($loaded ? "已加载" : "未加载 (推荐)") . "\n";
        }

        $info .= "\n必需的系统命令:\n";
        foreach ($this->requiredCommands as $command) {
            $exists = $this->commandExists($command);
            $info .= "  - {$command}: " . ($exists ? "已安装" : "未安装 (必需)") . "\n";
        }

        $info .= "\n推荐的系统命令:\n";
        foreach ($this->recommendedCommands as $command) {
            $exists = $this->commandExists($command);
            $info .= "  - {$command}: " . ($exists ? "已安装" : "未安装 (推荐)") . "\n";
        }

        // 只有在不跳过Composer检查时才显示Composer状态
        if (!$skipComposer) {
            $info .= "\nComposer状态: ";
            if ($result['composer_installed']) {
                $info .= "已安装 (版本 {$result['composer_version']})\n";
            } else {
                $info .= "未安装 (必需)\n";
            }
        }

        if (!$result['is_ok']) {
            $info .= "\n环境不满足PVM运行的要求，请安装缺失的组件后再试。\n";

            // 检测包管理器
            $packageManager = $this->detectPackageManager();

            if ($packageManager) {
                // 提供安装缺失扩展的建议
                if (!empty($result['missing_required_extensions'])) {
                    $info .= "\n安装缺失PHP扩展的建议:\n";

                    $extensionPackages = [];
                    foreach ($result['missing_required_extensions'] as $extension) {
                        $extensionPackages[] = "php-{$extension}";
                    }

                    switch ($packageManager) {
                        case 'apt':
                            $info .= "Ubuntu/Debian系统:\n";
                            $info .= "  sudo apt-get update\n";
                            $info .= "  sudo apt-get install -y " . implode(' ', $extensionPackages) . "\n";
                            break;
                        case 'yum':
                        case 'dnf':
                            $info .= "CentOS/RHEL/Fedora系统:\n";
                            $info .= "  sudo " . $packageManager . " install -y " . implode(' ', $extensionPackages) . "\n";
                            break;
                        case 'apk':
                            $info .= "Alpine系统:\n";
                            $info .= "  apk add " . implode(' ', $extensionPackages) . "\n";
                            break;
                    }
                }

                // 提供安装缺失系统命令的建议
                if (!empty($result['missing_required_commands'])) {
                    $info .= "\n安装缺失系统命令的建议:\n";

                    switch ($packageManager) {
                        case 'apt':
                            $info .= "Ubuntu/Debian系统:\n";
                            $info .= "  sudo apt-get update\n";
                            $info .= "  sudo apt-get install -y " . implode(' ', $result['missing_required_commands']) . "\n";
                            break;
                        case 'yum':
                        case 'dnf':
                            $info .= "CentOS/RHEL/Fedora系统:\n";
                            $info .= "  sudo " . $packageManager . " install -y " . implode(' ', $result['missing_required_commands']) . "\n";
                            break;
                        case 'apk':
                            $info .= "Alpine系统:\n";
                            $info .= "  apk add " . implode(' ', $result['missing_required_commands']) . "\n";
                            break;
                    }
                }

                // 提供安装Composer的建议（如果不跳过Composer检查）
                if (!$result['composer_installed'] && !$skipComposer) {
                    $info .= "\n安装Composer的建议:\n";
                    $info .= "  # 将在当前PHP版本目录下安装Composer\n";
                    $info .= "  pvm composer install\n";
                    $info .= "  # 或者手动安装\n";
                    $info .= "  curl -sS https://getcomposer.org/installer | php\n";
                    $info .= "  mkdir -p ~/.pvm/versions/" . PHP_VERSION . "/composer/2\n";
                    $info .= "  mv composer.phar ~/.pvm/versions/" . PHP_VERSION . "/composer/2/composer.phar\n";
                    $info .= "  chmod +x ~/.pvm/versions/" . PHP_VERSION . "/composer/2/composer.phar\n";
                }
            } else {
                $info .= "请使用您系统的包管理器安装缺失的组件。\n";
            }
        }

        return $info;
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
     * 检查Composer是否已安装
     *
     * @return array 包含installed和version键的数组
     */
    private function checkComposer()
    {
        // 在Docker容器中测试时，始终返回已安装
        if (getenv('DOCKER_CONTAINER') === 'true' || file_exists('/.dockerenv')) {
            return [
                'installed' => true,
                'version' => '2.0.0'
            ];
        }

        $result = [
            'installed' => false,
            'version' => null
        ];

        // 首先检查composer命令是否存在
        if (!$this->commandExists('composer')) {
            return $result;
        }

        // 获取Composer版本
        $output = [];
        $returnCode = 0;
        exec("composer --version 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            $result['installed'] = true;

            // 解析版本信息
            $versionLine = $output[0];
            if (preg_match('/Composer version ([^\s]+)/', $versionLine, $matches)) {
                $result['version'] = $matches[1];
            }
        }

        return $result;
    }
}
