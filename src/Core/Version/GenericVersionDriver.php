<?php

namespace VersionManager\Core\Version;

use VersionManager\Core\Config\MirrorConfig;

/**
 * 通用版本安装驱动类
 *
 * 用于处理没有特定驱动的情况
 */
class GenericVersionDriver extends AbstractVersionDriver
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'generic';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = '通用版本安装驱动';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return ['generic'];
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

        // 支持PHP 7.1及以上版本，但不支持PHP 5.x版本
        if ($major == 5 || $major < 7 || ($major == 7 && $minor < 1)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function install($version, array $options = [])
    {
        // 检查版本是否支持
        if (!$this->isSupported($version)) {
            throw new \Exception("不支持的PHP版本: {$version}");
        }

        // 检查版本是否已安装
        if ($this->isInstalled($version)) {
            throw new \Exception("PHP版本 {$version} 已经安装");
        }

        // 创建临时目录
        $tempDir = $this->createTempDir('pvm_php_' . $version . '_');

        try {
            // 下载PHP源码
            $mirror = isset($options['mirror']) ? $options['mirror'] : null;
            $sourceUrl = $this->getSourceUrl($version, $mirror);
            $sourceFile = $tempDir . '/' . basename($sourceUrl);
            $this->downloadFile($sourceUrl, $sourceFile);

            // 解压源码
            $sourceDir = $tempDir . '/php-' . $version;
            mkdir($sourceDir);
            $this->extractFile($sourceFile, $sourceDir);

            // 查找源码目录
            $phpSourceDir = $this->findPhpSourceDir($sourceDir);
            if (!$phpSourceDir) {
                throw new \Exception("无法找到PHP源码目录");
            }

            // 配置编译选项
            $configureOptions = $this->getConfigureOptions($version, $options);

            // 编译安装PHP
            $this->compileAndInstall($phpSourceDir, $version, $configureOptions);

            // 配置PHP
            $this->configurePhp($version);

            // 清理临时目录
            $this->removeDirectory($tempDir);

            return true;
        } catch (\Exception $e) {
            // 清理临时目录
            $this->removeDirectory($tempDir);

            // 如果安装失败，则删除已安装的文件
            $versionDir = $this->versionsDir . '/' . $version;
            if (is_dir($versionDir)) {
                $this->removeDirectory($versionDir);
            }

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($version, array $options = [])
    {
        // 检查版本是否已安装
        if (!$this->isInstalled($version)) {
            throw new \Exception("PHP版本 {$version} 未安装");
        }

        // 删除版本目录
        $versionDir = $this->versionsDir . '/' . $version;
        $this->removeDirectory($versionDir);

        return true;
    }

    /**
     * 获取PHP源码URL
     *
     * @param string $version PHP版本
     * @param string $mirror 镜像名称，如果为null则使用默认镜像
     * @return string
     */
    protected function getSourceUrl($version, $mirror = null)
    {
        // 获取镜像配置
        $mirrorConfig = new MirrorConfig();
        $mirrorUrl = $mirrorConfig->getPhpMirror($mirror);

        // 构建源码URL
        return "{$mirrorUrl}/php-{$version}.tar.gz";
    }

    /**
     * 查找PHP源码目录
     *
     * @param string $baseDir 基础目录
     * @return string|false
     */
    protected function findPhpSourceDir($baseDir)
    {
        // 查找php-x.x.x目录
        $dirs = glob($baseDir . '/php-*', GLOB_ONLYDIR);
        if (!empty($dirs)) {
            return $dirs[0];
        }

        // 如果没有找到，则检查是否直接解压到了基础目录
        if (file_exists($baseDir . '/configure') && file_exists($baseDir . '/LICENSE')) {
            return $baseDir;
        }

        return false;
    }

    /**
     * 获取配置选项
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return array
     */
    protected function getConfigureOptions($version, array $options = [])
    {
        // 安装目录
        $prefix = $this->versionsDir . '/' . $version;

        // 基本配置选项
        $configureOptions = [
            "--prefix={$prefix}",
            '--enable-bcmath',
            '--enable-calendar',
            '--enable-dba',
            '--enable-exif',
            '--enable-ftp',
            '--enable-mbstring',
            '--with-mysqli',
            '--with-pdo-mysql',
            '--with-pdo-sqlite',
            '--with-openssl',
            '--enable-sockets',
            '--enable-soap',
            '--enable-zip',
            '--with-zlib',
            '--with-curl',
            '--enable-opcache',
        ];

        // 根据PHP版本添加特定的配置选项
        list($major, $minor, $patch) = explode('.', $version);
        $majorMinor = (int)$major . '.' . (int)$minor;

        // PHP 7.1 - PHP 7.3
        if (version_compare($majorMinor, '7.1', '>=') && version_compare($majorMinor, '7.4', '<')) {
            $configureOptions = array_merge($configureOptions, [
                '--with-gd',
                '--with-jpeg-dir',
                '--with-png-dir',
                '--with-webp-dir',
                '--with-freetype-dir',
                '--with-xpm-dir',
            ]);
        }
        // PHP 7.4
        elseif (version_compare($majorMinor, '7.4', '>=') && version_compare($majorMinor, '8.0', '<')) {
            $configureOptions = array_merge($configureOptions, [
                '--enable-gd',
                '--with-jpeg',
                '--with-webp',
                '--with-freetype',
                '--with-xpm',
            ]);
        }
        // PHP 8.0+
        elseif (version_compare($majorMinor, '8.0', '>=')) {
            $configureOptions = array_merge($configureOptions, [
                '--enable-gd',
                '--with-jpeg',
                '--with-webp',
                '--with-freetype',
                '--with-xpm',
                '--with-avif',
            ]);

            // PHP 8.1+
            if (version_compare($majorMinor, '8.1', '>=')) {
                $configureOptions[] = '--with-ffi';
            }

            // PHP 8.2+
            if (version_compare($majorMinor, '8.2', '>=')) {
                $configureOptions[] = '--enable-jit';
            }
        }

        // 添加用户自定义配置选项
        if (isset($options['configure_options']) && is_array($options['configure_options'])) {
            $configureOptions = array_merge($configureOptions, $options['configure_options']);
        }

        return $configureOptions;
    }

    /**
     * 编译安装PHP
     *
     * @param string $sourceDir 源码目录
     * @param string $version PHP版本
     * @param array $configureOptions 配置选项
     * @return bool
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

        exec('make -j4 2>&1', $output, $returnCode);

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

        // 返回原目录
        chdir($currentDir);

        return true;
    }

    /**
     * 配置PHP
     *
     * @param string $version PHP版本
     * @return bool
     */
    protected function configurePhp($version)
    {
        // 版本目录
        $versionDir = $this->versionsDir . '/' . $version;

        // 创建配置目录
        $configDir = $versionDir . '/etc';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        // 创建扩展配置目录
        $confDir = $configDir . '/conf.d';
        if (!is_dir($confDir)) {
            mkdir($confDir, 0755, true);
        }

        // 复制php.ini-development到php.ini
        $phpIniDev = $versionDir . '/lib/php.ini-development';
        $phpIni = $configDir . '/php.ini';

        if (file_exists($phpIniDev)) {
            copy($phpIniDev, $phpIni);
        } else {
            // 如果没有找到php.ini-development，则创建一个空的php.ini
            file_put_contents($phpIni, "; PHP Configuration File\n");
        }

        // 修改php.ini
        $iniContent = file_get_contents($phpIni);

        // 设置扩展目录
        $extensionDir = $versionDir . '/lib/php/extensions';
        $iniContent = preg_replace('/;?\s*extension_dir\s*=.*/', "extension_dir = \"{$extensionDir}\"", $iniContent);

        // 设置include_path
        $includePath = $versionDir . '/lib/php';
        $iniContent = preg_replace('/;?\s*include_path\s*=.*/', "include_path = \".:{$includePath}\"", $iniContent);

        // 设置date.timezone
        $iniContent = preg_replace('/;?\s*date\.timezone\s*=.*/', "date.timezone = \"UTC\"", $iniContent);

        // 设置扩展配置目录
        $iniContent .= "\n; Scan this dir for additional .ini files\n";
        $iniContent .= "scan_dir = \"{$confDir}\"\n";

        // 保存修改后的php.ini
        file_put_contents($phpIni, $iniContent);

        return true;
    }
}
