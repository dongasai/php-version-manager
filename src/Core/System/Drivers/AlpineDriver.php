<?php

namespace VersionManager\Core\System\Drivers;

use VersionManager\Core\System\AbstractOsDriver;

/**
 * Alpine操作系统驱动类
 */
class AlpineDriver extends AbstractOsDriver
{
    /**
     * {@inheritdoc}
     */
    protected function detectOsInfo()
    {
        // 默认设置Alpine信息
        $this->name = 'alpine';
        $this->description = 'Alpine Linux';
        $this->version = '';
        
        // 从/etc/alpine-release获取Alpine版本信息
        if (file_exists('/etc/alpine-release')) {
            $this->version = trim(file_get_contents('/etc/alpine-release'));
            $this->description = "Alpine Linux {$this->version}";
        }
        
        // 如果无法从/etc/alpine-release获取信息，则尝试从/etc/os-release获取
        if (empty($this->version) && file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');
            
            if (isset($osRelease['ID']) && strtolower($osRelease['ID']) === 'alpine') {
                if (isset($osRelease['VERSION_ID'])) {
                    $this->version = $osRelease['VERSION_ID'];
                }
                
                if (isset($osRelease['PRETTY_NAME'])) {
                    $this->description = $osRelease['PRETTY_NAME'];
                }
            }
        }
        
        // 如果仍然无法获取版本信息，则尝试使用apk命令
        if (empty($this->version) && $this->commandExists('apk')) {
            $output = [];
            $returnCode = 0;
            
            exec('apk --version | head -1 | cut -d" " -f2', $output, $returnCode);
            
            if ($returnCode === 0 && !empty($output)) {
                // apk版本可能与Alpine版本不完全一致
                // 这里只是一个备选方案
                $this->version = trim($output[0]);
            }
        }
    }
}
