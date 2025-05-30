<?php

namespace VersionManager\Core\System\Drivers;

use VersionManager\Core\System\AbstractOsDriver;

/**
 * Ubuntu操作系统驱动类
 */
class UbuntuDriver extends AbstractOsDriver
{
    /**
     * {@inheritdoc}
     */
    protected function detectOsInfo()
    {
        // 默认设置Ubuntu信息
        $this->name = 'ubuntu';
        $this->description = 'Ubuntu Linux';
        $this->version = '';

        // 从/etc/lsb-release获取Ubuntu版本信息
        if (file_exists('/etc/lsb-release')) {
            $lsbRelease = parse_ini_file('/etc/lsb-release');

            if (isset($lsbRelease['DISTRIB_ID']) && strtolower($lsbRelease['DISTRIB_ID']) === 'ubuntu') {
                if (isset($lsbRelease['DISTRIB_RELEASE'])) {
                    $this->version = $lsbRelease['DISTRIB_RELEASE'];
                }

                if (isset($lsbRelease['DISTRIB_DESCRIPTION'])) {
                    $this->description = $lsbRelease['DISTRIB_DESCRIPTION'];
                }
            }
        }

        // 如果从/etc/lsb-release无法获取信息，则尝试从/etc/os-release获取
        if (empty($this->version) && file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID']) && strtolower($osRelease['ID']) === 'ubuntu') {
                if (isset($osRelease['VERSION_ID'])) {
                    $this->version = $osRelease['VERSION_ID'];
                }

                if (isset($osRelease['PRETTY_NAME'])) {
                    $this->description = $osRelease['PRETTY_NAME'];
                }
            }
        }

        // 如果仍然无法获取版本信息，则尝试使用lsb_release命令
        if (empty($this->version) && $this->commandExists('lsb_release')) {
            $output = [];
            $returnCode = 0;

            exec('lsb_release -r | cut -f2', $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $this->version = trim($output[0]);
            }

            // 获取描述信息
            $output = [];
            exec('lsb_release -d | cut -f2', $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $this->description = trim($output[0]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageManager()
    {
        return 'apt';
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackageCache(array $options = [])
    {
        \VersionManager\Core\Logger\Logger::info("更新软件包列表...", "\033[33m");

        $command = 'apt-get update';
        list($output, $returnCode) = $this->executeWithPrivileges($command, $options);

        // 在详细模式下显示命令输出
        if (\VersionManager\Core\Logger\Logger::isVerbose()) {
            \VersionManager\Core\Logger\Logger::verbose("执行命令: $command");
            foreach ($output as $line) {
                \VersionManager\Core\Logger\Logger::verbose("  $line");
            }
        }

        // 对于apt-get update，只要退出码为0就认为成功，忽略警告信息
        if ($returnCode === 0) {
            \VersionManager\Core\Logger\Logger::success("软件包列表更新成功");
            return true;
        }

        // 检查是否是权限问题
        $outputStr = implode("\n", $output);
        if (strpos($outputStr, '权限不够') !== false ||
            strpos($outputStr, 'Permission denied') !== false ||
            strpos($outputStr, '无法对目录') !== false ||
            strpos($outputStr, '无法打开锁文件') !== false) {
            throw new \Exception("权限不足，无法更新软件包列表");
        }

        // 检查是否是认证失败
        if (strpos($outputStr, '认证失败') !== false ||
            strpos($outputStr, 'Authentication failure') !== false) {
            throw new \Exception("认证失败，无法更新软件包列表");
        }

        // 检查是否是网络问题（ESM源连接超时等）
        if (strpos($outputStr, '连接超时') !== false ||
            strpos($outputStr, 'Connection timed out') !== false ||
            strpos($outputStr, '无法连接') !== false ||
            strpos($outputStr, 'Could not connect') !== false) {
            // 网络问题不应该阻止安装过程，只是警告
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
        $command = "dpkg -l | grep -w '{$package}' | grep -v '^rc'";
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

        // 注意：依赖检查已在上层AbstractVersionDriver中完成
        // 这里传入的packages应该都是需要安装的包
        \VersionManager\Core\Logger\Logger::info("安装依赖包: " . implode(' ', $packages), "\033[33m");

        $packageList = implode(' ', $packages);
        $command = "apt-get install -y {$packageList}";

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
