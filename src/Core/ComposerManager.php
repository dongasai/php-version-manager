<?php

namespace VersionManager\Core;

use VersionManager\Core\Config\MirrorConfig;

/**
 * Composer管理器类
 *
 * 用于管理不同PHP版本的Composer
 */
class ComposerManager
{
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmRoot;

    /**
     * Composer目录
     *
     * @var string
     */
    private $composerDir;

    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $versionSwitcher;

    /**
     * 镜像配置
     *
     * @var MirrorConfig
     */
    private $mirrorConfig;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->pvmRoot = getenv('HOME') . '/.pvm';
        $this->versionSwitcher = new VersionSwitcher();
        $this->mirrorConfig = new MirrorConfig();

        // 将Composer目录设置为PHP版本目录下
        $this->composerDir = $this->pvmRoot . '/versions';

        // 确保目录存在
        if (!is_dir($this->composerDir)) {
            mkdir($this->composerDir, 0755, true);
        }
    }

    /**
     * 安装Composer
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本，可以是'1'、'2'或具体版本号
     * @param array $options 安装选项
     * @return bool
     */
    public function install($phpVersion, $composerVersion = '2', array $options = [])
    {
        // 检查PHP版本是否已安装
        if (!$this->versionSwitcher->isVersionInstalled($phpVersion)) {
            throw new \Exception("PHP版本 {$phpVersion} 未安装");
        }

        // 获取PHP二进制文件路径
        $phpBin = $this->versionSwitcher->getBinaryPath($phpVersion);

        // 创建Composer目录
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);
        if (!is_dir($composerVersionDir)) {
            mkdir($composerVersionDir, 0755, true);
        }

        // 下载Composer安装程序
        $installerPath = $this->downloadComposerInstaller($options);

        // 安装Composer
        $composerPhar = $composerVersionDir . '/composer.phar';
        $command = "{$phpBin} {$installerPath} --install-dir=" . escapeshellarg($composerVersionDir) . " --filename=composer.phar";

        // 如果指定了具体版本，则添加版本参数
        if ($composerVersion !== '1' && $composerVersion !== '2') {
            $command .= " --version=" . escapeshellarg($composerVersion);
        } elseif ($composerVersion === '1') {
            $command .= " --1";
        } else {
            $command .= " --2";
        }

        // 执行安装命令
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("安装Composer失败: " . implode("\n", $output));
        }

        // 创建Composer包装脚本
        $this->createComposerWrapper($phpVersion, $composerVersion);

        // 如果设置了默认选项，则设置为默认Composer
        if (isset($options['default']) && $options['default']) {
            $this->setDefaultComposer($phpVersion, $composerVersion);
        }

        return true;
    }

    /**
     * 下载Composer安装程序
     *
     * @param array $options 下载选项
     * @return string 安装程序路径
     */
    private function downloadComposerInstaller(array $options = [])
    {
        // 获取镜像地址
        $mirror = isset($options['mirror']) ? $options['mirror'] : null;
        $mirrorUrl = $this->mirrorConfig->getComposerMirror($mirror);

        // 下载安装程序
        $installerUrl = $mirrorUrl . '/installer';
        $installerPath = $this->composerDir . '/installer';

        // 使用curl下载安装程序
        $command = "curl -s {$installerUrl} -o {$installerPath}";
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("下载Composer安装程序失败: " . implode("\n", $output));
        }

        return $installerPath;
    }

    /**
     * 创建Composer包装脚本
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return bool
     */
    private function createComposerWrapper($phpVersion, $composerVersion)
    {
        // 获取PHP二进制文件路径
        $phpBin = $this->versionSwitcher->getBinaryPath($phpVersion);

        // 获取Composer目录
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);
        $composerPhar = $composerVersionDir . '/composer.phar';

        // 创建包装脚本
        $wrapperPath = $composerVersionDir . '/composer';
        $wrapperContent = "#!/bin/bash\n\n";
        $wrapperContent .= "{$phpBin} {$composerPhar} \"\$@\"\n";

        file_put_contents($wrapperPath, $wrapperContent);
        chmod($wrapperPath, 0755);

        return true;
    }

    /**
     * 设置默认Composer
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return bool
     */
    public function setDefaultComposer($phpVersion, $composerVersion)
    {
        // 检查Composer是否已安装
        if (!$this->isInstalled($phpVersion, $composerVersion)) {
            throw new \Exception("Composer {$composerVersion} 未安装于PHP {$phpVersion}");
        }

        // 获取Composer目录
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);
        $composerWrapper = $composerVersionDir . '/composer';

        // 创建符号链接
        $defaultComposerPath = $this->pvmRoot . '/bin/composer';

        // 确保bin目录存在
        $binDir = dirname($defaultComposerPath);
        if (!is_dir($binDir)) {
            mkdir($binDir, 0755, true);
        }

        // 如果已存在符号链接，则先删除
        if (file_exists($defaultComposerPath)) {
            unlink($defaultComposerPath);
        }

        // 创建符号链接
        symlink($composerWrapper, $defaultComposerPath);

        // 保存默认Composer配置
        $this->saveDefaultComposerConfig($phpVersion, $composerVersion);

        return true;
    }

    /**
     * 保存默认Composer配置
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return bool
     */
    private function saveDefaultComposerConfig($phpVersion, $composerVersion)
    {
        $configFile = $this->pvmRoot . '/config/composer.php';

        // 确保配置目录存在
        $configDir = dirname($configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        // 保存配置
        $config = [
            'php_version' => $phpVersion,
            'composer_version' => $composerVersion,
        ];

        $content = "<?php\n\n// 默认Composer配置\n// 由 PVM 自动生成，可以手动修改\n\nreturn " . var_export($config, true) . ";\n";
        return file_put_contents($configFile, $content) !== false;
    }

    /**
     * 获取默认Composer配置
     *
     * @return array|null
     */
    public function getDefaultComposerConfig()
    {
        $configFile = $this->pvmRoot . '/config/composer.php';

        if (file_exists($configFile)) {
            return require $configFile;
        }

        return null;
    }

    /**
     * 检查Composer是否已安装
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return bool
     */
    public function isInstalled($phpVersion, $composerVersion)
    {
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);
        $composerPhar = $composerVersionDir . '/composer.phar';

        return file_exists($composerPhar);
    }

    /**
     * 获取已安装的Composer列表
     *
     * @return array
     */
    public function getInstalledComposers()
    {
        $result = [];

        // 获取已安装的PHP版本
        $phpVersions = $this->versionSwitcher->getInstalledVersions();

        foreach ($phpVersions as $phpVersion) {
            $phpComposerDir = $this->composerDir . '/' . $phpVersion . '/composer';

            if (is_dir($phpComposerDir)) {
                $composerVersions = [];

                // 获取已安装的Composer版本
                $dirs = scandir($phpComposerDir);
                foreach ($dirs as $dir) {
                    if ($dir === '.' || $dir === '..') {
                        continue;
                    }

                    $composerVersionDir = $phpComposerDir . '/' . $dir;
                    $composerPhar = $composerVersionDir . '/composer.phar';

                    if (is_dir($composerVersionDir) && file_exists($composerPhar)) {
                        $composerVersions[] = $dir;
                    }
                }

                if (!empty($composerVersions)) {
                    $result[$phpVersion] = $composerVersions;
                }
            }
        }

        return $result;
    }

    /**
     * 获取Composer版本目录
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return string
     */
    private function getComposerVersionDir($phpVersion, $composerVersion)
    {
        // 将Composer安装到PHP版本目录下的composer子目录中
        return $this->composerDir . '/' . $phpVersion . '/composer/' . $composerVersion;
    }

    /**
     * 获取Composer版本信息
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return array|null
     */
    public function getComposerInfo($phpVersion, $composerVersion)
    {
        // 检查Composer是否已安装
        if (!$this->isInstalled($phpVersion, $composerVersion)) {
            return null;
        }

        // 获取Composer目录
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);
        $composerPhar = $composerVersionDir . '/composer.phar';

        // 获取PHP二进制文件路径
        $phpBin = $this->versionSwitcher->getBinaryPath($phpVersion);

        // 获取Composer版本信息
        $command = "{$phpBin} {$composerPhar} --version";
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            return null;
        }

        // 解析版本信息
        $versionInfo = [];

        if (!empty($output)) {
            $versionLine = $output[0];

            // 提取版本号
            if (preg_match('/Composer version ([^\s]+)/', $versionLine, $matches)) {
                $versionInfo['version'] = $matches[1];
            }

            // 提取完整版本信息
            $versionInfo['full_version'] = $versionLine;
        }

        // 获取Composer配置信息
        $command = "{$phpBin} {$composerPhar} config --list";
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            $config = [];

            foreach ($output as $line) {
                if (strpos($line, '[') === 0) {
                    continue;
                }

                if (preg_match('/^([^=]+)=(.*)$/', $line, $matches)) {
                    $key = trim($matches[1]);
                    $value = trim($matches[2]);
                    $config[$key] = $value;
                }
            }

            $versionInfo['config'] = $config;
        }

        return $versionInfo;
    }

    /**
     * 配置Composer
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @param array $config 配置项
     * @return bool
     */
    public function configure($phpVersion, $composerVersion, array $config)
    {
        // 检查Composer是否已安装
        if (!$this->isInstalled($phpVersion, $composerVersion)) {
            throw new \Exception("Composer {$composerVersion} 未安装于PHP {$phpVersion}");
        }

        // 获取Composer目录
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);
        $composerPhar = $composerVersionDir . '/composer.phar';

        // 获取PHP二进制文件路径
        $phpBin = $this->versionSwitcher->getBinaryPath($phpVersion);

        // 配置Composer
        foreach ($config as $key => $value) {
            $command = "{$phpBin} {$composerPhar} config --global {$key} {$value}";
            $output = [];
            $returnCode = 0;

            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("配置Composer失败: " . implode("\n", $output));
            }
        }

        return true;
    }

    /**
     * 删除Composer
     *
     * @param string $phpVersion PHP版本
     * @param string $composerVersion Composer版本
     * @return bool
     */
    public function remove($phpVersion, $composerVersion)
    {
        // 检查Composer是否已安装
        if (!$this->isInstalled($phpVersion, $composerVersion)) {
            throw new \Exception("Composer {$composerVersion} 未安装于PHP {$phpVersion}");
        }

        // 获取Composer目录
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);

        // 检查是否为默认Composer
        $defaultConfig = $this->getDefaultComposerConfig();
        if ($defaultConfig && $defaultConfig['php_version'] === $phpVersion && $defaultConfig['composer_version'] === $composerVersion) {
            // 删除默认Composer符号链接
            $defaultComposerPath = $this->pvmRoot . '/bin/composer';
            if (file_exists($defaultComposerPath)) {
                unlink($defaultComposerPath);
            }

            // 删除默认Composer配置
            $configFile = $this->pvmRoot . '/config/composer.php';
            if (file_exists($configFile)) {
                unlink($configFile);
            }
        }

        // 删除Composer目录
        $this->removeDirectory($composerVersionDir);

        return true;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     * @return bool
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
     * 获取当前PHP版本的Composer版本
     *
     * @param string $phpVersion PHP版本
     * @return string|null
     */
    public function getComposerVersion($phpVersion)
    {
        // 获取默认配置
        $defaultConfig = $this->getDefaultComposerConfig();

        if ($defaultConfig && $defaultConfig['php_version'] === $phpVersion) {
            return $defaultConfig['composer_version'];
        }

        // 如果没有默认配置，查找已安装的Composer版本
        $installedComposers = $this->getInstalledComposers();

        if (isset($installedComposers[$phpVersion]) && !empty($installedComposers[$phpVersion])) {
            // 返回第一个找到的版本
            return $installedComposers[$phpVersion][0];
        }

        return null;
    }

    /**
     * 获取可用的Composer版本列表
     *
     * @return array
     */
    public function getAvailableComposerVersions()
    {
        return [
            [
                'version' => '2.6.5',
                'name' => 'Composer 2.6.5',
                'description' => '最新稳定版本',
                'recommended' => true,
            ],
            [
                'version' => '2.5.8',
                'name' => 'Composer 2.5.8',
                'description' => '稳定版本',
                'recommended' => false,
            ],
            [
                'version' => '2',
                'name' => 'Composer 2.x',
                'description' => '最新的2.x版本',
                'recommended' => false,
            ],
            [
                'version' => '1',
                'name' => 'Composer 1.x',
                'description' => '旧版本（不推荐）',
                'recommended' => false,
            ],
        ];
    }

    /**
     * 执行Composer命令
     *
     * @param string $command Composer命令
     * @param string $workingDir 工作目录
     * @param string $phpVersion PHP版本，如果为null则使用当前版本
     * @param string $composerVersion Composer版本，如果为null则使用默认版本
     * @return array [output, returnCode]
     */
    public function executeCommand($command, $workingDir, $phpVersion = null, $composerVersion = null)
    {
        // 如果未指定PHP版本，则使用当前版本
        if ($phpVersion === null) {
            $phpVersion = $this->versionSwitcher->getCurrentVersion();
        }

        // 如果未指定Composer版本，则使用默认版本
        if ($composerVersion === null) {
            $defaultConfig = $this->getDefaultComposerConfig();

            if ($defaultConfig) {
                $composerVersion = $defaultConfig['composer_version'];
            } else {
                $composerVersion = '2';
            }
        }

        // 检查Composer是否已安装
        if (!$this->isInstalled($phpVersion, $composerVersion)) {
            throw new \Exception("Composer {$composerVersion} 未安装于PHP {$phpVersion}");
        }

        // 获取Composer目录
        $composerVersionDir = $this->getComposerVersionDir($phpVersion, $composerVersion);
        $composerWrapper = $composerVersionDir . '/composer';

        // 执行命令
        $fullCommand = "cd {$workingDir} && {$composerWrapper} {$command}";
        $output = [];
        $returnCode = 0;

        exec($fullCommand . ' 2>&1', $output, $returnCode);

        return [$output, $returnCode];
    }
}
