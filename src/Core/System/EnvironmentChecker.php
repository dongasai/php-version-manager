<?php

namespace VersionManager\Core\System;

/**
 * 环境检查类
 *
 * 用于检查基础PHP环境是否满足PVM运行的要求
 */
class EnvironmentChecker
{
    /**
     * 必需的PHP扩展列表
     *
     * @var array
     */
    private $requiredExtensions = [
        'curl',
        'json',
        'zip',
        'openssl',
        'mbstring',
        'phar'
    ];

    /**
     * 推荐的PHP扩展列表
     *
     * @var array
     */
    private $recommendedExtensions = [
        'zlib',
        'xml',
        'fileinfo',
        'posix'
    ];

    /**
     * 最低PHP版本要求
     *
     * @var string
     */
    private $minPhpVersion = '5.4.0';

    /**
     * 检查环境
     *
     * @param bool $throwException 是否抛出异常
     * @return array 检查结果
     * @throws \Exception 如果环境不满足要求且$throwException为true
     */
    public function check($throwException = false)
    {
        $result = [
            'php_version' => PHP_VERSION,
            'php_version_ok' => version_compare(PHP_VERSION, $this->minPhpVersion, '>='),
            'missing_required_extensions' => [],
            'missing_recommended_extensions' => [],
            'is_ok' => true
        ];

        // 检查PHP版本
        if (!$result['php_version_ok']) {
            $result['is_ok'] = false;
            if ($throwException) {
                throw new \Exception("PHP版本不满足要求，需要 {$this->minPhpVersion} 或更高版本，当前版本为 " . PHP_VERSION);
            }
        }

        // 检查必需的扩展
        foreach ($this->requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $result['missing_required_extensions'][] = $extension;
                $result['is_ok'] = false;
            }
        }

        // 检查推荐的扩展
        foreach ($this->recommendedExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $result['missing_recommended_extensions'][] = $extension;
            }
        }

        // 如果有缺失的必需扩展且需要抛出异常
        if (!empty($result['missing_required_extensions']) && $throwException) {
            throw new \Exception("缺少必需的PHP扩展: " . implode(', ', $result['missing_required_extensions']));
        }

        return $result;
    }

    /**
     * 获取环境检查结果的详细信息
     *
     * @return string 详细信息
     */
    public function getDetailedInfo()
    {
        $result = $this->check();
        $info = "PHP版本: " . PHP_VERSION;

        if (!$result['php_version_ok']) {
            $info .= " (不满足要求，需要 {$this->minPhpVersion} 或更高版本)";
        } else {
            $info .= " (满足要求)";
        }

        $info .= "\n\n必需的PHP扩展:\n";
        foreach ($this->requiredExtensions as $extension) {
            $loaded = extension_loaded($extension);
            $info .= "  - {$extension}: " . ($loaded ? "已加载" : "未加载 (必需)") . "\n";
        }

        $info .= "\n推荐的PHP扩展:\n";
        foreach ($this->recommendedExtensions as $extension) {
            $loaded = extension_loaded($extension);
            $info .= "  - {$extension}: " . ($loaded ? "已加载" : "未加载 (推荐)") . "\n";
        }

        if (!$result['is_ok']) {
            $info .= "\n环境不满足PVM运行的要求，请安装缺失的扩展后再试。\n";

            // 提供安装扩展的建议
            $info .= "\n安装缺失扩展的建议:\n";

            // 检测包管理器
            $packageManager = $this->detectPackageManager();

            if ($packageManager) {
                switch ($packageManager) {
                    case 'apt':
                        $info .= "Ubuntu/Debian系统:\n";
                        $info .= "  sudo apt-get update\n";
                        $info .= "  sudo apt-get install php-curl php-json php-zip php-openssl php-mbstring php-phar\n";
                        break;
                    case 'yum':
                    case 'dnf':
                        $info .= "CentOS/RHEL/Fedora系统:\n";
                        $info .= "  sudo " . $packageManager . " install php-curl php-json php-zip php-openssl php-mbstring php-phar\n";
                        break;
                    case 'apk':
                        $info .= "Alpine系统:\n";
                        $info .= "  apk add php-curl php-json php-zip php-openssl php-mbstring php-phar\n";
                        break;
                }
            } else {
                $info .= "请使用您系统的包管理器安装缺失的PHP扩展。\n";
            }
        }

        return $info;
    }

    /**
     * 检测包管理器
     *
     * @return string|null 包管理器名称，如果未检测到则返回null
     */
    private function detectPackageManager()
    {
        $packageManagers = [
            'apt' => 'apt-get',
            'yum' => 'yum',
            'dnf' => 'dnf',
            'apk' => 'apk'
        ];

        foreach ($packageManagers as $name => $command) {
            $output = [];
            exec("which {$command} 2>/dev/null", $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                return $name;
            }
        }

        return null;
    }
}
