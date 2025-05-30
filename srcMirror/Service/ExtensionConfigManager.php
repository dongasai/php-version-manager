<?php

namespace Mirror\Service;

/**
 * 扩展配置管理服务
 *
 * 负责管理分离的扩展配置文件
 */
class ExtensionConfigManager
{
    private $configDir;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->configDir = ROOT_DIR . '/configMirror';
    }

    /**
     * 获取PHP版本配置
     *
     * @return array PHP版本配置
     */
    public function getPhpVersions()
    {
        $configFile = $this->configDir . '/extensions/php/versions.php';

        if (!file_exists($configFile)) {
            return [];
        }

        $config = require $configFile;
        return $config['versions'] ?? [];
    }

    /**
     * 获取Composer版本配置
     *
     * @return array Composer版本配置
     */
    public function getComposerVersions()
    {
        $configFile = $this->configDir . '/composer/versions.php';

        if (!file_exists($configFile)) {
            return [];
        }

        $config = require $configFile;
        return $config['versions'] ?? [];
    }

    /**
     * 获取PECL扩展版本配置
     *
     * @param string $extensionName 扩展名
     * @return array 扩展版本配置
     */
    public function getPeclExtensionVersions($extensionName)
    {
        $configFile = $this->configDir . "/extensions/pecl/{$extensionName}.php";

        if (!file_exists($configFile)) {
            return [];
        }

        $config = require $configFile;
        return $config['recommended_versions'] ?? $config['all_versions'] ?? [];
    }

    /**
     * 获取GitHub扩展版本配置
     *
     * @param string $extensionName 扩展名
     * @return array 扩展版本配置
     */
    public function getGithubExtensionVersions($extensionName)
    {
        $configFile = $this->configDir . "/extensions/github/{$extensionName}.php";

        if (!file_exists($configFile)) {
            return [];
        }

        $config = require $configFile;
        return $config['recommended_versions'] ?? $config['all_versions'] ?? [];
    }

    /**
     * 获取GitHub扩展配置
     *
     * @param string $extensionName 扩展名
     * @return array 扩展配置
     */
    public function getGithubExtensionConfig($extensionName)
    {
        $configFile = $this->configDir . "/extensions/github/{$extensionName}.php";

        if (!file_exists($configFile)) {
            return [];
        }

        return require $configFile;
    }

    /**
     * 获取所有PECL扩展的版本配置
     *
     * @return array 所有PECL扩展版本配置
     */
    public function getAllPeclExtensionVersions()
    {
        $peclDir = $this->configDir . '/extensions/pecl';
        $extensions = [];

        if (!is_dir($peclDir)) {
            return $extensions;
        }

        $files = glob($peclDir . '/*.php');
        foreach ($files as $file) {
            $extensionName = basename($file, '.php');
            $config = require $file;
            $extensions[$extensionName] = $config['recommended_versions'] ?? $config['all_versions'] ?? [];
        }

        return $extensions;
    }

    /**
     * 获取所有GitHub扩展的版本配置
     *
     * @return array 所有GitHub扩展版本配置
     */
    public function getAllGithubExtensionVersions()
    {
        $githubDir = $this->configDir . '/extensions/github';
        $extensions = [];

        if (!is_dir($githubDir)) {
            return $extensions;
        }

        $files = glob($githubDir . '/*.php');
        foreach ($files as $file) {
            $extensionName = basename($file, '.php');
            $config = require $file;
            $extensions[$extensionName] = $config['recommended_versions'] ?? $config['all_versions'] ?? [];
        }

        return $extensions;
    }

    /**
     * 保存PHP版本配置
     *
     * @param array $versions 版本配置（按主版本分组）
     * @return bool 是否成功
     */
    public function savePhpVersions($versions)
    {
        $configFile = $this->configDir . '/extensions/php/versions.php';
        $config = $this->loadConfigFile($configFile);

        $config['versions'] = $versions;
        $config['metadata']['last_updated'] = date('Y-m-d H:i:s');
        $config['metadata']['auto_updated'] = true;

        // 计算总版本数（所有主版本的版本数之和）
        $totalVersions = 0;
        foreach ($versions as $versionList) {
            $totalVersions += count($versionList);
        }
        $config['metadata']['total_versions'] = $totalVersions;

        return $this->saveConfigFile($configFile, $config);
    }

    /**
     * 保存Composer版本配置
     *
     * @param array $versions 版本配置
     * @return bool 是否成功
     */
    public function saveComposerVersions($versions)
    {
        $configFile = $this->configDir . '/composer/versions.php';
        $config = $this->loadConfigFile($configFile);

        $config['versions'] = $versions;
        $config['metadata']['last_updated'] = date('Y-m-d H:i:s');
        $config['metadata']['auto_updated'] = true;
        $config['metadata']['total_versions'] = count($versions);

        return $this->saveConfigFile($configFile, $config);
    }

    /**
     * 保存PECL扩展版本配置
     *
     * @param string $extensionName 扩展名
     * @param array $versions 版本配置
     * @return bool 是否成功
     */
    public function savePeclExtensionVersions($extensionName, $versions)
    {
        $configFile = $this->configDir . "/extensions/pecl/{$extensionName}.php";
        $config = $this->loadConfigFile($configFile);

        $config['all_versions'] = $versions;
        $config['recommended_versions'] = $this->selectRecommendedVersions($versions);
        $config['metadata']['last_updated'] = date('Y-m-d H:i:s');
        $config['metadata']['auto_updated'] = true;
        $config['metadata']['total_discovered'] = count($versions);
        $config['metadata']['total_recommended'] = count($config['recommended_versions']);

        return $this->saveConfigFile($configFile, $config);
    }

    /**
     * 保存GitHub扩展版本配置
     *
     * @param string $extensionName 扩展名
     * @param array $versions 版本配置
     * @return bool 是否成功
     */
    public function saveGithubExtensionVersions($extensionName, $versions)
    {
        $configFile = $this->configDir . "/extensions/github/{$extensionName}.php";
        $config = $this->loadConfigFile($configFile);

        $config['all_versions'] = $versions;
        $config['recommended_versions'] = $this->selectRecommendedVersions($versions);
        $config['metadata']['last_updated'] = date('Y-m-d H:i:s');
        $config['metadata']['auto_updated'] = true;
        $config['metadata']['total_discovered'] = count($versions);
        $config['metadata']['total_recommended'] = count($config['recommended_versions']);

        return $this->saveConfigFile($configFile, $config);
    }

    /**
     * 加载配置文件
     *
     * @param string $configFile 配置文件路径
     * @return array 配置数组
     */
    private function loadConfigFile($configFile)
    {
        if (file_exists($configFile)) {
            return require $configFile;
        }

        return [];
    }

    /**
     * 保存配置文件
     *
     * @param string $configFile 配置文件路径
     * @param array $config 配置数组
     * @return bool 是否成功
     */
    private function saveConfigFile($configFile, $config)
    {
        try {
            // 确保目录存在
            $dir = dirname($configFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $content = "<?php\n\n";
            $content .= "/**\n";
            $content .= " * " . ($config['name'] ?? 'Extension') . " 版本配置文件\n";
            $content .= " * \n";
            $content .= " * 此文件由版本发现服务自动更新\n";
            $content .= " * 最后更新时间: " . ($config['metadata']['last_updated'] ?? date('Y-m-d H:i:s')) . "\n";
            $content .= " */\n\n";
            $content .= "return " . $this->arrayToPhpCode($config, 0) . ";\n";

            $result = file_put_contents($configFile, $content);

            return $result !== false;

        } catch (\Exception $e) {
            echo "  错误: 保存配置文件失败: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * 选择推荐版本
     *
     * @param array $versions 所有版本
     * @return array 推荐版本
     */
    private function selectRecommendedVersions($versions)
    {
        // 如果版本数量少于等于10个，全部推荐
        if (count($versions) <= 10) {
            return $versions;
        }

        // 否则使用智能选择
        return $this->selectSmartVersions($versions);
    }

    /**
     * 智能选择版本
     *
     * @param array $versions 所有版本
     * @return array 智能选择的版本
     */
    private function selectSmartVersions($versions)
    {
        // 按主版本分组
        $grouped = [];
        foreach ($versions as $version) {
            if (preg_match('/^(\d+)\./', $version, $matches)) {
                $major = $matches[1];
                if (!isset($grouped[$major])) {
                    $grouped[$major] = [];
                }
                $grouped[$major][] = $version;
            }
        }

        $selected = [];

        // 对每个主版本，选择最新的几个版本
        foreach ($grouped as $major => $majorVersions) {
            usort($majorVersions, 'version_compare');
            $latestVersions = array_slice($majorVersions, -3);
            $selected = array_merge($selected, $latestVersions);
        }

        usort($selected, 'version_compare');
        return $selected;
    }

    /**
     * 将数组转换为PHP代码
     *
     * @param mixed $data 数据
     * @param int $indent 缩进级别
     * @return string PHP代码
     */
    private function arrayToPhpCode($data, $indent = 0)
    {
        $spaces = str_repeat('    ', $indent);

        if (is_array($data)) {
            $result = "[\n";
            foreach ($data as $key => $value) {
                $result .= $spaces . '    ';
                if (is_string($key)) {
                    $result .= "'" . addslashes($key) . "' => ";
                }
                $result .= $this->arrayToPhpCode($value, $indent + 1);
                $result .= ",\n";
            }
            $result .= $spaces . "]";
            return $result;
        } elseif (is_string($data)) {
            return "'" . addslashes($data) . "'";
        } elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        } elseif (is_null($data)) {
            return 'null';
        } else {
            return (string)$data;
        }
    }
}
