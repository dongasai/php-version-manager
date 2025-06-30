<?php

namespace VersionManager\Core\Download;

use VersionManager\Core\Config\PvmMirrorConfig;

/**
 * 下载地址管理类
 *
 * 完全依赖pvm-mirror镜像源的URL管理，支持智能测速选择最优镜像源：
 * - 集成MirrorSpeedTest进行镜像源测速
 * - 按测速结果排序镜像源
 * - 不再依赖官方源，只使用镜像源
 */
class UrlManager
{
    /**
     * PVM镜像配置
     *
     * @var PvmMirrorConfig
     */
    private $pvmMirrorConfig;

    /**
     * 镜像源测速器
     *
     * @var MirrorSpeedTest
     */
    private $speedTest;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->pvmMirrorConfig = new PvmMirrorConfig();
        $this->speedTest = new MirrorSpeedTest();
    }

    /**
     * 获取下载URL列表（只使用镜像源，按测速结果排序）
     *
     * @param string $originalUrl 原始URL
     * @return array URL列表（按测速结果排序）
     */
    public function getDownloadUrls($originalUrl)
    {
        $urls = [];

        // 只使用镜像源，不再支持官方源
        if ($this->pvmMirrorConfig->isEnabled()) {
            $mirrorUrl = $this->convertToMirrorUrl($originalUrl);
            if ($mirrorUrl) {
                // 获取按测速结果排序的镜像源
                $optimalMirrors = $this->getOptimalMirrors();

                // 根据测速结果生成URL列表
                foreach ($optimalMirrors as $mirrorInfo) {
                    $urls[] = str_replace($this->getMirrorBaseUrl(), $mirrorInfo['url'], $mirrorUrl);
                }
            }
        }

        return array_unique($urls);
    }

    /**
     * 获取PHP源码下载URL（只使用镜像源，按测速结果排序）
     *
     * @param string $version PHP版本
     * @return array 下载URL列表（按测速结果排序）
     */
    public function getPhpDownloadUrls($version)
    {
        $urls = [];

        // 只使用镜像源
        if ($this->pvmMirrorConfig->isEnabled()) {
            // 获取按测速结果排序的镜像源
            $optimalMirrors = $this->getOptimalMirrors();

            // 根据测速结果生成URL列表
            foreach ($optimalMirrors as $mirrorInfo) {
                $urls[] = $mirrorInfo['url'] . '/php/php-' . $version . '.tar.gz';
            }
        }

        return array_unique($urls);
    }

    /**
     * 获取PECL扩展下载URL
     *
     * @param string $extension 扩展名
     * @param string $version 版本号
     * @return array 下载URL列表（按优先级排序）
     */
    public function getPeclDownloadUrls($extension, $version)
    {
        $originalUrl = "https://pecl.php.net/get/{$extension}-{$version}.tgz";
        return $this->getDownloadUrls($originalUrl);
    }

    /**
     * 获取Composer下载URL
     *
     * @param string $version Composer版本
     * @return array 下载URL列表（按优先级排序）
     */
    public function getComposerDownloadUrls($version = 'stable')
    {
        if ($version === 'stable' || $version === 'latest') {
            $originalUrl = "https://getcomposer.org/download/composer.phar";
        } else {
            $originalUrl = "https://getcomposer.org/download/{$version}/composer.phar";
        }

        return $this->getDownloadUrls($originalUrl);
    }

    /**
     * 获取GitHub扩展下载URL
     *
     * @param string $owner 仓库所有者
     * @param string $repo 仓库名
     * @param string $version 版本号
     * @return array 下载URL列表（按优先级排序）
     */
    public function getGithubExtensionDownloadUrls($owner, $repo, $version)
    {
        $originalUrl = "https://github.com/{$owner}/{$repo}/archive/refs/tags/{$version}.tar.gz";
        return $this->getDownloadUrls($originalUrl);
    }

    /**
     * 将原始URL转换为镜像URL
     *
     * @param string $originalUrl 原始URL
     * @return string|null 镜像URL，如果不支持则返回null
     */
    private function convertToMirrorUrl($originalUrl)
    {
        $parsedUrl = parse_url($originalUrl);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return null;
        }

        $host = $parsedUrl['host'];
        $path = $parsedUrl['path'] ?? '';

        // 根据不同的官方源转换为镜像路径
        switch ($host) {
            case 'www.php.net':
                if (strpos($path, '/distributions/') === 0) {
                    // PHP源码: /distributions/php-8.1.0.tar.gz -> /php/php-8.1.0.tar.gz
                    $filename = basename($path);
                    return $this->getMirrorBaseUrl() . '/php/' . $filename;
                }
                break;

            case 'pecl.php.net':
                if (strpos($path, '/get/') === 0) {
                    // PECL扩展: /get/redis-5.3.4.tgz -> /pecl/redis-5.3.4.tgz
                    $filename = basename($path);
                    return $this->getMirrorBaseUrl() . '/pecl/' . $filename;
                }
                break;

            case 'getcomposer.org':
                if (strpos($path, '/download/') === 0) {
                    // Composer: /download/composer.phar -> /composer/composer.phar
                    // Composer: /download/2.5.1/composer.phar -> /composer/composer-2.5.1.phar
                    $pathParts = explode('/', trim($path, '/'));
                    if (count($pathParts) === 2) {
                        // /download/composer.phar
                        return $this->getMirrorBaseUrl() . '/composer/composer.phar';
                    } elseif (count($pathParts) === 3) {
                        // /download/2.5.1/composer.phar
                        $version = $pathParts[1];
                        return $this->getMirrorBaseUrl() . '/composer/composer-' . $version . '.phar';
                    }
                }
                break;

            case 'github.com':
                if (preg_match('#^/([^/]+)/([^/]+)/archive/refs/tags/(.+)$#', $path, $matches)) {
                    // GitHub扩展: /phpredis/phpredis/archive/refs/tags/5.3.4.tar.gz
                    // -> /github/phpredis/phpredis/5.3.4.tar.gz
                    $owner = $matches[1];
                    $repo = $matches[2];
                    $filename = $matches[3];
                    return $this->getMirrorBaseUrl() . '/github/' . $owner . '/' . $repo . '/' . $filename;
                }
                break;
        }

        return null;
    }

    /**
     * 获取镜像源基础URL
     *
     * @return string
     */
    private function getMirrorBaseUrl()
    {
        return $this->pvmMirrorConfig->getMirrorUrl();
    }

    /**
     * 检查URL是否支持镜像
     *
     * @param string $url 要检查的URL
     * @return bool
     */
    public function isMirrorSupported($url)
    {
        return $this->convertToMirrorUrl($url) !== null;
    }

    /**
     * 获取按测速结果排序的最优镜像源列表
     *
     * @return array 按速度排序的镜像源列表
     */
    private function getOptimalMirrors()
    {
        // 获取所有配置的镜像源
        $mirrors = $this->pvmMirrorConfig->getAllMirrors();

        if (empty($mirrors)) {
            return [];
        }

        // 使用测速器获取最优镜像源
        return $this->speedTest->getOptimalMirrors($mirrors);
    }
}
