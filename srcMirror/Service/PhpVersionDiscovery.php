<?php

namespace Mirror\Service;

/**
 * PHP版本发现服务
 * 
 * 从PHP官方API获取所有可用版本
 */
class PhpVersionDiscovery
{
    private $apiUrl = 'https://www.php.net/releases/index.php?json=1';
    private $timeout = 30;

    /**
     * 获取所有可用的PHP版本
     *
     * @return array 版本数组
     */
    public function getAvailableVersions()
    {
        try {
            $data = $this->fetchApiData();
            if (!$data) {
                return [];
            }

            $versions = [];
            
            // 解析当前活跃版本
            if (isset($data['active'])) {
                foreach ($data['active'] as $majorVersion => $versionInfo) {
                    if (isset($versionInfo['version'])) {
                        $versions[] = $versionInfo['version'];
                    }
                }
            }

            // 解析历史版本（如果需要）
            if (isset($data['inactive'])) {
                foreach ($data['inactive'] as $majorVersion => $versionInfo) {
                    if (isset($versionInfo['version'])) {
                        $versions[] = $versionInfo['version'];
                    }
                }
            }

            // 排序版本
            usort($versions, 'version_compare');
            
            return array_unique($versions);
            
        } catch (Exception $e) {
            echo "  错误: 获取PHP版本失败: " . $e->getMessage() . "\n";
            return [];
        }
    }

    /**
     * 获取指定主版本的所有子版本
     *
     * @param string $majorVersion 主版本号，如 '8.3'
     * @return array 子版本数组
     */
    public function getMajorVersionReleases($majorVersion)
    {
        try {
            // 尝试从GitHub API获取更详细的版本信息
            $githubApiUrl = "https://api.github.com/repos/php/php-src/tags";
            $data = $this->fetchGithubData($githubApiUrl);
            
            if (!$data) {
                return [];
            }

            $versions = [];
            foreach ($data as $tag) {
                $tagName = $tag['name'];
                // 匹配版本格式，如 php-8.3.1
                if (preg_match('/^php-(' . preg_quote($majorVersion, '/') . '\.\d+)$/', $tagName, $matches)) {
                    $versions[] = $matches[1];
                }
            }

            // 排序版本
            usort($versions, 'version_compare');
            
            return array_unique($versions);
            
        } catch (Exception $e) {
            echo "  错误: 获取PHP主版本 $majorVersion 的子版本失败: " . $e->getMessage() . "\n";
            return [];
        }
    }

    /**
     * 获取所有主版本及其最新版本
     *
     * @return array 主版本和最新版本的映射
     */
    public function getMajorVersionsWithLatest()
    {
        $allVersions = $this->getAvailableVersions();
        $majorVersions = [];

        foreach ($allVersions as $version) {
            if (preg_match('/^(\d+\.\d+)\./', $version, $matches)) {
                $majorVersion = $matches[1];
                
                if (!isset($majorVersions[$majorVersion]) || 
                    version_compare($version, $majorVersions[$majorVersion], '>')) {
                    $majorVersions[$majorVersion] = $version;
                }
            }
        }

        return $majorVersions;
    }

    /**
     * 从PHP官方API获取数据
     *
     * @return array|null API数据
     */
    private function fetchApiData()
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'user_agent' => 'PVM-Mirror/1.0',
            ]
        ]);

        $response = @file_get_contents($this->apiUrl, false, $context);
        
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * 从GitHub API获取数据
     *
     * @param string $url GitHub API URL
     * @return array|null API数据
     */
    private function fetchGithubData($url)
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'user_agent' => 'PVM-Mirror/1.0',
                'header' => [
                    'Accept: application/vnd.github.v3+json'
                ]
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * 过滤版本（排除alpha、beta、RC等）
     *
     * @param array $versions 版本数组
     * @param bool $stableOnly 是否只返回稳定版本
     * @return array 过滤后的版本数组
     */
    public function filterVersions($versions, $stableOnly = true)
    {
        if (!$stableOnly) {
            return $versions;
        }

        $filtered = [];
        foreach ($versions as $version) {
            // 排除包含alpha、beta、RC等的版本
            if (!preg_match('/(alpha|beta|rc|dev)/i', $version)) {
                $filtered[] = $version;
            }
        }

        return $filtered;
    }

    /**
     * 设置API超时时间
     *
     * @param int $timeout 超时时间（秒）
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
