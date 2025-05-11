<?php

namespace VersionManager\Core\Extension\Drivers\Gd;

/**
 * Ubuntu 22.04系统上的GD扩展驱动类
 */
class Ubuntu22 extends Ubuntu
{
    /**
     * 安装Debian/Ubuntu依赖
     */
    protected function installDebianDependencies()
    {
        $command = 'apt-get update && apt-get install -y '
            . 'libpng-dev '
            . 'libjpeg-dev '
            . 'libfreetype6-dev '
            . 'libwebp-dev '
            . 'libxpm-dev '
            . 'libgd-dev '
            . 'libavif-dev';  // Ubuntu 22.04特有的AVIF支持
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("安装 GD 依赖失败: " . implode("\n", $output));
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function enable($phpVersion, array $config = [])
    {
        // 添加Ubuntu 22.04特定的配置
        $config['enable_gd_jis_conv'] = '1';
        $config['with_avif'] = '1';
        
        return parent::enable($phpVersion, $config);
    }
}
