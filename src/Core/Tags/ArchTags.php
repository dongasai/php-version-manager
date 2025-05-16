<?php

namespace VersionManager\Core\Tags;

/**
 * 架构标签类
 * 
 * 定义所有架构相关的标签常量
 */
class ArchTags
{
    // 常见架构
    const X86 = 'x86';
    const X86_64 = 'x86_64';
    const AMD64 = 'amd64';
    const ARM = 'arm';
    const ARM64 = 'arm64';
    const AARCH64 = 'aarch64';
    const PPC64 = 'ppc64';
    const PPC64LE = 'ppc64le';
    const S390X = 's390x';
    const MIPS = 'mips';
    const MIPS64 = 'mips64';
    
    // 架构家族
    const X86_FAMILY = 'x86-family';
    const ARM_FAMILY = 'arm-family';
    const PPC_FAMILY = 'ppc-family';
    const MIPS_FAMILY = 'mips-family';
    
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
     * 根据架构信息获取标签
     * 
     * @param string $arch 架构名称
     * @return array 标签数组
     */
    public static function getTagsFromArch($arch)
    {
        $tags = [];
        $arch = strtolower($arch);
        
        // 添加架构标签
        switch ($arch) {
            case 'x86':
            case 'i386':
            case 'i486':
            case 'i586':
            case 'i686':
                $tags[] = self::X86;
                $tags[] = self::X86_FAMILY;
                break;
                
            case 'x86_64':
            case 'amd64':
            case 'x64':
                $tags[] = self::X86_64;
                $tags[] = self::AMD64;
                $tags[] = self::X86_FAMILY;
                break;
                
            case 'arm':
            case 'armv7':
            case 'armv7l':
                $tags[] = self::ARM;
                $tags[] = self::ARM_FAMILY;
                break;
                
            case 'arm64':
            case 'aarch64':
                $tags[] = self::ARM64;
                $tags[] = self::AARCH64;
                $tags[] = self::ARM_FAMILY;
                break;
                
            case 'ppc64':
                $tags[] = self::PPC64;
                $tags[] = self::PPC_FAMILY;
                break;
                
            case 'ppc64le':
                $tags[] = self::PPC64LE;
                $tags[] = self::PPC_FAMILY;
                break;
                
            case 's390x':
                $tags[] = self::S390X;
                break;
                
            case 'mips':
                $tags[] = self::MIPS;
                $tags[] = self::MIPS_FAMILY;
                break;
                
            case 'mips64':
                $tags[] = self::MIPS64;
                $tags[] = self::MIPS_FAMILY;
                break;
                
            default:
                // 如果没有匹配的架构，则添加原始架构名称
                $tags[] = $arch;
                break;
        }
        
        return $tags;
    }
}
