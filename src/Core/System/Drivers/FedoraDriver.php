<?php

namespace VersionManager\Core\System\Drivers;

use VersionManager\Core\System\AbstractOsDriver;

/**
 * Fedora操作系统驱动类
 */
class FedoraDriver extends AbstractOsDriver
{
    /**
     * {@inheritdoc}
     */
    protected function detectOsInfo()
    {
        // 默认设置Fedora信息
        $this->name = 'fedora';
        $this->description = 'Fedora Linux';
        $this->version = '';
        
        // 从/etc/fedora-release获取Fedora版本信息
        if (file_exists('/etc/fedora-release')) {
            $content = file_get_contents('/etc/fedora-release');
            $this->description = trim($content);
            
            // 提取版本号
            if (preg_match('/release\s+(\d+)/', $content, $matches)) {
                $this->version = $matches[1];
            }
        }
        
        // 如果无法从/etc/fedora-release获取信息，则尝试从/etc/os-release获取
        if (empty($this->version) && file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');
            
            if (isset($osRelease['ID']) && strtolower($osRelease['ID']) === 'fedora') {
                if (isset($osRelease['VERSION_ID'])) {
                    $this->version = $osRelease['VERSION_ID'];
                }
                
                if (isset($osRelease['PRETTY_NAME'])) {
                    $this->description = $osRelease['PRETTY_NAME'];
                }
            }
        }
        
        // 如果仍然无法获取版本信息，则尝试使用dnf命令
        if (empty($this->version) && $this->commandExists('dnf')) {
            $output = [];
            $returnCode = 0;
            
            exec('dnf --version | head -1 | cut -d" " -f3', $output, $returnCode);
            
            if ($returnCode === 0 && !empty($output)) {
                // dnf版本通常与Fedora版本相关，但不完全一致
                // 这里只是一个备选方案
                $this->version = trim($output[0]);
            }
        }
    }
}
