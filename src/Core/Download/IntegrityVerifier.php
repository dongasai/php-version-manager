<?php

namespace VersionManager\Core\Download;

use VersionManager\Core\Logger\FileLogger;

/**
 * 文件完整性校验器
 * 
 * 提供多种文件完整性校验方法，包括MD5、SHA256等
 */
class IntegrityVerifier
{
    /**
     * 支持的校验算法
     */
    const SUPPORTED_ALGORITHMS = ['md5', 'sha1', 'sha256', 'sha512'];

    /**
     * 校验文件完整性
     *
     * @param string $filePath 文件路径
     * @param string $expectedHash 期望的哈希值
     * @param string $algorithm 校验算法 (md5, sha1, sha256, sha512)
     * @return bool 是否通过校验
     */
    public static function verifyFile($filePath, $expectedHash, $algorithm = 'md5')
    {
        // 检查文件是否存在
        if (!file_exists($filePath)) {
            FileLogger::error("校验失败: 文件不存在 - {$filePath}", 'INTEGRITY');
            return false;
        }

        // 检查算法是否支持
        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, self::SUPPORTED_ALGORITHMS)) {
            FileLogger::error("校验失败: 不支持的算法 - {$algorithm}", 'INTEGRITY');
            return false;
        }

        // 计算文件哈希值
        $actualHash = self::calculateHash($filePath, $algorithm);
        if ($actualHash === false) {
            FileLogger::error("校验失败: 无法计算文件哈希值 - {$filePath}", 'INTEGRITY');
            return false;
        }

        // 比较哈希值（不区分大小写）
        $passed = strcasecmp($expectedHash, $actualHash) === 0;

        // 记录校验结果
        FileLogger::logIntegrityCheck($filePath, $algorithm, $expectedHash, $actualHash, $passed);

        return $passed;
    }

    /**
     * 计算文件哈希值
     *
     * @param string $filePath 文件路径
     * @param string $algorithm 算法
     * @return string|false 哈希值，失败时返回false
     */
    public static function calculateHash($filePath, $algorithm = 'md5')
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, self::SUPPORTED_ALGORITHMS)) {
            return false;
        }

        return hash_file($algorithm, $filePath);
    }

    /**
     * 验证下载文件的完整性（支持多种校验方式）
     *
     * @param string $filePath 文件路径
     * @param array $checksums 校验和数组，格式: ['md5' => 'hash', 'sha256' => 'hash']
     * @return bool 是否通过所有校验
     */
    public static function verifyDownloadedFile($filePath, array $checksums)
    {
        if (empty($checksums)) {
            FileLogger::debug("跳过完整性校验: 未提供校验和", 'INTEGRITY');
            return true;
        }

        $allPassed = true;

        foreach ($checksums as $algorithm => $expectedHash) {
            if (!self::verifyFile($filePath, $expectedHash, $algorithm)) {
                $allPassed = false;
                break; // 任何一个校验失败就停止
            }
        }

        return $allPassed;
    }

    /**
     * 生成文件的多种校验和
     *
     * @param string $filePath 文件路径
     * @param array $algorithms 要生成的算法列表
     * @return array 校验和数组
     */
    public static function generateChecksums($filePath, array $algorithms = ['md5', 'sha256'])
    {
        $checksums = [];

        if (!file_exists($filePath)) {
            return $checksums;
        }

        foreach ($algorithms as $algorithm) {
            $algorithm = strtolower($algorithm);
            if (in_array($algorithm, self::SUPPORTED_ALGORITHMS)) {
                $hash = self::calculateHash($filePath, $algorithm);
                if ($hash !== false) {
                    $checksums[$algorithm] = $hash;
                }
            }
        }

        return $checksums;
    }

    /**
     * 验证文件大小
     *
     * @param string $filePath 文件路径
     * @param int $expectedSize 期望的文件大小
     * @return bool 是否匹配
     */
    public static function verifyFileSize($filePath, $expectedSize)
    {
        if (!file_exists($filePath)) {
            FileLogger::error("大小校验失败: 文件不存在 - {$filePath}", 'INTEGRITY');
            return false;
        }

        $actualSize = filesize($filePath);
        $passed = ($actualSize === $expectedSize);

        if (!$passed) {
            FileLogger::error(
                "文件大小校验失败 - 文件: {$filePath}, 期望: {$expectedSize}, 实际: {$actualSize}",
                'INTEGRITY'
            );
        } else {
            FileLogger::debug(
                "文件大小校验通过 - 文件: {$filePath}, 大小: {$actualSize}",
                'INTEGRITY'
            );
        }

        return $passed;
    }

    /**
     * 检查文件是否为空或损坏
     *
     * @param string $filePath 文件路径
     * @param int $minSize 最小文件大小（字节）
     * @return bool 文件是否有效
     */
    public static function isFileValid($filePath, $minSize = 1)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $fileSize = filesize($filePath);
        if ($fileSize < $minSize) {
            FileLogger::warning(
                "文件可能损坏: 文件大小过小 - 文件: {$filePath}, 大小: {$fileSize}, 最小要求: {$minSize}",
                'INTEGRITY'
            );
            return false;
        }

        // 检查文件是否可读
        if (!is_readable($filePath)) {
            FileLogger::error("文件不可读 - {$filePath}", 'INTEGRITY');
            return false;
        }

        return true;
    }

    /**
     * 获取支持的算法列表
     *
     * @return array 支持的算法列表
     */
    public static function getSupportedAlgorithms()
    {
        return self::SUPPORTED_ALGORITHMS;
    }
}
