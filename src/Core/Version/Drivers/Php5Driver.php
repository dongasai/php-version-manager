<?php

namespace VersionManager\Core\Version\Drivers;

use VersionManager\Core\Version\GenericVersionDriver;

/**
 * PHP 5.x版本安装驱动类
 */
class Php5Driver extends GenericVersionDriver
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'php5';
        $this->description = 'PHP 5.x版本安装驱动';
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 检查版本格式
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            return false;
        }

        // 检查版本是否在支持范围内
        list($major, $minor, $patch) = explode('.', $version);

        // 支持PHP 5.4及以上版本
        if ($major != 5 || $minor < 4) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取版本信息
        list($major, $minor, $patch) = explode('.', $version);

        // 基本配置选项
        $configureOptions = [
            "--prefix={$this->versionsDir}/{$version}",
            "--with-config-file-path={$this->versionsDir}/{$version}/etc",
            "--with-config-file-scan-dir={$this->versionsDir}/{$version}/etc/conf.d",
            "--enable-fpm",
            "--enable-mbstring",
            "--enable-zip",
            "--with-mysql",
            "--with-mysqli",
            "--with-pdo-mysql",
            "--with-curl",
            "--with-openssl",
            "--with-zlib",
            "--with-gd",
            "--with-jpeg-dir",
            "--with-png-dir",
            "--with-freetype-dir",
            "--enable-bcmath",
            "--enable-exif",
            "--enable-ftp",
            "--enable-intl",
            "--enable-soap",
            "--enable-sockets",
        ];

        // PHP 5.5及以上版本支持opcache
        if ($minor >= 5) {
            $configureOptions[] = "--enable-opcache";
        }

        // PHP 5.4特定配置
        if ($minor == 4) {
            // PHP 5.4使用旧版本的mysql扩展
            $configureOptions[] = "--with-mysql=mysqlnd";
            $configureOptions[] = "--with-mysqli=mysqlnd";
            $configureOptions[] = "--with-pdo-mysql=mysqlnd";
        }

        // 添加自定义配置选项
        if (isset($options['configure_options']) && is_array($options['configure_options'])) {
            $configureOptions = array_merge($configureOptions, $options['configure_options']);
        }

        return $configureOptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSourceUrl($version, $mirror = null)
    {
        // 使用PHP官方源码URL
        return "https://www.php.net/distributions/php-{$version}.tar.gz";
    }

    /**
     * {@inheritdoc}
     */
    protected function getDependencies($version)
    {
        // 基本依赖
        $dependencies = [
            'build-essential',
            'libxml2-dev',
            'libssl-dev',
            'libcurl4-openssl-dev',
            'libjpeg-dev',
            'libpng-dev',
            'libfreetype6-dev',
            'libmcrypt-dev',
            'libreadline-dev',
            'libedit-dev',
            'libzip-dev',
            'libicu-dev',
        ];

        // 获取版本信息
        list($major, $minor, $patch) = explode('.', $version);

        // PHP 5.4特定依赖
        if ($minor == 4) {
            $dependencies[] = 'libmysqlclient-dev';
        }

        return $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    protected function compileAndInstall($sourceDir, $version, array $configureOptions)
    {
        // 当前目录
        $currentDir = getcwd();

        // 进入源码目录
        chdir($sourceDir);

        // 配置
        $configureCommand = './configure ' . implode(' ', $configureOptions);
        $output = [];
        $returnCode = 0;

        exec($configureCommand . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("配置PHP失败: " . implode("\n", $output));
        }

        // 编译
        $output = [];
        $returnCode = 0;

        // 获取CPU核心数
        $cpuCores = $this->getCpuCores();

        exec("make -j{$cpuCores} 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("编译PHP失败: " . implode("\n", $output));
        }

        // 安装
        $output = [];
        $returnCode = 0;

        exec('make install 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("安装PHP失败: " . implode("\n", $output));
        }

        // 复制配置文件
        $this->copyConfigFiles($version);

        // 返回到原目录
        chdir($currentDir);

        return true;
    }

    /**
     * 获取CPU核心数
     *
     * @return int
     */
    private function getCpuCores()
    {
        $cores = 1;

        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cores = count($matches[0]);
        }

        return $cores > 0 ? $cores : 1;
    }

    /**
     * 复制配置文件
     *
     * @param string $version PHP版本
     * @return bool
     */
    private function copyConfigFiles($version)
    {
        $versionDir = $this->versionsDir . '/' . $version;
        $configDir = $versionDir . '/etc';
        $configScanDir = $configDir . '/conf.d';

        // 创建配置目录
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        if (!is_dir($configScanDir)) {
            mkdir($configScanDir, 0755, true);
        }

        // 复制php.ini文件
        $phpIniDevelopment = 'php.ini-development';
        $phpIniProduction = 'php.ini-production';

        if (file_exists($phpIniDevelopment)) {
            copy($phpIniDevelopment, $configDir . '/php.ini-development');
        }

        if (file_exists($phpIniProduction)) {
            copy($phpIniProduction, $configDir . '/php.ini-production');
        }

        // 创建默认配置文件
        if (file_exists($phpIniDevelopment)) {
            copy($phpIniDevelopment, $configDir . '/php.ini');
        }

        return true;
    }
}
