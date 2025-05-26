<?php

namespace Mirror\Command;

use Mirror\Mirror\PhpMirror;
use Mirror\Mirror\PeclMirror;
use Mirror\Mirror\ExtensionMirror;
use Mirror\Mirror\ComposerMirror;

/**
 * 同步命令类
 */
class SyncCommand extends AbstractCommand
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct('sync', '同步镜像内容');
    }

    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 退出代码
     */
    public function execute(array $args = [])
    {
        // 如果有参数，则进行指定同步
        if (!empty($args)) {
            return $this->executeSpecificSync($args);
        }

        // 无参数时同步所有内容
        return $this->executeFullSync();
    }

    /**
     * 执行指定内容同步
     *
     * @param array $args 命令参数
     * @return int 退出代码
     */
    private function executeSpecificSync(array $args)
    {
        $type = $args[0];
        $version = isset($args[1]) ? $args[1] : null;

        echo "开始同步指定镜像内容...\n";
        echo "类型: $type" . ($version ? ", 版本: $version" : "") . "\n\n";

        // 加载配置
        $configs = $this->loadConfig();
        $mirrorConfig = $configs['mirror'];
        $runtimeConfig = $configs['runtime'];

        // 显示同步配置信息
        $this->showSyncConfig($runtimeConfig);

        switch ($type) {
            case 'composer':
                return $this->syncComposer($mirrorConfig, $version);

            case 'php':
                return $this->syncPhp($mirrorConfig, $version);

            case 'pecl':
                return $this->syncPecl($mirrorConfig, $version);

            case 'extensions':
            case 'ext':
                return $this->syncExtensions($mirrorConfig, $version);

            default:
                echo "错误: 未知的同步类型 '$type'\n";
                echo "支持的类型: composer, php, pecl, extensions\n";
                echo "\n用法示例:\n";
                echo "  pvm-mirror sync composer           # 同步所有 Composer 版本\n";
                echo "  pvm-mirror sync composer 2.6.5     # 同步指定 Composer 版本\n";
                echo "  pvm-mirror sync php                # 同步所有 PHP 版本\n";
                echo "  pvm-mirror sync php 8.3            # 同步指定 PHP 主版本\n";
                echo "  pvm-mirror sync pecl               # 同步所有 PECL 扩展\n";
                echo "  pvm-mirror sync extensions          # 同步所有特定扩展\n";
                return 1;
        }
    }

    /**
     * 执行完整同步
     *
     * @return int 退出代码
     */
    private function executeFullSync()
    {
        echo "开始同步镜像内容...\n";

        // 加载配置
        $configs = $this->loadConfig();
        $mirrorConfig = $configs['mirror'];
        $runtimeConfig = $configs['runtime'];

        // 显示同步配置信息
        $this->showSyncConfig($runtimeConfig);

        // 同步所有内容
        $this->syncPhp($mirrorConfig);
        $this->syncPecl($mirrorConfig);
        $this->syncExtensions($mirrorConfig);
        $this->syncComposer($mirrorConfig);

        echo "\n镜像同步完成\n";
        return 0;
    }

    /**
     * 显示同步配置信息
     *
     * @param array $runtimeConfig 运行时配置
     */
    private function showSyncConfig(array $runtimeConfig)
    {
        $syncConfig = $runtimeConfig['sync'] ?? [];
        echo "同步配置:\n";
        echo "  最大重试次数: " . ($syncConfig['max_retries'] ?? 3) . "\n";
        echo "  重试间隔: " . ($syncConfig['retry_interval'] ?? 300) . " 秒\n";
        echo "  下载超时: " . ($syncConfig['download_timeout'] ?? 600) . " 秒\n";
        echo "  最大并行下载数: " . ($syncConfig['max_parallel_downloads'] ?? 5) . "\n";
    }

    /**
     * 同步 PHP 源码包
     *
     * @param array $mirrorConfig 镜像配置
     * @param string|null $version 指定版本
     * @return int 退出代码
     */
    private function syncPhp(array $mirrorConfig, $version = null)
    {
        if (!isset($mirrorConfig['php']['enabled']) || !$mirrorConfig['php']['enabled']) {
            echo "\n跳过 PHP 源码包同步 (已禁用)\n";
            return 0;
        }

        echo "\n同步 PHP 源码包...\n";
        $phpMirror = new PhpMirror();

        if ($version) {
            return $phpMirror->syncVersion($mirrorConfig['php'], $version) ? 0 : 1;
        } else {
            return $phpMirror->sync($mirrorConfig['php']) ? 0 : 1;
        }
    }

    /**
     * 同步 PECL 扩展包
     *
     * @param array $mirrorConfig 镜像配置
     * @param string|null $extension 指定扩展
     * @return int 退出代码
     */
    private function syncPecl(array $mirrorConfig, $extension = null)
    {
        if (!isset($mirrorConfig['pecl']['enabled']) || !$mirrorConfig['pecl']['enabled']) {
            echo "\n跳过 PECL 扩展包同步 (已禁用)\n";
            return 0;
        }

        echo "\n同步 PECL 扩展包...\n";
        $peclMirror = new PeclMirror();

        if ($extension) {
            return $peclMirror->syncExtension($mirrorConfig['pecl'], $extension) ? 0 : 1;
        } else {
            return $peclMirror->sync($mirrorConfig['pecl']) ? 0 : 1;
        }
    }

    /**
     * 同步特定扩展的 GitHub 源码
     *
     * @param array $mirrorConfig 镜像配置
     * @param string|null $extension 指定扩展
     * @return int 退出代码
     */
    private function syncExtensions(array $mirrorConfig, $extension = null)
    {
        $enabledExtensions = [];
        foreach ($mirrorConfig['extensions'] as $ext => $config) {
            if (isset($config['enabled']) && $config['enabled']) {
                if ($extension && $ext !== $extension) {
                    continue;
                }
                $enabledExtensions[$ext] = $config;
            }
        }

        if (empty($enabledExtensions)) {
            if ($extension) {
                echo "\n错误: 扩展 '$extension' 未启用或不存在\n";
                return 1;
            } else {
                echo "\n跳过特定扩展源码同步 (已禁用)\n";
                return 0;
            }
        }

        echo "\n同步特定扩展的 GitHub 源码...\n";
        $extensionMirror = new ExtensionMirror();
        return $extensionMirror->sync($enabledExtensions) ? 0 : 1;
    }

    /**
     * 同步 Composer 包
     *
     * @param array $mirrorConfig 镜像配置
     * @param string|null $version 指定版本
     * @return int 退出代码
     */
    private function syncComposer(array $mirrorConfig, $version = null)
    {
        if (!isset($mirrorConfig['composer']['enabled']) || !$mirrorConfig['composer']['enabled']) {
            echo "\n跳过 Composer 包同步 (已禁用)\n";
            return 0;
        }

        echo "\n同步 Composer 包...\n";
        $composerMirror = new ComposerMirror();

        if ($version) {
            return $composerMirror->syncVersion($mirrorConfig['composer'], $version) ? 0 : 1;
        } else {
            return $composerMirror->sync($mirrorConfig['composer']) ? 0 : 1;
        }
    }
}
