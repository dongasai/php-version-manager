<?php

namespace VersionManager\Core\Security;

use VersionManager\Core\VersionSwitcher;

/**
 * 安全更新类
 * 
 * 负责检查和应用安全更新
 */
class SecurityUpdater
{
    /**
     * 是否启用安全更新
     *
     * @var bool
     */
    private $enabled = true;
    
    /**
     * 是否自动更新
     *
     * @var bool
     */
    private $autoUpdate = false;
    
    /**
     * 安全更新API URL
     *
     * @var string
     */
    private $apiUrl = 'https://www.php.net/releases/index.php?json&version=';
    
    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $versionSwitcher;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->versionSwitcher = new VersionSwitcher();
    }
    
    /**
     * 设置是否启用安全更新
     *
     * @param bool $enabled 是否启用
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }
    
    /**
     * 设置是否自动更新
     *
     * @param bool $autoUpdate 是否自动更新
     * @return $this
     */
    public function setAutoUpdate($autoUpdate)
    {
        $this->autoUpdate = $autoUpdate;
        return $this;
    }
    
    /**
     * 设置安全更新API URL
     *
     * @param string $apiUrl API URL
     * @return $this
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }
    
    /**
     * 检查安全更新
     *
     * @param string $version PHP版本
     * @return array|false 安全更新信息，如果没有更新则返回false
     */
    public function checkSecurityUpdate($version)
    {
        if (!$this->enabled) {
            return false;
        }
        
        // 提取主版本号和次版本号
        $versionParts = explode('.', $version);
        $majorMinor = $versionParts[0] . '.' . $versionParts[1];
        
        // 获取安全更新信息
        $updateInfo = $this->getSecurityUpdateInfo($majorMinor);
        if (!$updateInfo) {
            return false;
        }
        
        // 检查是否有更新
        $latestVersion = $updateInfo['version'];
        if (version_compare($version, $latestVersion, '>=')) {
            return false;
        }
        
        // 返回更新信息
        return [
            'current_version' => $version,
            'latest_version' => $latestVersion,
            'security_fixes' => isset($updateInfo['security']) ? $updateInfo['security'] : [],
            'release_date' => isset($updateInfo['date']) ? $updateInfo['date'] : '',
            'update_url' => isset($updateInfo['announcement']) ? $updateInfo['announcement'] : '',
        ];
    }
    
    /**
     * 检查所有已安装版本的安全更新
     *
     * @return array 安全更新信息
     */
    public function checkAllSecurityUpdates()
    {
        if (!$this->enabled) {
            return [];
        }
        
        $updates = [];
        
        // 获取所有已安装的PHP版本
        $installedVersions = $this->versionSwitcher->getInstalledVersions();
        
        foreach ($installedVersions as $version) {
            $updateInfo = $this->checkSecurityUpdate($version);
            if ($updateInfo) {
                $updates[$version] = $updateInfo;
            }
        }
        
        return $updates;
    }
    
    /**
     * 应用安全更新
     *
     * @param string $version PHP版本
     * @return bool 是否更新成功
     * @throws \Exception 更新失败时抛出异常
     */
    public function applySecurityUpdate($version)
    {
        if (!$this->enabled) {
            return false;
        }
        
        // 检查安全更新
        $updateInfo = $this->checkSecurityUpdate($version);
        if (!$updateInfo) {
            return false;
        }
        
        // 获取最新版本
        $latestVersion = $updateInfo['latest_version'];
        
        // 安装最新版本
        $installer = new \VersionManager\Core\VersionInstaller();
        $success = $installer->install($latestVersion);
        
        if (!$success) {
            throw new \Exception("无法安装PHP {$latestVersion}");
        }
        
        // 如果当前使用的是需要更新的版本，则切换到新版本
        $currentVersion = $this->versionSwitcher->getCurrentVersion();
        if ($currentVersion === $version) {
            $this->versionSwitcher->switchVersion($latestVersion);
        }
        
        return true;
    }
    
    /**
     * 应用所有安全更新
     *
     * @return array 更新结果
     */
    public function applyAllSecurityUpdates()
    {
        if (!$this->enabled) {
            return [];
        }
        
        $results = [];
        
        // 检查所有安全更新
        $updates = $this->checkAllSecurityUpdates();
        
        foreach ($updates as $version => $updateInfo) {
            try {
                $success = $this->applySecurityUpdate($version);
                $results[$version] = [
                    'success' => $success,
                    'message' => $success ? "已更新到 {$updateInfo['latest_version']}" : "更新失败",
                ];
            } catch (\Exception $e) {
                $results[$version] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 获取安全更新信息
     *
     * @param string $majorMinor 主版本号和次版本号
     * @return array|false 安全更新信息，如果获取失败则返回false
     */
    private function getSecurityUpdateInfo($majorMinor)
    {
        $url = $this->apiUrl . $majorMinor;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            return false;
        }
        
        $data = json_decode($response, true);
        if (!$data || !isset($data['version'])) {
            return false;
        }
        
        return $data;
    }
}
