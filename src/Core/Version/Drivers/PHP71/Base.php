<?php

namespace VersionManager\Core\Version\Drivers\PHP71;



use VersionManager\Core\Tags\PhpTag;
use VersionManager\Core\Version\AbstractVersionDriver;

/**
 * PHP 7.1 基础版本安装驱动类
 */
class Base extends AbstractVersionDriver
{
    /**
     * 驱动名称
     *
     * @var string
     */
    protected $name = 'php71';

    /**
     * 驱动描述
     *
     * @var string
     */
    protected $description = 'PHP 7.1 版本安装驱动';




    /**
     * {@inheritdoc}
     */
    public function isSupported($version)
    {
        // 只支持PHP 7.1.x版本
        return preg_match('/^7\.1\.\d+$/', $version);
    }

    /**
     * {@inheritdoc}
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
            "--enable-bcmath",
            "--enable-calendar",
            "--enable-dba",
            "--enable-exif",
            "--enable-ftp",
            "--enable-gd-native-ttf",
            "--enable-intl",
            "--enable-mbstring",
            "--enable-pcntl",
            "--enable-shmop",
            "--enable-soap",
            "--enable-sockets",
            "--enable-sysvmsg",
            "--enable-sysvsem",
            "--enable-sysvshm",
            "--enable-wddx",
            "--enable-zip",
            "--with-curl",
            "--with-gd",
            "--with-gettext",
            "--with-iconv",
            "--with-mcrypt",
            "--with-mhash",
            "--with-mysql-sock=/var/run/mysqld/mysqld.sock",
            "--with-mysqli=mysqlnd",
            "--with-openssl=/usr",
            "--with-openssl-dir=/usr",
            "--with-pdo-mysql=mysqlnd",
            "--with-pdo-sqlite",
            "--with-readline",
            "--with-sqlite3",
            "--with-xmlrpc",
            "--with-xsl",
            "--with-zlib",
        ];

        // 如果指定了配置选项，则使用指定的选项
        if (isset($options['configure_options']) && is_array($options['configure_options'])) {
            $configureOptions = array_merge($configureOptions, $options['configure_options']);
        }

        return $configureOptions;
    }

    /**
     * 获取源码URL
     *
     * @param string $version PHP版本
     * @param string $mirror 镜像名称，如果为null则使用默认镜像
     * @return string
     */
    protected function getSourceUrl($version, $mirror = null)
    {
        // 如果是PHP 7.1.0，则使用特定的URL
        if ($version === '7.1.0') {
            return "https://www.php.net/distributions/php-7.1.0.tar.gz";
        }

        // 构建源码URL
        return "https://www.php.net/distributions/php-{$version}.tar.gz";
    }



    public function getTags(): array
    {
        return [
            PhpTag::PHP71
        ];
    }

    /**
     * 下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return bool
     * @throws \Exception
     */
    protected function downloadFile($url, $destination)
    {
        // 使用curl的进度显示选项
        $command = "curl -L --progress-bar -o {$destination} {$url}";

        // 使用passthru而不是exec，这样可以实时显示输出
        echo "正在下载 {$url}...\n";
        passthru($command, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("下载文件失败，返回代码: {$returnCode}");
        }

        echo "下载完成: " . basename($destination) . "\n";
        return true;
    }

    /**
     * 解压文件
     *
     * @param string $file 压缩文件路径
     * @param string $destination 目标目录
     * @return bool
     * @throws \Exception
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

        echo "正在解压 " . basename($file) . "...\n";
        passthru($command, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("解压文件失败，返回代码: {$returnCode}");
        }

        echo "解压完成\n";
        return true;
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
     * 编译安装PHP
     *
     * @param string $sourceDir 源码目录
     * @param string $version PHP版本
     * @param array $configureOptions 配置选项
     * @return bool
     * @throws \Exception
     */
    protected function compileAndInstall($sourceDir, $version, array $configureOptions)
    {
        // 当前目录
        $currentDir = getcwd();

        // 进入源码目录
        chdir($sourceDir);

        // 检查OpenSSL库是否存在
        echo "检查OpenSSL库...\n";
        $opensslLibs = [
            '/usr/lib/libssl.so',
            '/usr/lib/x86_64-linux-gnu/libssl.so',
            '/usr/lib64/libssl.so',
            '/usr/local/lib/libssl.so'
        ];

        $opensslFound = false;
        foreach ($opensslLibs as $lib) {
            if (file_exists($lib)) {
                echo "找到OpenSSL库: {$lib}\n";
                $opensslFound = true;
                break;
            }
        }

        if (!$opensslFound) {
            echo "警告: 未找到OpenSSL库，可能会导致配置失败\n";
            echo "尝试安装libssl-dev或openssl-devel包\n";
        }

        // 检查OpenSSL头文件是否存在
        $opensslHeaders = [
            '/usr/include/openssl/ssl.h',
            '/usr/local/include/openssl/ssl.h'
        ];

        $opensslHeaderFound = false;
        foreach ($opensslHeaders as $header) {
            if (file_exists($header)) {
                echo "找到OpenSSL头文件: {$header}\n";
                $opensslHeaderFound = true;
                break;
            }
        }

        if (!$opensslHeaderFound) {
            echo "警告: 未找到OpenSSL头文件，可能会导致配置失败\n";
        }

        // 配置
        $configureCommand = './configure ' . implode(' ', $configureOptions);

        echo "配置PHP {$version}...\n";
        echo "执行: {$configureCommand}\n";
        passthru($configureCommand, $returnCode);

        if ($returnCode !== 0) {
            // 尝试查找错误信息
            if (file_exists('config.log')) {
                echo "配置失败，查看config.log中的错误信息...\n";
                $configLog = file_get_contents('config.log');

                // 查找OpenSSL相关错误
                if (strpos($configLog, 'Cannot find OpenSSL') !== false) {
                    echo "错误: 找不到OpenSSL库\n";
                    echo "请安装libssl-dev (Debian/Ubuntu) 或 openssl-devel (CentOS/RHEL)\n";
                }

                // 输出最后100行日志
                $logLines = explode("\n", $configLog);
                $lastLines = array_slice($logLines, -100);
                echo "config.log最后100行:\n";
                echo implode("\n", $lastLines) . "\n";
            }

            chdir($currentDir);
            throw new \Exception("配置PHP失败，返回代码: {$returnCode}");
        }

        // 检测CPU核心数，用于并行编译
        $cpuCores = $this->detectCPUCores();
        $makeCommand = "make -j{$cpuCores}";

        // 编译
        echo "\n编译PHP {$version}...\n";
        echo "执行: {$makeCommand}\n";
        passthru($makeCommand, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("编译PHP失败，返回代码: {$returnCode}");
        }

        // 安装
        echo "\n安装PHP {$version}...\n";
        echo "执行: make install\n";
        passthru('make install', $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("安装PHP失败，返回代码: {$returnCode}");
        }

        // 返回原目录
        chdir($currentDir);

        echo "\nPHP {$version} 编译安装完成\n";
        return true;
    }

    /**
     * 检测CPU核心数
     *
     * @return int CPU核心数
     */
    protected function detectCPUCores()
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

    /**
     * 安装依赖项（旧版本方法，已废弃）
     *
     * @return bool
     * @throws \Exception
     * @deprecated 使用新的installDependencies(array $dependencies)方法
     */
    protected function installDependenciesLegacy()
    {
        // 检测操作系统类型
        $osInfo = $this->getOsInfo();

        echo "检测到操作系统: {$osInfo['type']} {$osInfo['version']}\n";
        echo "正在安装PHP 7.1所需的依赖项...\n";

        switch ($osInfo['type']) {
            case 'debian':
            case 'ubuntu':
                $command = 'apt-get update && apt-get install -y libssl-dev libcurl4-openssl-dev libxml2-dev libpng-dev libjpeg-dev libfreetype6-dev libmcrypt-dev libreadline-dev';
                break;
            case 'centos':
            case 'fedora':
            case 'rhel':
                $command = 'yum install -y openssl-devel curl-devel libxml2-devel libpng-devel libjpeg-devel freetype-devel libmcrypt-devel readline-devel';
                break;
            case 'alpine':
                $command = 'apk add --no-cache openssl-dev curl-dev libxml2-dev libpng-dev libjpeg-dev freetype-dev libmcrypt-dev readline-dev';
                break;
            default:
                throw new \Exception("不支持的操作系统类型: {$osInfo['type']}");
        }

        echo "执行: {$command}\n";
        passthru($command, $returnCode);

        if ($returnCode !== 0) {
            echo "警告: 安装依赖项失败，尝试不使用sudo...\n";
            $command = str_replace('apt-get', 'apt-get --no-install-recommends', $command);
            passthru($command, $returnCode);

            if ($returnCode !== 0) {
                echo "警告: 安装依赖项失败，将继续尝试编译PHP...\n";
                return false;
            }
        }

        echo "依赖项安装完成\n";
        return true;
    }

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

        // 安装依赖项
        try {
            // 获取PHP 7.1所需的依赖包
            $dependencies = $this->getBaseDependencies($version);
            // 添加PHP 7.1特有的依赖
            $dependencies[] = 'libmcrypt-dev'; // PHP 7.1需要mcrypt
            $dependencies[] = 'libreadline-dev'; // readline支持

            $this->installDependencies($dependencies);
        } catch (\Exception $e) {
            echo "警告: " . $e->getMessage() . "\n";
            echo "将继续尝试编译PHP，但可能会失败...\n";
        }

        // 创建临时目录
        $tempDir = sys_get_temp_dir() . '/pvm_php_' . $version . '_' . time();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            // 下载PHP源码
            $sourceUrl = $this->getSourceUrl($version);
            $sourceFile = $tempDir . '/' . basename($sourceUrl);

            echo "下载PHP {$version} 源码...\n";
            $this->downloadFile($sourceUrl, $sourceFile);

            // 解压源码
            echo "解压源码...\n";
            $sourceDir = $tempDir . '/php-' . $version;
            mkdir($sourceDir, 0755, true);
            $this->extractFile($sourceFile, $tempDir);

            // 查找源码目录
            $phpSourceDir = $this->findPhpSourceDir($tempDir);
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

}
