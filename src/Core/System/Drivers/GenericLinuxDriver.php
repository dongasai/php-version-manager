<?php

namespace VersionManager\Core\System\Drivers;

use VersionManager\Core\System\AbstractOsDriver;

/**
 * 通用Linux操作系统驱动类
 */
class GenericLinuxDriver extends AbstractOsDriver
{
    /**
     * {@inheritdoc}
     */
    protected function detectOsInfo()
    {
        // 默认设置通用Linux信息
        $this->name = 'linux';
        $this->description = 'Generic Linux';
        $this->version = php_uname('r');

        // 尝试从/etc/os-release获取信息
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID'])) {
                $this->name = strtolower($osRelease['ID']);
            }

            if (isset($osRelease['PRETTY_NAME'])) {
                $this->description = $osRelease['PRETTY_NAME'];
            }

            if (isset($osRelease['VERSION_ID'])) {
                $this->version = $osRelease['VERSION_ID'];
            }
        }

        // 如果仍然无法确定，则尝试其他方法
        if (empty($this->name) || $this->name === 'linux') {
            // 检查常见的发行版特定文件
            if (file_exists('/etc/debian_version')) {
                $this->name = 'debian';
                $this->version = trim(file_get_contents('/etc/debian_version'));
                $this->description = "Debian Linux {$this->version}";
            } elseif (file_exists('/etc/centos-release')) {
                $this->name = 'centos';
                $content = file_get_contents('/etc/centos-release');

                if (preg_match('/release\s+(\d+(\.\d+)*)/', $content, $matches)) {
                    $this->version = $matches[1];
                }

                $this->description = trim($content);
            } elseif (file_exists('/etc/fedora-release')) {
                $this->name = 'fedora';
                $content = file_get_contents('/etc/fedora-release');

                if (preg_match('/release\s+(\d+)/', $content, $matches)) {
                    $this->version = $matches[1];
                }

                $this->description = trim($content);
            } elseif (file_exists('/etc/alpine-release')) {
                $this->name = 'alpine';
                $this->version = trim(file_get_contents('/etc/alpine-release'));
                $this->description = "Alpine Linux {$this->version}";
            }
        }

        // 如果仍然无法确定，则尝试使用uname命令
        if (empty($this->name) || $this->name === 'linux') {
            $output = [];
            $returnCode = 0;

            exec('uname -s', $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $this->name = strtolower(trim($output[0]));
            }

            $output = [];
            exec('uname -r', $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $this->version = trim($output[0]);
            }

            $output = [];
            exec('uname -v', $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $this->description = trim($output[0]);
            } else {
                $this->description = "Linux {$this->version}";
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageManager()
    {
        // 检测包管理器
        if ($this->commandExists('apt-get')) {
            return 'apt';
        } elseif ($this->commandExists('yum')) {
            return 'yum';
        } elseif ($this->commandExists('dnf')) {
            return 'dnf';
        } elseif ($this->commandExists('apk')) {
            return 'apk';
        } elseif ($this->commandExists('pacman')) {
            return 'pacman';
        } else {
            return 'unknown';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackageCache(array $options = [])
    {
        \VersionManager\Core\Logger\Logger::info("更新软件包列表...", "\033[33m");

        $packageManager = $this->getPackageManager();

        switch ($packageManager) {
            case 'apt':
                $command = 'apt-get update';
                break;
            case 'yum':
                $command = 'yum makecache';
                break;
            case 'dnf':
                $command = 'dnf makecache';
                break;
            case 'apk':
                $command = 'apk update';
                break;
            case 'pacman':
                $command = 'pacman -Sy';
                break;
            default:
                throw new \Exception("不支持的包管理器: {$packageManager}");
        }

        list($output, $returnCode) = $this->executeWithPrivileges($command, $options);

        // 在详细模式下显示命令输出
        if (\VersionManager\Core\Logger\Logger::isVerbose()) {
            \VersionManager\Core\Logger\Logger::verbose("执行命令: $command");
            foreach ($output as $line) {
                \VersionManager\Core\Logger\Logger::verbose("  $line");
            }
        }

        // 对于大多数包管理器，只要退出码为0就认为成功
        if ($returnCode === 0) {
            \VersionManager\Core\Logger\Logger::success("软件包列表更新成功");
            return true;
        }

        $outputStr = implode("\n", $output);

        // 检查是否是权限问题
        if (strpos($outputStr, '权限不够') !== false ||
            strpos($outputStr, 'Permission denied') !== false) {
            throw new \Exception("权限不足，无法更新软件包列表");
        }

        // 检查是否是认证失败
        if (strpos($outputStr, '认证失败') !== false ||
            strpos($outputStr, 'Authentication failure') !== false) {
            throw new \Exception("认证失败，无法更新软件包列表");
        }

        // 对于apt，检查网络问题
        if ($packageManager === 'apt' && (
            strpos($outputStr, '连接超时') !== false ||
            strpos($outputStr, 'Connection timed out') !== false ||
            strpos($outputStr, '无法连接') !== false ||
            strpos($outputStr, 'Could not connect') !== false)) {
            \VersionManager\Core\Logger\Logger::warning("部分软件源连接失败，但主要软件源可用");
            return true;
        }

        throw new \Exception("更新软件包列表失败: " . $outputStr);
    }

    /**
     * {@inheritdoc}
     */
    public function isPackageInstalled($package)
    {
        $packageManager = $this->getPackageManager();

        switch ($packageManager) {
            case 'apt':
                $command = "dpkg -l | grep -w '{$package}' | grep -v '^rc'";
                break;
            case 'yum':
            case 'dnf':
                $command = "rpm -qa | grep -w '{$package}'";
                break;
            case 'apk':
                $command = "apk info | grep -w '{$package}'";
                break;
            case 'pacman':
                $command = "pacman -Q '{$package}'";
                break;
            default:
                return false;
        }

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        return $returnCode === 0 && !empty($output);
    }

    /**
     * {@inheritdoc}
     */
    public function installPackages(array $packages, array $options = [])
    {
        if (empty($packages)) {
            return true;
        }

        $packageManager = $this->getPackageManager();

        // 过滤掉已安装的包
        $packagesToInstall = [];
        foreach ($packages as $package) {
            if (!$this->isPackageInstalled($package)) {
                $packagesToInstall[] = $package;
            }
        }

        if (empty($packagesToInstall)) {
            \VersionManager\Core\Logger\Logger::info("所有依赖包已安装", "\033[32m");
            return true;
        }

        \VersionManager\Core\Logger\Logger::info("安装依赖包: " . implode(' ', $packagesToInstall), "\033[33m");

        $packageList = implode(' ', $packagesToInstall);

        switch ($packageManager) {
            case 'apt':
                $command = "apt-get install -y {$packageList}";
                break;
            case 'yum':
                $command = "yum install -y {$packageList}";
                break;
            case 'dnf':
                $command = "dnf install -y {$packageList}";
                break;
            case 'apk':
                $command = "apk add {$packageList}";
                break;
            case 'pacman':
                $command = "pacman -S --noconfirm {$packageList}";
                break;
            default:
                throw new \Exception("不支持的包管理器: {$packageManager}");
        }

        list($output, $returnCode) = $this->executeWithPrivileges($command, $options);

        // 在详细模式下显示命令输出
        if (\VersionManager\Core\Logger\Logger::isVerbose()) {
            \VersionManager\Core\Logger\Logger::verbose("执行命令: $command");
            foreach ($output as $line) {
                \VersionManager\Core\Logger\Logger::verbose("  $line");
            }
        }

        if ($returnCode === 0) {
            \VersionManager\Core\Logger\Logger::success("依赖包安装成功");
            return true;
        }

        $outputStr = implode("\n", $output);

        // 检查是否是权限问题
        if (strpos($outputStr, '权限不够') !== false ||
            strpos($outputStr, 'Permission denied') !== false) {
            throw new \Exception("权限不足，无法安装依赖包");
        }

        // 检查是否是认证失败
        if (strpos($outputStr, '认证失败') !== false ||
            strpos($outputStr, 'Authentication failure') !== false) {
            throw new \Exception("认证失败，无法安装依赖包");
        }

        throw new \Exception("安装依赖包失败: " . $outputStr);
    }
}
