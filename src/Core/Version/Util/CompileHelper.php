<?php

namespace VersionManager\Core\Version\Util;

use VersionManager\Core\Logger\FileLogger;

/**
 * 编译助手类
 *
 * 提供PHP编译相关的通用功能
 */
class CompileHelper
{
    /**
     * 编译安装PHP
     *
     * @param string $sourceDir 源码目录
     * @param string $version PHP版本
     * @param array $configureOptions 配置选项
     * @param int $jobs 并行编译任务数（默认4）
     * @return bool
     */
    public static function compileAndInstall($sourceDir, $version, array $configureOptions, $jobs = 4)
    {
        // 当前目录
        $currentDir = getcwd();

        try {
            // 进入源码目录
            chdir($sourceDir);

            // 配置
            self::configure($configureOptions);

            // 编译
            self::make($jobs);

            // 安装
            self::makeInstall();

            // 返回原目录
            chdir($currentDir);

            return true;
        } catch (\Exception $e) {
            // 返回原目录
            chdir($currentDir);
            throw $e;
        }
    }

    /**
     * 执行configure配置
     *
     * @param array $configureOptions 配置选项
     * @return bool
     */
    public static function configure(array $configureOptions)
    {
        $configureCommand = './configure ' . implode(' ', array_map('escapeshellarg', $configureOptions));
        $output = [];
        $returnCode = 0;

        echo "正在配置PHP编译选项...\n";
        FileLogger::info("执行配置命令: {$configureCommand}", 'COMMAND');
        $startTime = microtime(true);

        exec($configureCommand . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("配置命令执行失败: {$configureCommand}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("配置PHP失败: " . implode("\n", $output));
        } else {
            FileLogger::info("配置命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
            echo "PHP配置完成\n";
        }

        return true;
    }

    /**
     * 执行make编译
     *
     * @param int $jobs 并行编译任务数
     * @return bool
     */
    public static function make($jobs = 4)
    {
        $makeCommand = "make -j{$jobs}";
        $output = [];
        $returnCode = 0;

        echo "正在编译PHP（使用 {$jobs} 个并行任务）...\n";
        FileLogger::info("执行编译命令: {$makeCommand}", 'COMMAND');
        $startTime = microtime(true);

        exec($makeCommand . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("编译命令执行失败: {$makeCommand}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("编译PHP失败: " . implode("\n", $output));
        } else {
            FileLogger::info("编译命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
            echo "PHP编译完成\n";
        }

        return true;
    }

    /**
     * 执行make install安装
     *
     * @return bool
     */
    public static function makeInstall()
    {
        $installCommand = "make install";
        $output = [];
        $returnCode = 0;

        echo "正在安装PHP...\n";
        FileLogger::info("执行安装命令: {$installCommand}", 'COMMAND');
        $startTime = microtime(true);

        exec($installCommand . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("安装命令执行失败: {$installCommand}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("安装PHP失败: " . implode("\n", $output));
        } else {
            FileLogger::info("安装命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
            echo "PHP安装完成\n";
        }

        return true;
    }

    /**
     * 执行make test测试
     *
     * @return bool
     */
    public static function makeTest()
    {
        $testCommand = "make test";
        $output = [];
        $returnCode = 0;

        echo "正在运行PHP测试...\n";
        FileLogger::info("执行测试命令: {$testCommand}", 'COMMAND');
        $startTime = microtime(true);

        exec($testCommand . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        // 测试可能会有一些失败，不一定要求返回码为0
        FileLogger::info("测试命令执行完成，退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
        if (!empty($output)) {
            FileLogger::info("测试输出: " . implode("\n", $output), 'COMMAND');
        }
        echo "PHP测试完成\n";

        return true;
    }

    /**
     * 清理编译文件
     *
     * @return bool
     */
    public static function makeClean()
    {
        $cleanCommand = "make clean";
        $output = [];
        $returnCode = 0;

        echo "正在清理编译文件...\n";
        FileLogger::info("执行清理命令: {$cleanCommand}", 'COMMAND');
        $startTime = microtime(true);

        exec($cleanCommand . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::warning("清理命令执行失败: {$cleanCommand}", 'COMMAND');
            FileLogger::warning("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            // 清理失败不抛出异常，只记录警告
        } else {
            FileLogger::info("清理命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
            echo "编译文件清理完成\n";
        }

        return true;
    }

    /**
     * 检查编译依赖
     *
     * @param array $requiredCommands 必需的命令列表
     * @return array 检查结果
     */
    public static function checkCompileDependencies(array $requiredCommands = [])
    {
        $defaultCommands = ['gcc', 'make', 'autoconf', 'pkg-config'];
        $commands = array_merge($defaultCommands, $requiredCommands);
        
        $results = [];
        $allAvailable = true;

        foreach ($commands as $command) {
            $available = self::isCommandAvailable($command);
            $results[$command] = $available;
            
            if (!$available) {
                $allAvailable = false;
            }
        }

        return [
            'all_available' => $allAvailable,
            'commands' => $results
        ];
    }

    /**
     * 检查命令是否可用
     *
     * @param string $command 命令名
     * @return bool
     */
    public static function isCommandAvailable($command)
    {
        $output = [];
        $returnCode = 0;

        exec("which {$command} 2>/dev/null", $output, $returnCode);

        return $returnCode === 0 && !empty($output);
    }

    /**
     * 获取系统CPU核心数
     *
     * @return int CPU核心数
     */
    public static function getCpuCoreCount()
    {
        $output = [];
        $returnCode = 0;

        exec("nproc 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return (int)$output[0];
        }

        // 备用方法
        exec("grep -c ^processor /proc/cpuinfo 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return (int)$output[0];
        }

        // 默认返回4
        return 4;
    }

    /**
     * 获取推荐的编译任务数
     *
     * @return int 推荐的任务数
     */
    public static function getRecommendedJobCount()
    {
        $coreCount = self::getCpuCoreCount();
        
        // 使用CPU核心数，但不超过8
        return min($coreCount, 8);
    }

    /**
     * 检查磁盘空间是否足够
     *
     * @param string $path 路径
     * @param int $requiredBytes 需要的字节数
     * @return bool
     */
    public static function checkDiskSpace($path, $requiredBytes)
    {
        $freeBytes = disk_free_space($path);
        
        if ($freeBytes === false) {
            return false;
        }

        return $freeBytes >= $requiredBytes;
    }

    /**
     * 获取编译预估时间
     *
     * @param string $version PHP版本
     * @param int $jobs 并行任务数
     * @return array 预估时间信息
     */
    public static function getEstimatedCompileTime($version, $jobs)
    {
        // 基础编译时间（分钟）- 基于经验值
        $baseTime = 15;
        
        // 根据PHP版本调整
        if (VersionHelper::isPhp8OrHigher($version)) {
            $baseTime += 5; // PHP 8+ 编译时间稍长
        }
        
        // 根据并行任务数调整
        $adjustedTime = $baseTime / max($jobs, 1);
        
        // 最少5分钟
        $estimatedMinutes = max($adjustedTime, 5);
        
        return [
            'minutes' => round($estimatedMinutes),
            'human_readable' => self::formatDuration($estimatedMinutes * 60)
        ];
    }

    /**
     * 格式化持续时间
     *
     * @param int $seconds 秒数
     * @return string 格式化的时间
     */
    private static function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' 秒';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . ' 分钟';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = round(($seconds % 3600) / 60);
            return $hours . ' 小时 ' . $minutes . ' 分钟';
        }
    }
}
