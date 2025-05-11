<?php

namespace VersionManager\Core\Extension\Drivers\Gd;

/**
 * Ubuntu系统上的GD扩展驱动类
 */
class Ubuntu extends Base
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
            . 'libgd-dev';  // Ubuntu特有的GD开发包
        
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
        // 添加Ubuntu特定的配置
        $config['enable_gd_jis_conv'] = '1';
        
        return parent::enable($phpVersion, $config);
    }
}
