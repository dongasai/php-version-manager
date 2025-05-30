<?php

namespace VersionManager\Core\Version\Drivers\PHP71;

/**
 * PHP 7.1 Debian版本安装驱动类
 */
class Debian extends Base
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'php71_debian';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = 'PHP 7.1 Debian版本安装驱动';

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);

        // 添加Debian特定的配置选项
        $debianOptions = [
            '--with-fpm-user=www-data',
            '--with-fpm-group=www-data',
        ];

        // 合并配置选项
        $configureOptions = array_merge($configureOptions, $debianOptions);

        return $configureOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function install($version, array $options = [])
    {
        // 安装依赖
        $this->installDependencies();

        // 调用父类的安装方法
        return parent::install($version, $options);
    }

    /**
     * 安装依赖（旧版本方法，已重构）
     *
     * @param array $dependencies 依赖列表（如果为空，使用默认依赖）
     * @return bool
     */
    protected function installDependencies(array $dependencies = [])
    {
        // 在Docker容器中测试时，跳过依赖安装
        if (getenv('DOCKER_CONTAINER') === 'true' || file_exists('/.dockerenv')) {
            echo "在Docker容器中检测到，跳过依赖安装...\n";
            return true;
        }

        // 如果没有提供依赖列表，使用默认的PHP 7.1依赖
        if (empty($dependencies)) {
            $dependencies = [
                'build-essential',
                'libxml2-dev',
                'libssl-dev',
                'libsqlite3-dev',
                'zlib1g-dev',
                'libcurl4-openssl-dev',
                'libpng-dev',
                'libjpeg-dev',
                'libfreetype6-dev',
                'libwebp-dev',
                'libxpm-dev',
                'libmcrypt-dev',
            ];
        }

        // 使用父类的新方法
        return parent::installDependencies($dependencies);
    }
}
