<?php

namespace VersionManager\Core;

/**
 * PHP扩展管理器类
 *
 * 负责管理PHP扩展的安装、配置和删除
 */
class ExtensionManager
{
    /**
     * PHP版本
     *
     * @var string
     */
    private $phpVersion;

    /**
     * 扩展配置类
     *
     * @var ExtensionConfig
     */
    private $config;

    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $switcher;

    /**
     * 已安装的扩展列表
     *
     * @var array
     */
    private $installedExtensions = [];

    /**
     * 可用的扩展列表
     *
     * @var array
     */
    private $availableExtensions = [];

    /**
     * 构造函数
     *
     * @param string $phpVersion PHP版本
     */
    public function __construct($phpVersion = null)
    {
        $this->switcher = new VersionSwitcher();
        $this->phpVersion = $phpVersion ?: $this->switcher->getCurrentVersion();
        $this->config = new ExtensionConfig($this->phpVersion);
        $this->loadExtensions();
    }

    /**
     * 加载扩展信息
     */
    private function loadExtensions()
    {
        // 加载已安装的扩展
        $this->loadInstalledExtensions();

        // 加载可用的扩展
        $this->loadAvailableExtensions();
    }

    /**
     * 加载已安装的扩展
     */
    private function loadInstalledExtensions()
    {
        // 获取已配置的扩展
        $configuredExtensions = $this->config->getConfiguredExtensions();

        // 获取PHP内置扩展
        $builtinExtensions = $this->getBuiltinExtensions();

        // 合并扩展列表
        $this->installedExtensions = array_merge($configuredExtensions, $builtinExtensions);
    }

    /**
     * 加载可用的扩展
     */
    private function loadAvailableExtensions()
    {
        // 加载常用扩展配置
        $configFile = __DIR__ . '/../../config/extensions/common_extensions.php';
        if (file_exists($configFile)) {
            $this->availableExtensions = require $configFile;
        }

        // 加载 PECL 扩展列表
        $this->loadPeclExtensions();
    }

    /**
     * 获取PHP内置扩展
     *
     * @return array
     */
    private function getBuiltinExtensions()
    {
        $extensions = [];

        // 使用 PHP 命令行获取内置扩展列表
        $output = [];
        $phpBin = $this->getPhpBinary();
        exec($phpBin . ' -m', $output);

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
                $extensions[$line] = [
                    'name' => $line,
                    'type' => 'builtin',
                    'status' => 'enabled',
                    'config' => [],
                ];
            }
        }

        return $extensions;
    }

    /**
     * 获取PHP二进制文件路径
     *
     * @return string
     */
    private function getPhpBinary()
    {
        // 默认使用 PVM 管理的 PHP 版本
        $pvmDir = getenv('HOME') . '/.pvm';
        $versionDir = $pvmDir . '/versions/' . $this->phpVersion;
        $phpBin = $versionDir . '/bin/php';

        if (file_exists($phpBin)) {
            return $phpBin;
        }

        // 如果不存在，则使用系统 PHP
        return 'php';
    }

    /**
     * 加载 PECL 扩展列表
     */
    private function loadPeclExtensions()
    {
        // 使用 pecl list-all 命令获取所有可用的 PECL 扩展
        $output = [];
        exec('pecl list-all 2>/dev/null', $output);

        // 跳过前两行标题
        $output = array_slice($output, 2);

        foreach ($output as $line) {
            // 解析扩展信息
            if (preg_match('/^([^\s]+)\s+([^\s]+)\s+(.*)$/', $line, $matches)) {
                $name = $matches[1];
                $version = $matches[2];
                $description = trim($matches[3]);

                // 如果扩展已经存在于可用列表中，则跳过
                if (isset($this->availableExtensions[$name])) {
                    continue;
                }

                $this->availableExtensions[$name] = [
                    'name' => $name,
                    'version' => $version,
                    'description' => $description,
                    'source' => 'pecl',
                ];
            }
        }
    }

    /**
     * 获取已安装的扩展列表
     *
     * @return array
     */
    public function getInstalledExtensions()
    {
        return $this->installedExtensions;
    }

    /**
     * 获取可用的扩展列表
     *
     * @return array
     */
    public function getAvailableExtensions()
    {
        return $this->availableExtensions;
    }

    /**
     * 获取扩展信息
     *
     * @param string $extension 扩展名称
     * @return ExtensionInfo|null
     */
    public function getExtensionInfo($extension)
    {
        if (isset($this->installedExtensions[$extension])) {
            $info = $this->installedExtensions[$extension];
            return new ExtensionInfo(
                $extension,
                isset($info['version']) ? $info['version'] : '',
                isset($info['type']) ? $info['type'] : '',
                isset($info['enabled']) && $info['enabled'] ? 'enabled' : 'disabled',
                isset($info['dependencies']) ? $info['dependencies'] : [],
                isset($info['config']) ? $info['config'] : []
            );
        }

        if (isset($this->availableExtensions[$extension])) {
            $info = $this->availableExtensions[$extension];
            return new ExtensionInfo(
                $extension,
                isset($info['version']) ? $info['version'] : '',
                '',
                'available',
                isset($info['dependencies']) ? $info['dependencies'] : [],
                []
            );
        }

        return null;
    }

    /**
     * 检查扩展是否已安装
     *
     * @param string $extension 扩展名称
     * @return bool
     */
    public function isExtensionInstalled($extension)
    {
        return isset($this->installedExtensions[$extension]);
    }

    /**
     * 安装扩展
     *
     * @param string $extension 扩展名称
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws \Exception 安装失败时抛出异常
     */
    public function installExtension($extension, array $options = [])
    {
        // 检查扩展是否已安装
        if ($this->isExtensionInstalled($extension)) {
            throw new \Exception("扩展 {$extension} 已经安装");
        }

        // 检查扩展是否可用
        if (!isset($this->availableExtensions[$extension])) {
            throw new \Exception("扩展 {$extension} 不可用");
        }

        $info = $this->availableExtensions[$extension];
        $source = isset($info['source']) ? $info['source'] : 'pecl';

        // 根据扩展来源选择安装方式
        switch ($source) {
            case 'pecl':
                return $this->installPeclExtension($extension, $options);
            case 'source':
                return $this->installFromSource($extension, $options);
            default:
                throw new \Exception("不支持的扩展来源: {$source}");
        }
    }

    /**
     * 从 PECL 安装扩展
     *
     * @param string $extension 扩展名称
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws \Exception 安装失败时抛出异常
     */
    private function installPeclExtension($extension, array $options = [])
    {
        // 获取 PHP 版本的配置选项
        $phpConfig = $this->getPhpBinary() . '-config';
        if (!file_exists($phpConfig)) {
            $phpConfig = 'php-config';
        }

        // 构建 pecl 命令
        $command = 'pecl install';

        // 添加版本限制
        if (isset($options['version'])) {
            $command .= ' ' . $extension . '-' . $options['version'];
        } else {
            $command .= ' ' . $extension;
        }

        // 添加其他选项
        if (isset($options['force']) && $options['force']) {
            $command .= ' --force';
        }

        // 执行安装命令
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("安装扩展 {$extension} 失败: " . implode("\n", $output));
        }

        // 启用扩展
        $isZend = isset($options['zend']) && $options['zend'];
        $config = isset($options['config']) ? $options['config'] : [];
        $this->config->enableExtension($extension, $config, $isZend);

        // 重新加载扩展信息
        $this->loadExtensions();

        return true;
    }

    /**
     * 从源码安装扩展
     *
     * @param string $extension 扩展名称
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws \Exception 安装失败时抛出异常
     */
    private function installFromSource($extension, array $options = [])
    {
        // 创建临时目录
        $tempDir = sys_get_temp_dir() . '/pvm_ext_' . $extension;
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // 下载源码
        $sourceUrl = isset($options['source_url']) ? $options['source_url'] : '';
        if (empty($sourceUrl)) {
            throw new \Exception("没有指定源码URL");
        }

        $tarFile = $tempDir . '/' . basename($sourceUrl);
        $this->downloadFile($sourceUrl, $tarFile);

        // 解压源码
        $this->extractTarball($tarFile, $tempDir);

        // 查找源码目录
        $sourceDir = $this->findSourceDir($tempDir, $extension);
        if (!$sourceDir) {
            throw new \Exception("无法找到扩展 {$extension} 的源码目录");
        }

        // 编译和安装扩展
        $this->compileAndInstallExtension($extension, $sourceDir, $options);

        // 清理临时目录
        $this->removeDirectory($tempDir);

        // 重新加载扩展信息
        $this->loadExtensions();

        return true;
    }

    /**
     * 下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return bool 是否下载成功
     * @throws \Exception 下载失败时抛出异常
     */
    private function downloadFile($url, $destination)
    {
        $command = "curl -L -o {$destination} {$url}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("下载文件失败: " . implode("\n", $output));
        }

        return true;
    }

    /**
     * 解压文件
     *
     * @param string $tarFile 压缩文件路径
     * @param string $destination 目标目录
     * @return bool 是否解压成功
     * @throws \Exception 解压失败时抛出异常
     */
    private function extractTarball($tarFile, $destination)
    {
        $command = "tar -xzf {$tarFile} -C {$destination}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("解压文件失败: " . implode("\n", $output));
        }

        return true;
    }

    /**
     * 查找源码目录
     *
     * @param string $baseDir 基础目录
     * @param string $extension 扩展名称
     * @return string|false 源码目录路径，如果未找到则返回 false
     */
    private function findSourceDir($baseDir, $extension)
    {
        // 直接查找扩展目录
        $dirs = glob($baseDir . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $dirName = basename($dir);

            // 检查目录名称是否包含扩展名称
            if (strpos($dirName, $extension) !== false) {
                return $dir;
            }

            // 检查是否有 config.m4 文件
            if (file_exists($dir . '/config.m4')) {
                $content = file_get_contents($dir . '/config.m4');
                if (strpos($content, $extension) !== false) {
                    return $dir;
                }
            }
        }

        // 如果只有一个目录，则返回该目录
        if (count($dirs) === 1) {
            return $dirs[0];
        }

        return false;
    }

    /**
     * 编译和安装扩展
     *
     * @param string $extension 扩展名称
     * @param string $sourceDir 源码目录
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws \Exception 安装失败时抛出异常
     */
    private function compileAndInstallExtension($extension, $sourceDir, array $options = [])
    {
        // 获取 PHP 版本的配置选项
        $phpConfig = $this->getPhpBinary() . '-config';
        if (!file_exists($phpConfig)) {
            $phpConfig = 'php-config';
        }

        // 进入源码目录
        $currentDir = getcwd();
        chdir($sourceDir);

        // 运行 phpize
        $phpize = dirname($phpConfig) . '/phpize';
        if (!file_exists($phpize)) {
            $phpize = 'phpize';
        }

        $output = [];
        $returnCode = 0;
        exec($phpize . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("phpize 失败: " . implode("\n", $output));
        }

        // 配置
        $configureOptions = isset($options['configure_options']) ? $options['configure_options'] : [];
        $configureCommand = './configure --with-php-config=' . $phpConfig;

        foreach ($configureOptions as $option) {
            $configureCommand .= ' ' . $option;
        }

        $output = [];
        $returnCode = 0;
        exec($configureCommand . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("配置失败: " . implode("\n", $output));
        }

        // 编译
        $output = [];
        $returnCode = 0;
        exec('make 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("编译失败: " . implode("\n", $output));
        }

        // 安装
        $output = [];
        $returnCode = 0;
        exec('make install 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            chdir($currentDir);
            throw new \Exception("安装失败: " . implode("\n", $output));
        }

        // 返回原目录
        chdir($currentDir);

        // 启用扩展
        $isZend = isset($options['zend']) && $options['zend'];
        $config = isset($options['config']) ? $options['config'] : [];
        $this->config->enableExtension($extension, $config, $isZend);

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
     * 删除扩展
     *
     * @param string $extension 扩展名称
     * @param array $options 删除选项
     * @return bool 是否删除成功
     * @throws \Exception 删除失败时抛出异常
     */
    public function removeExtension($extension, array $options = [])
    {
        // 检查扩展是否已安装
        if (!$this->isExtensionInstalled($extension)) {
            throw new \Exception("扩展 {$extension} 未安装");
        }

        // 如果是内置扩展，则只能禁用而不能删除
        $info = $this->getExtensionInfo($extension);
        if ($info->isBuiltin()) {
            if (isset($options['disable']) && $options['disable']) {
                return $this->disableExtension($extension);
            } else {
                throw new \Exception("无法删除内置扩展 {$extension}，只能禁用");
            }
        }

        // 删除扩展配置
        $this->config->removeExtensionConfig($extension);

        // 如果是 PECL 扩展，则使用 pecl uninstall 命令删除
        if (isset($options['pecl']) && $options['pecl']) {
            $command = "pecl uninstall {$extension}";
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("删除扩展 {$extension} 失败: " . implode("\n", $output));
            }
        }

        // 重新加载扩展信息
        $this->loadExtensions();

        return true;
    }

    /**
     * 禁用扩展
     *
     * @param string $extension 扩展名称
     * @return bool 是否禁用成功
     * @throws \Exception 禁用失败时抛出异常
     */
    public function disableExtension($extension)
    {
        // 检查扩展是否已安装
        if (!$this->isExtensionInstalled($extension)) {
            throw new \Exception("扩展 {$extension} 未安装");
        }

        // 禁用扩展
        $this->config->disableExtension($extension);

        // 重新加载扩展信息
        $this->loadExtensions();

        return true;
    }

    /**
     * 启用扩展
     *
     * @param string $extension 扩展名称
     * @param array $config 扩展配置
     * @param bool $isZend 是否是Zend扩展
     * @return bool 是否启用成功
     * @throws \Exception 启用失败时抛出异常
     */
    public function enableExtension($extension, array $config = [], $isZend = false)
    {
        // 检查扩展是否已安装
        if (!$this->isExtensionInstalled($extension)) {
            throw new \Exception("扩展 {$extension} 未安装");
        }

        // 启用扩展
        $this->config->enableExtension($extension, $config, $isZend);

        // 重新加载扩展信息
        $this->loadExtensions();

        return true;
    }

    /**
     * 配置扩展
     *
     * @param string $extension 扩展名称
     * @param array $config 扩展配置
     * @return bool 是否配置成功
     * @throws \Exception 配置失败时抛出异常
     */
    public function configureExtension($extension, array $config)
    {
        // 检查扩展是否已安装
        if (!$this->isExtensionInstalled($extension)) {
            throw new \Exception("扩展 {$extension} 未安装");
        }

        // 获取扩展信息
        $info = $this->getExtensionInfo($extension);
        $isZend = $info->isZend();

        // 更新扩展配置
        $this->config->writeExtensionConfig($extension, $config, $isZend);

        // 重新加载扩展信息
        $this->loadExtensions();

        return true;
    }
}
