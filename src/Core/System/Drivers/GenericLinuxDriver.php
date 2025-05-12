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
}
