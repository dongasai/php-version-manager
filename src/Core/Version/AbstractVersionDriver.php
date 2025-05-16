<?php

namespace VersionManager\Core\Version;

use VersionManager\Core\Tags\TaggableInterface;

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
            exec($phpBin . ' -v', $output);

            if (!empty($output)) {
                $info['version_string'] = $output[0];
            }

            // 获取PHP编译选项
            $output = [];
            exec($phpBin . ' -i | grep "Configure Command"', $output);

            if (!empty($output)) {
                $info['configure_command'] = trim(str_replace('Configure Command =>', '', $output[0]));
            }

            // 获取PHP扩展
            $output = [];
            exec($phpBin . ' -m', $output);

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
        passthru($command, $returnCode);

        if ($returnCode !== 0) {
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
        }

        echo "目录清理完成\n";
        return true;
    }

    /**
     * 下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return bool
     */
    protected function downloadFile($url, $destination)
    {
        $command = "curl -L -o {$destination} {$url}";
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("下载文件失败: " . implode("\n", $output));
        }

        return true;
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

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("解压文件失败: " . implode("\n", $output));
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
}
