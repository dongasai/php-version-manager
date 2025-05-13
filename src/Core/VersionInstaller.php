<?php

namespace VersionManager\Core;

use Exception;
use VersionManager\Core\Cache\CacheManager;
use VersionManager\Core\Download\DownloadManager;
use VersionManager\Core\Security\SignatureVerifier;
use VersionManager\Core\Security\PermissionManager;
use VersionManager\Core\Security\SecurityUpdater;
use VersionManager\Core\Version\GenericVersionDriver;
use VersionManager\Core\Version\VersionDriverFactory;

/**
 * PHP版本安装类
 *
 * 负责安装和配置PHP版本
 */
class VersionInstaller
{
    /**
     * 版本检测器
     *
     * @var VersionDetector
     */
    private $detector;

    /**
     * 支持的版本管理器
     *
     * @var SupportedVersions
     */
    private $supportedVersions;

    /**
     * 缓存管理器
     *
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * 下载管理器
     *
     * @var DownloadManager
     */
    private $downloadManager;

    /**
     * 签名验证器
     *
     * @var SignatureVerifier
     */
    private $signatureVerifier;

    /**
     * 权限管理器
     *
     * @var PermissionManager
     */
    private $permissionManager;

    /**
     * 安全更新器
     *
     * @var SecurityUpdater
     */
    private $securityUpdater;

    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmDir;

    /**
     * 版本目录
     *
     * @var string
     */
    private $versionsDir;

    /**
     * 临时目录
     *
     * @var string
     */
    private $tempDir;

    /**
     * 是否使用缓存
     *
     * @var bool
     */
    private $useCache = true;

    /**
     * 是否使用多线程下载
     *
     * @var bool
     */
    private $useMultiThread = true;

    /**
     * 是否验证签名
     *
     * @var bool
     */
    private $verifySignature = true;

    /**
     * 版本驱动类列表
     *
     * @var array
     */
    private $versionDrivers = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->detector = new VersionDetector();
        $this->supportedVersions = new SupportedVersions();
        $this->cacheManager = new CacheManager();
        $this->downloadManager = new DownloadManager();
        $this->signatureVerifier = new SignatureVerifier();
        $this->permissionManager = new PermissionManager();
        $this->securityUpdater = new SecurityUpdater();
        $this->pvmDir = getenv('HOME') . '/.pvm';
        $this->versionsDir = $this->pvmDir . '/versions';
        $this->tempDir = $this->pvmDir . '/tmp';

        // 初始化版本驱动类
        $this->initVersionDrivers();

        // 确保目录存在
        $this->ensureDirectoriesExist();
    }

    /**
     * 确保必要的目录存在
     */
    private function ensureDirectoriesExist()
    {
        $dirs = [$this->pvmDir, $this->versionsDir, $this->tempDir];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // 设置安全的目录权限
            $this->permissionManager->setSecureDirPermission($dir);
        }

        // 创建配置目录
        $configDir = $this->pvmDir . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0700, true);
        }

        // 设置配置目录的权限
        $this->permissionManager->setSecureDirPermission($configDir, 0700);
    }

    /**
     * 安装PHP版本
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws Exception 安装失败时抛出异常
     */
    public function install($version, array $options = [])
    {
        // 检查版本是否已安装
        if ($this->isVersionInstalled($version)) {
            throw new Exception("PHP版本 {$version} 已安装");
        }

        // 设置下载选项
        if (isset($options['use_cache'])) {
            $this->setUseCache($options['use_cache']);
        }

        if (isset($options['use_multi_thread'])) {
            $this->setUseMultiThread($options['use_multi_thread']);
        }

        if (isset($options['thread_count'])) {
            $this->setThreadCount($options['thread_count']);
        }

        // 检查版本兼容性
        $supportLevel = $this->supportedVersions->getSupportLevel($version);
        if ($supportLevel === SupportedVersions::SUPPORT_NONE) {
            throw new Exception("PHP版本 {$version} 与当前系统不兼容");
        }

        // 显示警告信息
        if ($supportLevel === SupportedVersions::SUPPORT_PARTIAL) {
            echo "\033[33m警告: PHP版本 {$version} 在当前系统上只有部分支持\033[0m\n";

            // 显示已知问题
            $knownIssues = $this->supportedVersions->getKnownIssues($version);
            if (!empty($knownIssues)) {
                echo "\033[33m已知问题:\033[0m\n";
                foreach ($knownIssues as $issue) {
                    echo "  - {$issue}\n";
                }
            }

            // 如果没有设置yes选项，则询问用户是否继续
            if (!isset($options['yes']) || !$options['yes']) {
                echo "\033[33m是否继续安装? (y/n) \033[0m";
                $answer = trim(fgets(STDIN));
                if (strtolower($answer) !== 'y') {
                    throw new Exception("用户取消安装");
                }
            } else {
                echo "\033[33m自动确认安装\033[0m\n";
            }
        }

        // 检查版本信息缓存
        if ($this->useCache) {
            $versionInfo = $this->cacheManager->getVersionCache($version);
            if ($versionInfo) {
                echo "\033[32m使用缓存的版本信息: {$version}\033[0m\n";
            }
        }

        // 检查安全更新
        $securityUpdate = $this->securityUpdater->checkSecurityUpdate($version);
        if ($securityUpdate) {
            echo "\033[33m警告: PHP版本 {$version} 有安全更新可用\033[0m\n";
            echo "\033[33m最新版本: {$securityUpdate['latest_version']}\033[0m\n";

            if (!empty($securityUpdate['security_fixes'])) {
                echo "\033[33m安全修复:\033[0m\n";
                foreach ($securityUpdate['security_fixes'] as $fix) {
                    echo "  - {$fix}\n";
                }
            }

            // 如果没有设置yes选项，则询问用户是否安装最新版本
            if (!isset($options['yes']) || !$options['yes']) {
                echo "\033[33m是否安装最新版本? (y/n) \033[0m";
                $answer = trim(fgets(STDIN));
                if (strtolower($answer) === 'y') {
                    return $this->install($securityUpdate['latest_version'], $options);
                }
            } else {
                echo "\033[33m自动确认安装最新版本 {$securityUpdate['latest_version']}\033[0m\n";
                return $this->install($securityUpdate['latest_version'], $options);
            }
        } elseif ($supportLevel === SupportedVersions::SUPPORT_UNTESTED) {
            echo "\033[33m警告: PHP版本 {$version} 在当前系统上尚未经过测试\033[0m\n";

            // 如果没有设置yes选项，则询问用户是否继续
            if (!isset($options['yes']) || !$options['yes']) {
                echo "\033[33m是否继续安装? (y/n) \033[0m";
                $answer = trim(fgets(STDIN));
                if (strtolower($answer) !== 'y') {
                    throw new Exception("用户取消安装");
                }
            } else {
                echo "\033[33m自动确认安装\033[0m\n";
            }
        }

        // 检查依赖
        $missingDependencies = $this->detector->checkDependencies($version);
        if (!empty($missingDependencies)) {
            // 安装依赖
            $this->installDependencies($missingDependencies);
        }

        // 根据选项决定安装方式
        if (isset($options['from_source']) && $options['from_source']) {
            return $this->installFromSource($version, $options);
        } else {
            try {
                return $this->installFromBinary($version, $options);
            } catch (\Exception $e) {
                // 如果二进制包安装失败，尝试从源码安装
                echo "\033[33m二进制包安装失败，尝试从源码安装...\033[0m\n";
                return $this->installFromSource($version, $options);
            }
        }
    }

    /**
     * 检查版本是否已安装
     *
     * @param string $version PHP版本
     * @return bool 是否已安装
     */
    public function isVersionInstalled($version)
    {
        $versionDir = $this->versionsDir . '/' . $version;
        return is_dir($versionDir) && file_exists($versionDir . '/bin/php');
    }

    /**
     * 安装依赖
     *
     * @param array $dependencies 依赖列表
     * @return bool 是否安装成功
     * @throws Exception 安装失败时抛出异常
     */
    private function installDependencies(array $dependencies)
    {
        $packageManager = $this->detectPackageManager();

        if (!$packageManager) {
            throw new Exception("无法检测包管理器");
        }

        echo "安装依赖...\n";

        // 检查是否在Docker容器中
        $inDocker = file_exists('/.dockerenv');

        // 尝试不同的权限提升方式
        $sudoCommands = [
            'sudo ',           // 标准sudo
            'su -c "',        // 使用su切换到root
            ''                // 不使用权限提升
        ];

        // 构建基本命令
        $baseCommand = '';
        switch ($packageManager) {
            case 'apt':
                $baseCommand = 'apt-get update && apt-get install -y ' . implode(' ', $dependencies);
                break;
            case 'yum':
                $baseCommand = 'yum install -y ' . implode(' ', $dependencies);
                break;
            case 'dnf':
                $baseCommand = 'dnf install -y ' . implode(' ', $dependencies);
                break;
            case 'apk':
                $baseCommand = 'apk add ' . implode(' ', $dependencies);
                break;
            default:
                throw new Exception("不支持的包管理器: {$packageManager}");
        }

        // 尝试不同的权限提升方式
        $success = false;
        $lastError = '';

        foreach ($sudoCommands as $sudoCmd) {
            $command = $sudoCmd . $baseCommand;
            // 如果是su命令，需要添加结束引号
            if ($sudoCmd === 'su -c "') {
                $command .= '"';
            }

            echo "\033[33m尝试执行: {$command}\033[0m\n";

            try {
                $result = $this->executeCommand($command);
                $success = true;
                echo "\n\033[32m依赖安装成功\033[0m\n";
                break;
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                // 如果错误为空，说明命令执行成功但没有输出错误信息
                if (trim($lastError) === '') {
                    $success = true;
                    echo "\n\033[32m依赖已安装或不需要安装\033[0m\n";
                    break;
                }
                echo "\033[33m执行失败: {$lastError}\033[0m\n";
                echo "\033[33m尝试下一种权限提升方式...\033[0m\n";
                continue;
            }
        }

        if (!$success) {
            // 如果所有方式都失败，尝试使用临时脚本
            echo "\033[33m所有直接执行方式均失败，尝试使用临时脚本...\033[0m\n";

            // 创建临时脚本
            $scriptPath = $this->tempDir . '/install_deps_' . time() . '.sh';
            $scriptContent = "#!/bin/bash\n" . $baseCommand . "\n";
            file_put_contents($scriptPath, $scriptContent);
            chmod($scriptPath, 0755);

            try {
                // 尝试使用sudo执行脚本
                $command = "sudo {$scriptPath}";
                echo "\033[33m尝试执行: {$command}\033[0m\n";
                $result = $this->executeCommand($command);
                $success = true;
                echo "\n\033[32m依赖安装成功\033[0m\n";
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                // 如果错误为空，说明命令执行成功但没有输出错误信息
                if (trim($lastError) === '') {
                    $success = true;
                    echo "\n\033[32m依赖已安装或不需要安装\033[0m\n";
                } else {
                    echo "\033[33m脚本执行失败: {$lastError}\033[0m\n";

                    // 尝试使用su执行脚本
                    try {
                        $command = "su -c \"{$scriptPath}\"";
                        echo "\033[33m尝试执行: {$command}\033[0m\n";
                        $result = $this->executeCommand($command);
                        $success = true;
                        echo "\n\033[32m依赖安装成功\033[0m\n";
                    } catch (Exception $e) {
                        $lastError = $e->getMessage();
                        // 如果错误为空，说明命令执行成功但没有输出错误信息
                        if (trim($lastError) === '') {
                            $success = true;
                            echo "\n\033[32m依赖已安装或不需要安装\033[0m\n";
                        } else {
                            echo "\033[33m脚本执行失败: {$lastError}\033[0m\n";
                        }
                    }
                }
            }

            // 清理临时脚本
            if (file_exists($scriptPath)) {
                unlink($scriptPath);
            }
        }

        if (!$success) {
            throw new Exception("依赖安装失败: " . $lastError);
        }

        return true;
    }

    /**
     * 执行命令并实时显示输出
     *
     * @param string $command 要执行的命令
     * @return bool 是否执行成功
     * @throws Exception 执行失败时抛出异常
     */
    private function executeCommand($command)
    {
        // 使用proc_open实现实时输出
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            // 关闭stdin
            fclose($pipes[0]);

            // 设置非阻塞模式
            stream_set_blocking($pipes[1], 0);
            stream_set_blocking($pipes[2], 0);

            $output = '';
            $error = '';

            // 循环读取输出，直到进程结束
            while (true) {
                $status = proc_get_status($process);

                // 读取stdout
                $stdout = fread($pipes[1], 4096);
                if ($stdout) {
                    echo $stdout;
                    $output .= $stdout;
                }

                // 读取stderr
                $stderr = fread($pipes[2], 4096);
                if ($stderr) {
                    echo $stderr;
                    $error .= $stderr;
                }

                // 如果进程已结束，则退出循环
                if (!$status['running']) {
                    // 读取剩余输出
                    $stdout = stream_get_contents($pipes[1]);
                    if ($stdout) {
                        echo $stdout;
                        $output .= $stdout;
                    }

                    $stderr = stream_get_contents($pipes[2]);
                    if ($stderr) {
                        echo $stderr;
                        $error .= $stderr;
                    }

                    break;
                }

                // 避免 CPU 占用过高
                usleep(100000); // 100ms
            }

            // 关闭管道
            fclose($pipes[1]);
            fclose($pipes[2]);

            // 关闭进程
            $returnCode = proc_close($process);

            // 如果返回非零状态码且有错误输出，则抛出异常
            if ($returnCode !== 0 && !empty(trim($error))) {
                throw new Exception($error);
            }

            // 如果返回非零状态码但没有错误输出，可能是因为没有需要安装的包
            if ($returnCode !== 0) {
                // 检查输出中是否包含“已经是最新版本”或“已安装”等信息
                if (strpos($output, 'already the newest version') !== false ||
                    strpos($output, 'already installed') !== false ||
                    strpos($output, '0 newly installed') !== false ||
                    strpos($output, '0 upgraded') !== false) {
                    // 这是正常情况，依赖已经安装
                    return true;
                }

                // 如果没有匹配到上述模式，则抛出异常
                throw new Exception($error);
            }

            return true;
        } else {
            throw new Exception("无法启动进程");
        }
    }

    /**
     * 检测包管理器
     *
     * @return string|null 包管理器名称，如果未检测到则返回null
     */
    private function detectPackageManager()
    {
        $packageManagers = [
            'apt' => 'apt-get',
            'yum' => 'yum',
            'dnf' => 'dnf',
            'apk' => 'apk'
        ];

        foreach ($packageManagers as $name => $command) {
            $output = [];
            exec("which {$command} 2>/dev/null", $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * 从源码安装PHP
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws Exception 安装失败时抛出异常
     */
    private function installFromSource($version, array $options = [])
    {
        // 获取适用的版本驱动类
        $driver = $this->getVersionDriver($version);

        // 如果驱动类不是自身，则使用驱动类安装
        if ($driver !== $this) {
            try {
                return $driver->install($version, $options);
            } catch (\Exception $e) {
                throw new Exception("安装PHP失败: " . $e->getMessage());
            }
        }
        $versionDir = $this->versionsDir . '/' . $version;
        $sourceDir = $this->tempDir . '/php-' . $version;
        $buildDir = $this->tempDir . '/php-build-' . $version;

        // 创建目录
        if (!is_dir($versionDir)) {
            mkdir($versionDir, 0755, true);
        }

        if (!is_dir($sourceDir)) {
            mkdir($sourceDir, 0755, true);
        }

        if (!is_dir($buildDir)) {
            mkdir($buildDir, 0755, true);
        }

        // 下载源码
        $sourceUrl = $this->getSourceUrl($version);
        $tarFile = $this->tempDir . '/php-' . $version . '.tar.gz';

        echo "下载PHP {$version} 源码...\n";
        $this->downloadFile($sourceUrl, $tarFile, [
            'verify_type' => 'php',
            'verify_version' => $version
        ]);

        // 解压源码
        echo "解压源码...\n";
        $command = "tar -xzf {$tarFile} -C {$this->tempDir}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("源码解压失败: " . implode("\n", $output));
        }

        // 配置编译选项
        echo "配置编译选项...\n";
        $configureOptions = [
            "--prefix={$versionDir}",
            "--with-config-file-path={$versionDir}/etc",
            "--with-config-file-scan-dir={$versionDir}/etc/conf.d",
            "--enable-opcache",
            "--enable-fpm",
            "--enable-mbstring",
            "--enable-mysqlnd",
            "--with-mysqli=mysqlnd",
            "--with-pdo-mysql=mysqlnd",
            "--with-curl",
            "--with-openssl",
            "--with-zlib"
        ];

        // 添加自定义编译选项
        if (isset($options['configure_options']) && is_array($options['configure_options'])) {
            $configureOptions = array_merge($configureOptions, $options['configure_options']);
        }

        // 执行配置
        $configureCommand = "cd {$sourceDir} && ./configure " . implode(' ', $configureOptions);
        echo "执行配置: {$configureCommand}\n";
        $output = [];
        $returnCode = 0;
        exec($configureCommand, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("配置失败: " . implode("\n", $output));
        }

        // 编译
        echo "编译PHP {$version}...\n";
        $cpuCores = $this->detectCPUCores();
        $makeCommand = "cd {$sourceDir} && make -j{$cpuCores}";
        $output = [];
        $returnCode = 0;
        exec($makeCommand, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("编译失败: " . implode("\n", $output));
        }

        // 安装
        echo "安装PHP {$version}...\n";
        $installCommand = "cd {$sourceDir} && make install";
        $output = [];
        $returnCode = 0;
        exec($installCommand, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("安装失败: " . implode("\n", $output));
        }

        // 创建配置目录
        $configDir = $versionDir . '/etc';
        $configScanDir = $configDir . '/conf.d';

        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        if (!is_dir($configScanDir)) {
            mkdir($configScanDir, 0755, true);
        }

        // 复制默认配置文件
        $phpIniDevelopment = $sourceDir . '/php.ini-development';
        $phpIniProduction = $sourceDir . '/php.ini-production';

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

        // 清理临时文件
        if (!isset($options['keep_source']) || !$options['keep_source']) {
            $this->cleanupTempFiles($version);
        }

        // 缓存版本信息
        if ($this->useCache) {
            $versionInfo = [
                'version' => $version,
                'install_time' => time(),
                'install_type' => 'source',
                'configure_options' => $configureOptions,
                'php_info' => $this->getPhpInfo($version),
            ];
            $this->cacheManager->setVersionCache($version, $versionInfo);
            echo "\033[32m版本信息已缓存: {$version}\033[0m\n";
        }

        echo "PHP {$version} 安装完成\n";
        return true;
    }

    /**
     * 从预编译二进制包安装PHP
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws Exception 安装失败时抛出异常
     */
    private function installFromBinary($version, array $options = [])
    {
        $versionDir = $this->versionsDir . '/' . $version;

        // 创建目录
        if (!is_dir($versionDir)) {
            mkdir($versionDir, 0755, true);
        }

        // 获取二进制包URL
        $binaryUrl = $this->getBinaryUrl($version);
        if (!$binaryUrl) {
            throw new Exception("无法获取PHP {$version} 的二进制包URL");
        }

        // 下载二进制包
        $tarFile = $this->tempDir . '/php-' . $version . '-binary.tar.gz';
        echo "下载PHP {$version} 二进制包...\n";
        $this->downloadFile($binaryUrl, $tarFile, [
            'verify_type' => 'php',
            'verify_version' => $version
        ]);

        // 解压二进制包
        echo "解压二进制包...\n";
        $command = "tar -xzf {$tarFile} -C {$versionDir} --strip-components=1";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("二进制包解压失败: " . implode("\n", $output));
        }

        // 创建配置目录
        $configDir = $versionDir . '/etc';
        $configScanDir = $configDir . '/conf.d';

        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        if (!is_dir($configScanDir)) {
            mkdir($configScanDir, 0755, true);
        }

        // 清理临时文件
        if (!isset($options['keep_binary']) || !$options['keep_binary']) {
            unlink($tarFile);
        }

        // 缓存版本信息
        if ($this->useCache) {
            $versionInfo = [
                'version' => $version,
                'install_time' => time(),
                'install_type' => 'binary',
                'binary_url' => $binaryUrl,
                'php_info' => $this->getPhpInfo($version),
            ];
            $this->cacheManager->setVersionCache($version, $versionInfo);
            echo "\033[32m版本信息已缓存: {$version}\033[0m\n";
        }

        echo "PHP {$version} 安装完成\n";
        return true;
    }

    /**
     * 获取源码URL
     *
     * @param string $version PHP版本
     * @return string 源码URL
     */
    private function getSourceUrl($version)
    {
        $majorVersion = substr($version, 0, 1);
        $minorVersion = substr($version, 0, 3);

        return "https://www.php.net/distributions/php-{$version}.tar.gz";
    }

    /**
     * 获取二进制包URL
     *
     * @param string $version PHP版本
     * @return string|null 二进制包URL，如果不可用则返回null
     */
    private function getBinaryUrl($version)
    {
        // 检测系统架构
        $arch = php_uname('m');

        // 目前只支持x86_64和aarch64架构的预编译包
        if ($arch !== 'x86_64' && $arch !== 'aarch64') {
            return null;
        }

        // 这里应该根据版本和架构返回实际的二进制包URL
        // 由于没有官方的预编译包，这里返回null，表示不可用
        return null;
    }

    /**
     * 设置是否使用缓存
     *
     * @param bool $useCache 是否使用缓存
     * @return $this
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
        $this->downloadManager->setUseCache($useCache);
        return $this;
    }

    /**
     * 设置是否使用多线程下载
     *
     * @param bool $useMultiThread 是否使用多线程下载
     * @return $this
     */
    public function setUseMultiThread($useMultiThread)
    {
        $this->useMultiThread = $useMultiThread;
        $this->downloadManager->setUseMultiThread($useMultiThread);
        return $this;
    }

    /**
     * 设置是否验证签名
     *
     * @param bool $verifySignature 是否验证签名
     * @return $this
     */
    public function setVerifySignature($verifySignature)
    {
        $this->verifySignature = $verifySignature;
        $this->downloadManager->setVerifySignature($verifySignature);
        $this->signatureVerifier->setEnabled($verifySignature);
        return $this;
    }

    /**
     * 设置线程数
     *
     * @param int $threadCount 线程数
     * @return $this
     */
    public function setThreadCount($threadCount)
    {
        $this->downloadManager->setThreadCount($threadCount);
        return $this;
    }

    /**
     * 下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @param array $options 下载选项
     * @return bool 是否下载成功
     * @throws Exception 下载失败时抛出异常
     */
    private function downloadFile($url, $destination, array $options = [])
    {
        try {
            // 使用下载管理器下载
            return $this->downloadManager->download($url, $destination, $options);
        } catch (\Exception $e) {
            throw new Exception("文件下载失败: " . $e->getMessage());
        }
    }

    /**
     * 检测CPU核心数
     *
     * @return int CPU核心数
     */
    private function detectCPUCores()
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
     * 获取PHP信息
     *
     * @param string $version PHP版本
     * @return array PHP信息
     */
    private function getPhpInfo($version)
    {
        $versionDir = $this->versionsDir . '/' . $version;
        $phpBin = $versionDir . '/bin/php';

        if (!file_exists($phpBin)) {
            return [];
        }

        // 获取PHP版本
        $output = [];
        exec($phpBin . ' -v', $output);
        $phpVersion = !empty($output) ? $output[0] : '';

        // 获取PHP配置信息
        $output = [];
        exec($phpBin . ' -i | grep "Configure Command"', $output);
        $configureCommand = !empty($output) ? str_replace('Configure Command => ', '', $output[0]) : '';

        // 获取PHP扩展信息
        $output = [];
        exec($phpBin . ' -m', $output);
        $extensions = array_filter($output, function($line) {
            return !empty($line) && $line !== '[PHP Modules]' && $line !== '[Zend Modules]';
        });

        return [
            'php_version' => $phpVersion,
            'configure_command' => $configureCommand,
            'extensions' => $extensions,
        ];
    }

    /**
     * 清理临时文件
     *
     * @param string $version PHP版本
     * @return bool 是否清理成功
     */
    private function cleanupTempFiles($version)
    {
        $tarFile = $this->tempDir . '/php-' . $version . '.tar.gz';
        $sourceDir = $this->tempDir . '/php-' . $version;
        $buildDir = $this->tempDir . '/php-build-' . $version;

        if (file_exists($tarFile)) {
            unlink($tarFile);
        }

        if (is_dir($sourceDir)) {
            $this->removeDirectory($sourceDir);
        }

        if (is_dir($buildDir)) {
            $this->removeDirectory($buildDir);
        }

        return true;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     * @return bool 是否删除成功
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

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
    }

    /**
     * 初始化版本驱动类
     */
    private function initVersionDrivers()
    {
        // 使用版本驱动工厂初始化驱动
        $this->versionDrivers['generic'] = VersionDriverFactory::getDriver();
    }

    /**
     * 获取版本驱动类
     *
     * @param string $version PHP版本
     * @return mixed 版本驱动类
     */
    private function getVersionDriver($version)
    {
        // 使用版本驱动工厂获取驱动
        return VersionDriverFactory::getDriver($version);
    }
}
