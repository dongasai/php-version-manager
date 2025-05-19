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
        echo "开始同步镜像内容...\n";

        // 加载配置
        $configs = $this->loadConfig();
        $mirrorConfig = $configs['mirror'];
        $runtimeConfig = $configs['runtime'];

        // 显示同步配置信息
        $syncConfig = $runtimeConfig['sync'] ?? [];
        echo "同步配置:\n";
        echo "  最大重试次数: " . ($syncConfig['max_retries'] ?? 3) . "\n";
        echo "  重试间隔: " . ($syncConfig['retry_interval'] ?? 300) . " 秒\n";
        echo "  下载超时: " . ($syncConfig['download_timeout'] ?? 600) . " 秒\n";
        echo "  最大并行下载数: " . ($syncConfig['max_parallel_downloads'] ?? 5) . "\n";

        // 同步 PHP 源码包
        if (isset($mirrorConfig['php']['enabled']) && $mirrorConfig['php']['enabled']) {
            echo "\n同步 PHP 源码包...\n";
            $phpMirror = new PhpMirror();
            $phpMirror->sync($mirrorConfig['php']);
        } else {
            echo "\n跳过 PHP 源码包同步 (已禁用)\n";
        }

        // 同步 PECL 扩展包
        if (isset($mirrorConfig['pecl']['enabled']) && $mirrorConfig['pecl']['enabled']) {
            echo "\n同步 PECL 扩展包...\n";
            $peclMirror = new PeclMirror();
            $peclMirror->sync($mirrorConfig['pecl']);
        } else {
            echo "\n跳过 PECL 扩展包同步 (已禁用)\n";
        }

        // 同步特定扩展的 GitHub 源码
        $enabledExtensions = [];
        foreach ($mirrorConfig['extensions'] as $extension => $config) {
            if (isset($config['enabled']) && $config['enabled']) {
                $enabledExtensions[$extension] = $config;
            }
        }

        if (!empty($enabledExtensions)) {
            echo "\n同步特定扩展的 GitHub 源码...\n";
            $extensionMirror = new ExtensionMirror();
            $extensionMirror->sync($enabledExtensions);
        } else {
            echo "\n跳过特定扩展源码同步 (已禁用)\n";
        }

        // 同步 Composer 包
        if (isset($mirrorConfig['composer']['enabled']) && $mirrorConfig['composer']['enabled']) {
            echo "\n同步 Composer 包...\n";
            $composerMirror = new ComposerMirror();
            $composerMirror->sync($mirrorConfig['composer']);
        } else {
            echo "\n跳过 Composer 包同步 (已禁用)\n";
        }

        echo "\n镜像同步完成\n";

        return 0;
    }
}
