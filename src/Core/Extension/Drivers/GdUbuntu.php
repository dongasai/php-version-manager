<?php

namespace VersionManager\Core\Extension\Drivers;

/**
 * Ubuntu系统上的GD扩展驱动类
 */
class GdUbuntu extends Gd
{
    /**
     * 安装GD依赖
     */
    private function installDependencies()
    {
        $command = 'apt-get update && apt-get install -y '
            . 'libpng-dev '
            . 'libjpeg-dev '
            . 'libfreetype6-dev '
            . 'libwebp-dev '
            . 'libxpm-dev';
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 GD 依赖失败: " . implode("\n", $output));
        }
    }
}
