<?php

namespace VersionManager\Core\Version\Drivers\PHP73;

/**
 * PHP 7.3 Ubuntu版本安装驱动类
 */
class Ubuntu extends Base
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'php73_ubuntu';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = 'PHP 7.3 Ubuntu版本安装驱动';

    /**
     * {@inheritdoc}
     */
    protected function getDependencies($version)
    {
        // 获取基本依赖
        $dependencies = parent::getDependencies($version);

        // 添加Ubuntu特定的依赖
        $ubuntuDependencies = [
            'libreadline-dev',
            'libsodium-dev',
            'libargon2-dev',
        ];

        return array_merge($dependencies, $ubuntuDependencies);
    }

    /**
     * {@inheritdoc}
     */
    protected function installDependencies(array $dependencies)
    {
        // Ubuntu特定的依赖安装方式
        $command = 'apt-get update && apt-get install -y ' . implode(' ', $dependencies);
        $output = [];
        $returnCode = 0;

        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }
}
