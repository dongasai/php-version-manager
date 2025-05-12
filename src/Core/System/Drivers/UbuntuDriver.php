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
}
