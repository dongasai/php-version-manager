<?php

namespace VersionManager\Core\Version\Util;

use VersionManager\Core\Logger\FileLogger;

/**
 * 文件操作助手类
 *
 * 提供文件和目录操作相关的通用功能
 */
class FileHelper
{
    /**
     * 创建临时目录
     *
     * @param string $prefix 目录前缀
     * @return string 临时目录路径
     */
    public static function createTempDir($prefix = 'pvm_')
    {
        $tempDir = sys_get_temp_dir() . '/' . $prefix . uniqid();

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return $tempDir;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     * @return bool
     */
    public static function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        echo "正在清理目录: " . basename($dir) . "\n";

        // 使用系统命令删除目录，这样更快
        $command = "rm -rf " . escapeshellarg($dir);
        FileLogger::info("执行删除命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        passthru($command, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("删除命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            echo "警告: 使用系统命令删除目录失败，尝试使用PHP递归删除\n";

            // 如果系统命令失败，则使用PHP递归删除
            return self::removeDirectoryRecursive($dir);
        } else {
            FileLogger::info("删除命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        echo "目录清理完成\n";
        return true;
    }

    /**
     * PHP递归删除目录
     *
     * @param string $dir 目录路径
     * @return bool
     */
    private static function removeDirectoryRecursive($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $objects = scandir($dir);

        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }

            $path = $dir . '/' . $object;

            if (is_dir($path)) {
                self::removeDirectoryRecursive($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    /**
     * 解压文件
     *
     * @param string $file 压缩文件路径
     * @param string $destination 目标目录
     * @return bool
     */
    public static function extractFile($file, $destination)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'gz':
            case 'tgz':
                $command = "tar -xzf {$file} -C {$destination}";
                break;
            case 'bz2':
                $command = "tar -xjf {$file} -C {$destination}";
                break;
            case 'xz':
                $command = "tar -xJf {$file} -C {$destination}";
                break;
            case 'zip':
                $command = "unzip {$file} -d {$destination}";
                break;
            default:
                throw new \Exception("不支持的压缩格式: {$extension}");
        }

        $output = [];
        $returnCode = 0;

        // 记录命令执行
        FileLogger::info("执行解压命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        exec($command . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("解压命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("解压文件失败: " . implode("\n", $output));
        } else {
            FileLogger::info("解压命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        return true;
    }

    /**
     * 查找PHP源码目录
     *
     * @param string $baseDir 基础目录
     * @return string|false
     */
    public static function findPhpSourceDir($baseDir)
    {
        // 查找php-x.x.x目录
        $dirs = glob($baseDir . '/php-*', GLOB_ONLYDIR);
        if (!empty($dirs)) {
            return $dirs[0];
        }

        // 如果没有找到，则检查是否直接解压到了基础目录
        if (file_exists($baseDir . '/configure') && file_exists($baseDir . '/LICENSE')) {
            return $baseDir;
        }

        return false;
    }

    /**
     * 确保目录存在
     *
     * @param string $dir 目录路径
     * @param int $mode 目录权限
     * @return bool
     */
    public static function ensureDirectoryExists($dir, $mode = 0755)
    {
        if (!is_dir($dir)) {
            return mkdir($dir, $mode, true);
        }
        return true;
    }

    /**
     * 复制文件
     *
     * @param string $source 源文件路径
     * @param string $destination 目标文件路径
     * @return bool
     */
    public static function copyFile($source, $destination)
    {
        if (!file_exists($source)) {
            throw new \Exception("源文件不存在: {$source}");
        }

        // 确保目标目录存在
        $destinationDir = dirname($destination);
        self::ensureDirectoryExists($destinationDir);

        return copy($source, $destination);
    }

    /**
     * 移动文件
     *
     * @param string $source 源文件路径
     * @param string $destination 目标文件路径
     * @return bool
     */
    public static function moveFile($source, $destination)
    {
        if (!file_exists($source)) {
            throw new \Exception("源文件不存在: {$source}");
        }

        // 确保目标目录存在
        $destinationDir = dirname($destination);
        self::ensureDirectoryExists($destinationDir);

        return rename($source, $destination);
    }

    /**
     * 获取文件大小（人类可读格式）
     *
     * @param string $file 文件路径
     * @return string 文件大小
     */
    public static function getHumanReadableFileSize($file)
    {
        if (!file_exists($file)) {
            return '0 B';
        }

        $size = filesize($file);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * 检查文件是否可执行
     *
     * @param string $file 文件路径
     * @return bool
     */
    public static function isExecutable($file)
    {
        return file_exists($file) && is_executable($file);
    }

    /**
     * 设置文件权限
     *
     * @param string $file 文件路径
     * @param int $mode 权限模式
     * @return bool
     */
    public static function setPermissions($file, $mode)
    {
        if (!file_exists($file)) {
            throw new \Exception("文件不存在: {$file}");
        }

        return chmod($file, $mode);
    }

    /**
     * 获取目录大小
     *
     * @param string $dir 目录路径
     * @return int 目录大小（字节）
     */
    public static function getDirectorySize($dir)
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * 获取目录大小（人类可读格式）
     *
     * @param string $dir 目录路径
     * @return string 目录大小
     */
    public static function getHumanReadableDirectorySize($dir)
    {
        $size = self::getDirectorySize($dir);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * 创建符号链接
     *
     * @param string $target 目标路径
     * @param string $link 链接路径
     * @return bool
     */
    public static function createSymlink($target, $link)
    {
        if (!file_exists($target)) {
            throw new \Exception("目标文件不存在: {$target}");
        }

        // 如果链接已存在，先删除
        if (is_link($link)) {
            unlink($link);
        }

        return symlink($target, $link);
    }

    /**
     * 检查是否为符号链接
     *
     * @param string $path 路径
     * @return bool
     */
    public static function isSymlink($path)
    {
        return is_link($path);
    }

    /**
     * 获取符号链接的目标
     *
     * @param string $link 链接路径
     * @return string|false 目标路径，失败返回false
     */
    public static function getSymlinkTarget($link)
    {
        if (!self::isSymlink($link)) {
            return false;
        }

        return readlink($link);
    }
}
