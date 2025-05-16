<?php

namespace VersionManager\Core\Tags;

/**
 * 操作系统标签类
 * 
 * 定义所有操作系统相关的标签常量
 */
class OsTags
{
    // 操作系统类型
    const LINUX = 'linux';
    const WINDOWS = 'windows';
    const MACOS = 'macos';
    
    // Linux发行版
    const UBUNTU = 'ubuntu';
    const DEBIAN = 'debian';
    const CENTOS = 'centos';
    const FEDORA = 'fedora';
    const ALPINE = 'alpine';
    const RHEL = 'rhel';
    
    // 发行版家族
    const DEBIAN_BASED = 'debian-based';
    const RHEL_BASED = 'rhel-based';
    
    // Ubuntu版本
    const UBUNTU_1804 = 'ubuntu-18.04';
    const UBUNTU_2004 = 'ubuntu-20.04';
    const UBUNTU_2204 = 'ubuntu-22.04';
    
    // Debian版本
    const DEBIAN_10 = 'debian-10';
    const DEBIAN_11 = 'debian-11';
    const DEBIAN_12 = 'debian-12';
    
    // CentOS版本
    const CENTOS_7 = 'centos-7';
    const CENTOS_8 = 'centos-8';
    const CENTOS_9 = 'centos-9';
    
    // Alpine版本
    const ALPINE_3 = 'alpine-3';
    const ALPINE_3_15 = 'alpine-3.15';
    const ALPINE_3_16 = 'alpine-3.16';
    const ALPINE_3_17 = 'alpine-3.17';
    const ALPINE_3_18 = 'alpine-3.18';
    
    /**
     * 获取所有标签
     * 
     * @return array
     */
    public static function getAllTags()
    {
        $reflection = new \ReflectionClass(__CLASS__);
        return array_values($reflection->getConstants());
    }
    
    /**
     * 根据操作系统信息获取标签
     * 
     * @param string $osName 操作系统名称
     * @param string $osVersion 操作系统版本
     * @return array 标签数组
     */
    public static function getTagsFromOsInfo($osName, $osVersion)
    {
        $tags = [];
        
        // 添加操作系统类型标签
        $osName = strtolower($osName);
        if (strpos($osName, 'linux') !== false) {
            $tags[] = self::LINUX;
        } elseif (strpos($osName, 'win') !== false) {
            $tags[] = self::WINDOWS;
        } elseif (strpos($osName, 'darwin') !== false || strpos($osName, 'mac') !== false) {
            $tags[] = self::MACOS;
        }
        
        // 添加发行版标签
        switch ($osName) {
            case 'ubuntu':
                $tags[] = self::UBUNTU;
                $tags[] = self::DEBIAN_BASED;
                
                // 添加版本标签
                if ($osVersion) {
                    $tags[] = "ubuntu-{$osVersion}";
                    
                    // 添加特定版本常量
                    if ($osVersion === '18.04') {
                        $tags[] = self::UBUNTU_1804;
                    } elseif ($osVersion === '20.04') {
                        $tags[] = self::UBUNTU_2004;
                    } elseif ($osVersion === '22.04') {
                        $tags[] = self::UBUNTU_2204;
                    }
                }
                break;
                
            case 'debian':
                $tags[] = self::DEBIAN;
                $tags[] = self::DEBIAN_BASED;
                
                // 添加版本标签
                if ($osVersion) {
                    $tags[] = "debian-{$osVersion}";
                    
                    // 添加特定版本常量
                    if ($osVersion === '10') {
                        $tags[] = self::DEBIAN_10;
                    } elseif ($osVersion === '11') {
                        $tags[] = self::DEBIAN_11;
                    } elseif ($osVersion === '12') {
                        $tags[] = self::DEBIAN_12;
                    }
                }
                break;
                
            case 'centos':
                $tags[] = self::CENTOS;
                $tags[] = self::RHEL_BASED;
                
                // 添加版本标签
                if ($osVersion) {
                    $tags[] = "centos-{$osVersion}";
                    
                    // 添加特定版本常量
                    if ($osVersion === '7') {
                        $tags[] = self::CENTOS_7;
                    } elseif ($osVersion === '8') {
                        $tags[] = self::CENTOS_8;
                    } elseif ($osVersion === '9') {
                        $tags[] = self::CENTOS_9;
                    }
                }
                break;
                
            case 'fedora':
                $tags[] = self::FEDORA;
                $tags[] = self::RHEL_BASED;
                
                // 添加版本标签
                if ($osVersion) {
                    $tags[] = "fedora-{$osVersion}";
                }
                break;
                
            case 'alpine':
                $tags[] = self::ALPINE;
                
                // 添加版本标签
                if ($osVersion) {
                    $tags[] = "alpine-{$osVersion}";
                    
                    // 添加主版本标签
                    if (strpos($osVersion, '3.') === 0) {
                        $tags[] = self::ALPINE_3;
                        
                        // 添加特定版本常量
                        if ($osVersion === '3.15') {
                            $tags[] = self::ALPINE_3_15;
                        } elseif ($osVersion === '3.16') {
                            $tags[] = self::ALPINE_3_16;
                        } elseif ($osVersion === '3.17') {
                            $tags[] = self::ALPINE_3_17;
                        } elseif ($osVersion === '3.18') {
                            $tags[] = self::ALPINE_3_18;
                        }
                    }
                }
                break;
        }
        
        return $tags;
    }
}
