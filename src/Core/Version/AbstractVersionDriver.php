<?php

namespace VersionManager\Core\Version;

use VersionManager\Core\Tags\TaggableInterface;
use VersionManager\Core\System\OsDriverFactory;
use VersionManager\Core\Logger\FileLogger;

/**
 * 抽象版本安装驱动基类
 *
 * 实现一些通用功能
 */
abstract class AbstractVersionDriver implements VersionDriverInterface, TaggableInterface
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name;

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description;

    /**
     * PVM根目录
     *
     * @var string
     */
    protected $pvmRoot;

    /**
     * 版本目录
     *
     * @var string
     */
    protected $versionsDir;

    /**
     * 操作系统驱动
     *
     * @var \VersionManager\Core\System\OsDriverInterface
     */
    protected $osDriver;

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 设置默认值（如果子类没有设置）
        if (empty($this->name)) {
            $this->name = 'unknown';
        }

        if (empty($this->description)) {
            $this->description = 'Unknown driver';
        }

        $this->pvmRoot = getenv('HOME') . '/.pvm';
        $this->versionsDir = $this->pvmRoot . '/versions';

        // 确保目录存在
        if (!is_dir($this->pvmRoot)) {
            mkdir($this->pvmRoot, 0755, true);
        }

        if (!is_dir($this->versionsDir)) {
            mkdir($this->versionsDir, 0755, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        // 默认返回驱动名称作为标签
        return [$this->name];
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 默认实现，子类应该重写此方法
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled($version)
    {
        $versionDir = $this->versionsDir . '/' . $version;
        $phpBin = $versionDir . '/bin/php';

        return is_dir($versionDir) && file_exists($phpBin) && is_executable($phpBin);
    }

    /**
     * {@inheritdoc}
     */
    public function getBinaryPath($version)
    {
        return $this->versionsDir . '/' . $version . '/bin/php';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath($version)
    {
        return $this->versionsDir . '/' . $version . '/etc';
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionPath($version)
    {
        return $this->versionsDir . '/' . $version . '/lib/php/extensions';
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo($version)
    {
        $info = [
            'version' => $version,
            'installed' => $this->isInstalled($version),
            'supported' => $this->isSupported($version),
            'binary_path' => $this->getBinaryPath($version),
            'config_path' => $this->getConfigPath($version),
            'extension_path' => $this->getExtensionPath($version),
        ];

        if ($this->isInstalled($version)) {
            $phpBin = $this->getBinaryPath($version);

            // 获取PHP版本信息
            $output = [];
            $command = $phpBin . ' -v';
            FileLogger::debug("执行PHP版本查询: {$command}", 'COMMAND');
            exec($command, $output);

            if (!empty($output)) {
                $info['version_string'] = $output[0];
                FileLogger::debug("PHP版本信息: " . $output[0], 'COMMAND');
            }

            // 获取PHP编译选项
            $output = [];
            $command = $phpBin . ' -i | grep "Configure Command"';
            FileLogger::debug("执行PHP编译选项查询: {$command}", 'COMMAND');
            exec($command, $output);

            if (!empty($output)) {
                $info['configure_command'] = trim(str_replace('Configure Command =>', '', $output[0]));
                FileLogger::debug("PHP编译选项: " . $info['configure_command'], 'COMMAND');
            }

            // 获取PHP扩展
            $output = [];
            $command = $phpBin . ' -m';
            FileLogger::debug("执行PHP扩展查询: {$command}", 'COMMAND');
            exec($command, $output);

            $extensions = [];
            $inExtensions = false;

            foreach ($output as $line) {
                $line = trim($line);

                if ($line === '[PHP Modules]') {
                    $inExtensions = true;
                    continue;
                }

                if ($line === '[Zend Modules]') {
                    $inExtensions = false;
                    continue;
                }

                if ($inExtensions && !empty($line)) {
                    $extensions[] = $line;
                }
            }

            $info['extensions'] = $extensions;
        }

        return $info;
    }

    /**
     * 获取操作系统信息
     *
     * @return array [type => 类型, version => 版本, arch => 架构]
     */
    protected function getOsInfo()
    {
        $type = '';
        $version = '';
        $arch = php_uname('m');

        // 读取/etc/os-release文件
        if (file_exists('/etc/os-release')) {
            $osRelease = parse_ini_file('/etc/os-release');

            if (isset($osRelease['ID'])) {
                $type = strtolower($osRelease['ID']);
            }

            if (isset($osRelease['VERSION_ID'])) {
                $version = $osRelease['VERSION_ID'];
            }
        }

        return [
            'type' => $type,
            'version' => $version,
            'arch' => $arch,
        ];
    }

    /**
     * 创建临时目录
     *
     * @param string $prefix 目录前缀
     * @return string 临时目录路径
     */
    protected function createTempDir($prefix = 'pvm_')
    {
        $tempDir = sys_get_temp_dir() . '/' . $prefix . uniqid();

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return $tempDir;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     * @return bool
     */
    protected function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        echo "正在清理目录: " . basename($dir) . "\n";

        // 使用系统命令删除目录，这样更快
        $command = "rm -rf " . escapeshellarg($dir);
        FileLogger::info("执行删除命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        passthru($command, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("删除命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            echo "警告: 使用系统命令删除目录失败，尝试使用PHP递归删除\n";

            // 如果系统命令失败，则使用PHP递归删除
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object === '.' || $object === '..') {
                    continue;
                }

                $path = $dir . '/' . $object;

                if (is_dir($path)) {
                    $this->removeDirectory($path);
                } else {
                    unlink($path);
                }
            }

            return rmdir($dir);
        } else {
            FileLogger::info("删除命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        echo "目录清理完成\n";
        return true;
    }

    /**
     * 下载文件
     *
     * @param string|array $url 文件URL或URL数组
     * @param string $destination 目标路径
     * @return bool
     */
    protected function downloadFile($url, $destination)
    {
        // 如果传入的是数组，按优先级尝试下载
        if (is_array($url)) {
            return $this->downloadFileWithFallback($url, $destination);
        }

        $command = "curl -L -o {$destination} {$url}";
        $output = [];
        $returnCode = 0;

        // 记录命令执行
        FileLogger::info("执行下载命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        exec($command . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("下载命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("下载文件失败: " . implode("\n", $output));
        } else {
            FileLogger::info("下载命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        return true;
    }

    /**
     * 使用多个URL按优先级尝试下载
     *
     * @param array $urls URL数组
     * @param string $destination 目标路径
     * @return bool
     */
    protected function downloadFileWithFallback(array $urls, $destination)
    {
        $lastException = null;
        $attemptCount = 0;

        foreach ($urls as $url) {
            $attemptCount++;

            try {
                echo "尝试从源 {$attemptCount} 下载: " . parse_url($url, PHP_URL_HOST) . "\n";

                // 尝试下载
                $success = $this->downloadFile($url, $destination);

                if ($success) {
                    if ($attemptCount > 1) {
                        echo "下载成功！\n";
                    }
                    return true;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                echo "下载失败: " . $e->getMessage() . "\n";

                // 如果还有其他URL可以尝试，显示切换信息
                if ($attemptCount < count($urls)) {
                    echo "正在切换到下一个源...\n";
                }

                // 继续尝试下一个URL
                continue;
            }
        }

        // 所有URL都失败了
        if ($lastException) {
            throw new \Exception("所有下载源都失败了，最后一个错误: " . $lastException->getMessage());
        } else {
            throw new \Exception("所有下载源都失败了");
        }
    }

    /**
     * 解压文件
     *
     * @param string $file 压缩文件路径
     * @param string $destination 目标目录
     * @return bool
     */
    protected function extractFile($file, $destination)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'gz':
            case 'tgz':
                $command = "tar -xzf {$file} -C {$destination}";
                break;
            case 'bz2':
                $command = "tar -xjf {$file} -C {$destination}";
                break;
            case 'xz':
                $command = "tar -xJf {$file} -C {$destination}";
                break;
            case 'zip':
                $command = "unzip {$file} -d {$destination}";
                break;
            default:
                throw new \Exception("不支持的压缩格式: {$extension}");
        }

        $output = [];
        $returnCode = 0;

        // 记录命令执行
        FileLogger::info("执行解压命令: {$command}", 'COMMAND');
        $startTime = microtime(true);

        exec($command . ' 2>&1', $output, $returnCode);

        $duration = microtime(true) - $startTime;

        if ($returnCode !== 0) {
            FileLogger::error("解压命令执行失败: {$command}", 'COMMAND');
            FileLogger::error("退出码: {$returnCode}, 耗时: " . round($duration, 2) . "秒", 'COMMAND');
            if (!empty($output)) {
                FileLogger::error("命令输出: " . implode("\n", $output), 'COMMAND');
            }
            throw new \Exception("解压文件失败: " . implode("\n", $output));
        } else {
            FileLogger::info("解压命令执行成功，耗时: " . round($duration, 2) . "秒", 'COMMAND');
        }

        return true;
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
            "--with-config-file-path={$prefix}/etc",
            "--with-config-file-scan-dir={$prefix}/etc/conf.d",
        ];

        // 如果指定了配置选项，则使用指定的选项
        if (isset($options['configure_options']) && is_array($options['configure_options'])) {
            $configureOptions = array_merge($configureOptions, $options['configure_options']);
        }

        return $configureOptions;
    }

    /**
     * 获取操作系统驱动实例
     *
     * @return \VersionManager\Core\System\OsDriverInterface
     */
    protected function getOsDriver()
    {
        if ($this->osDriver === null) {
            $this->osDriver = OsDriverFactory::getInstance();
        }
        return $this->osDriver;
    }

    /**
     * 安装系统依赖
     *
     * @param array $dependencies 依赖列表
     * @return bool 是否安装成功
     * @throws \Exception 安装失败时抛出异常
     */
    protected function installDependencies(array $dependencies)
    {
        if (empty($dependencies)) {
            return true;
        }

        \VersionManager\Core\Logger\Logger::info("安装系统依赖...", "\033[33m");

        try {
            $osDriver = $this->getOsDriver();

            // 先更新包缓存
            $osDriver->updatePackageCache();

            // 安装依赖包
            $osDriver->installPackages($dependencies);

            \VersionManager\Core\Logger\Logger::success("系统依赖安装完成");
            return true;
        } catch (\Exception $e) {
            throw new \Exception("安装系统依赖失败: " . $e->getMessage());
        }
    }

    /**
     * 获取PHP编译所需的基础依赖
     *
     * @param string $version PHP版本
     * @return array 依赖包列表
     */
    protected function getBaseDependencies($version)
    {
        // 根据操作系统类型返回不同的依赖包
        $osDriver = $this->getOsDriver();
        $packageManager = $osDriver->getPackageManager();

        switch ($packageManager) {
            case 'apt':
                return [
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
                    'libxpm-dev'
                ];

            case 'yum':
            case 'dnf':
                return [
                    'gcc',
                    'gcc-c++',
                    'make',
                    'libxml2-devel',
                    'openssl-devel',
                    'sqlite-devel',
                    'zlib-devel',
                    'libcurl-devel',
                    'libpng-devel',
                    'libjpeg-devel',
                    'freetype-devel',
                    'libwebp-devel',
                    'libXpm-devel'
                ];

            case 'apk':
                return [
                    'build-base',
                    'libxml2-dev',
                    'openssl-dev',
                    'sqlite-dev',
                    'zlib-dev',
                    'curl-dev',
                    'libpng-dev',
                    'jpeg-dev',
                    'freetype-dev',
                    'libwebp-dev',
                    'libxpm-dev'
                ];

            default:
                return [];
        }
    }
}
