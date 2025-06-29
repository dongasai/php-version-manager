<?php

namespace VersionManager\Core\Version\Util;

/**
 * 配置助手类
 *
 * 提供PHP编译配置相关的通用功能
 */
class ConfigureHelper
{
    /**
     * 获取基础配置选项
     *
     * @param string $version PHP版本
     * @param string $prefix 安装前缀路径
     * @return array 基础配置选项
     */
    public static function getBaseConfigureOptions($version, $prefix)
    {
        return [
            "--prefix={$prefix}",
            "--with-config-file-path={$prefix}/etc",
            "--with-config-file-scan-dir={$prefix}/etc/conf.d",
            '--enable-bcmath',
            '--enable-calendar',
            '--enable-dba',
            '--enable-exif',
            '--enable-ftp',
            '--enable-mbstring',
            '--with-mysqli',
            '--with-pdo-mysql',
            '--with-pdo-sqlite',
            '--with-openssl',
            '--enable-sockets',
            '--enable-soap',
            '--enable-zip',
            '--with-zlib',
            '--with-curl',
            '--enable-opcache',
        ];
    }

    /**
     * 获取GD扩展配置选项（根据PHP版本）
     *
     * @param string $version PHP版本
     * @return array GD扩展配置选项
     */
    public static function getGdConfigureOptions($version)
    {
        if (VersionHelper::isPhp71To73($version)) {
            // PHP 7.1 - PHP 7.3
            return [
                '--with-gd',
                '--with-jpeg-dir',
                '--with-png-dir',
                '--with-webp-dir',
                '--with-freetype-dir',
                '--with-xpm-dir',
            ];
        } elseif (VersionHelper::compareVersions($version, '7.4.0', '>=') && 
                  VersionHelper::compareVersions($version, '8.0.0', '<')) {
            // PHP 7.4
            return [
                '--enable-gd',
                '--with-jpeg',
                '--with-webp',
                '--with-freetype',
                '--with-xpm',
            ];
        } elseif (VersionHelper::isPhp8OrHigher($version)) {
            // PHP 8.0+
            $options = [
                '--enable-gd',
                '--with-jpeg',
                '--with-webp',
                '--with-freetype',
                '--with-xpm',
                '--with-avif',
            ];

            // PHP 8.1+
            if (VersionHelper::compareVersions($version, '8.1.0', '>=')) {
                $options[] = '--with-ffi';
            }

            // PHP 8.2+
            if (VersionHelper::compareVersions($version, '8.2.0', '>=')) {
                $options[] = '--enable-jit';
            }

            return $options;
        }

        return [];
    }

    /**
     * 获取完整的配置选项
     *
     * @param string $version PHP版本
     * @param string $prefix 安装前缀路径
     * @param array $customOptions 自定义配置选项
     * @return array 完整的配置选项
     */
    public static function getFullConfigureOptions($version, $prefix, array $customOptions = [])
    {
        $options = self::getBaseConfigureOptions($version, $prefix);
        $gdOptions = self::getGdConfigureOptions($version);
        
        // 合并GD选项
        $options = array_merge($options, $gdOptions);
        
        // 合并自定义选项
        if (!empty($customOptions)) {
            $options = array_merge($options, $customOptions);
        }
        
        return $options;
    }

    /**
     * 获取推荐的配置选项（针对生产环境）
     *
     * @param string $version PHP版本
     * @param string $prefix 安装前缀路径
     * @return array 推荐的配置选项
     */
    public static function getRecommendedConfigureOptions($version, $prefix)
    {
        $options = self::getFullConfigureOptions($version, $prefix);
        
        // 添加生产环境推荐选项
        $productionOptions = [
            '--enable-fpm',
            '--with-fpm-user=www-data',
            '--with-fpm-group=www-data',
            '--disable-debug',
            '--enable-inline-optimization',
            '--with-pic',
            '--enable-shared',
        ];
        
        return array_merge($options, $productionOptions);
    }

    /**
     * 获取开发环境配置选项
     *
     * @param string $version PHP版本
     * @param string $prefix 安装前缀路径
     * @return array 开发环境配置选项
     */
    public static function getDevelopmentConfigureOptions($version, $prefix)
    {
        $options = self::getFullConfigureOptions($version, $prefix);
        
        // 添加开发环境选项
        $devOptions = [
            '--enable-debug',
            '--enable-maintainer-zts',
            '--with-valgrind',
        ];
        
        return array_merge($options, $devOptions);
    }

    /**
     * 获取最小化配置选项
     *
     * @param string $version PHP版本
     * @param string $prefix 安装前缀路径
     * @return array 最小化配置选项
     */
    public static function getMinimalConfigureOptions($version, $prefix)
    {
        return [
            "--prefix={$prefix}",
            "--with-config-file-path={$prefix}/etc",
            "--with-config-file-scan-dir={$prefix}/etc/conf.d",
            '--disable-all',
            '--enable-cli',
            '--enable-cgi',
            '--with-openssl',
            '--with-zlib',
        ];
    }

    /**
     * 验证配置选项
     *
     * @param array $options 配置选项
     * @return array 验证结果 [valid => bool, errors => array]
     */
    public static function validateConfigureOptions(array $options)
    {
        $errors = [];
        $valid = true;
        
        // 检查必需的选项
        $requiredOptions = ['--prefix'];
        foreach ($requiredOptions as $required) {
            $found = false;
            foreach ($options as $option) {
                if (strpos($option, $required) === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $errors[] = "缺少必需的配置选项: {$required}";
                $valid = false;
            }
        }
        
        // 检查冲突的选项
        $conflicts = [
            ['--enable-debug', '--disable-debug'],
            ['--enable-shared', '--disable-shared'],
        ];
        
        foreach ($conflicts as $conflictPair) {
            $hasFirst = in_array($conflictPair[0], $options);
            $hasSecond = in_array($conflictPair[1], $options);
            
            if ($hasFirst && $hasSecond) {
                $errors[] = "配置选项冲突: {$conflictPair[0]} 和 {$conflictPair[1]}";
                $valid = false;
            }
        }
        
        return [
            'valid' => $valid,
            'errors' => $errors
        ];
    }

    /**
     * 格式化配置选项为命令行字符串
     *
     * @param array $options 配置选项
     * @return string 格式化的配置字符串
     */
    public static function formatConfigureOptions(array $options)
    {
        return implode(' ', array_map('escapeshellarg', $options));
    }

    /**
     * 解析配置字符串为选项数组
     *
     * @param string $configureString 配置字符串
     * @return array 配置选项数组
     */
    public static function parseConfigureString($configureString)
    {
        // 简单的解析，按空格分割
        return array_filter(explode(' ', trim($configureString)));
    }

    /**
     * 获取配置选项的说明
     *
     * @param string $option 配置选项
     * @return string 选项说明
     */
    public static function getOptionDescription($option)
    {
        $descriptions = [
            '--enable-bcmath' => '启用BC数学函数',
            '--enable-calendar' => '启用日历函数',
            '--enable-dba' => '启用数据库抽象层',
            '--enable-exif' => '启用EXIF图像元数据支持',
            '--enable-ftp' => '启用FTP支持',
            '--enable-mbstring' => '启用多字节字符串支持',
            '--with-mysqli' => '启用MySQLi扩展',
            '--with-pdo-mysql' => '启用PDO MySQL驱动',
            '--with-pdo-sqlite' => '启用PDO SQLite驱动',
            '--with-openssl' => '启用OpenSSL支持',
            '--enable-sockets' => '启用套接字支持',
            '--enable-soap' => '启用SOAP支持',
            '--enable-zip' => '启用ZIP支持',
            '--with-zlib' => '启用zlib压缩支持',
            '--with-curl' => '启用cURL支持',
            '--enable-opcache' => '启用OPcache',
            '--enable-gd' => '启用GD图像处理库',
            '--with-jpeg' => '启用JPEG支持',
            '--with-webp' => '启用WebP支持',
            '--with-freetype' => '启用FreeType字体支持',
            '--with-xpm' => '启用XPM图像支持',
            '--with-avif' => '启用AVIF图像支持',
            '--with-ffi' => '启用FFI（外部函数接口）',
            '--enable-jit' => '启用JIT编译器',
            '--enable-fpm' => '启用PHP-FPM',
            '--enable-debug' => '启用调试模式',
            '--disable-debug' => '禁用调试模式',
        ];
        
        return isset($descriptions[$option]) ? $descriptions[$option] : '未知选项';
    }
}
