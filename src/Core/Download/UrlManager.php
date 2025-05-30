<?php

namespace VersionManager\Core\Download;

use VersionManager\Core\Config\PvmMirrorConfig;

/**
 * 下载地址管理类
 *
 * 简化的URL管理，只对几个主要官方源进行镜像适配：
 * - php.net
 * - pecl.php.net
 * - getcomposer.org
 * - github.com
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
     * 构造函数
     */
    public function __construct()
    {
        $this->pvmMirrorConfig = new PvmMirrorConfig();
    }

    /**
     * 转换URL，如果启用镜像则使用镜像地址，否则返回原地址
     *
     * @param string $originalUrl 原始URL
     * @return array URL列表（按优先级排序）
     */
    public function getDownloadUrls($originalUrl)
    {
        $urls = [];

        // 如果启用了PVM镜像源，优先使用镜像源
        if ($this->pvmMirrorConfig->isEnabled()) {
            $mirrorUrl = $this->convertToMirrorUrl($originalUrl);
            if ($mirrorUrl) {
                // 添加所有可用的镜像源
                foreach ($this->pvmMirrorConfig->getAllMirrors() as $mirror) {
                    $urls[] = str_replace($this->getMirrorBaseUrl(), $mirror, $mirrorUrl);
                }
            }
        }

        // 始终添加原始URL作为备用
        $urls[] = $originalUrl;

        return array_unique($urls);
    }

    /**
     * 获取PHP源码下载URL
     *
     * @param string $version PHP版本
     * @return array 下载URL列表（按优先级排序）
     */
    public function getPhpDownloadUrls($version)
    {
        $urls = [];

        // 如果启用了PVM镜像源，优先使用镜像源
        if ($this->pvmMirrorConfig->isEnabled()) {
            $mirrorUrl = $this->getMirrorBaseUrl() . '/php/php-' . $version . '.tar.gz';
            // 添加所有可用的镜像源
            foreach ($this->pvmMirrorConfig->getAllMirrors() as $mirror) {
                $urls[] = str_replace($this->getMirrorBaseUrl(), $mirror, $mirrorUrl);
            }
        }

        // 添加官方源
        $officialUrl = "https://www.php.net/distributions/php-{$version}.tar.gz";
        $urls[] = $officialUrl;

        // 为早期PHP版本添加museum源作为备用
        if ($this->isEarlyPhpVersion($version)) {
            $museumUrl = "https://museum.php.net/php5/php-{$version}.tar.gz";
            $urls[] = $museumUrl;
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
     * 检查是否为早期PHP版本（需要使用museum源）
     *
     * @param string $version PHP版本
     * @return bool
     */
    private function isEarlyPhpVersion($version)
    {
        // 解析版本号
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)/', $version, $matches)) {
            return false;
        }

        $major = (int)$matches[1];
        $minor = (int)$matches[2];
        $patch = (int)$matches[3];

        // PHP 5.4.0 - 5.4.44 的早期版本在官方源不可用
        if ($major == 5 && $minor == 4) {
            // 5.4.45是最后一个版本，在官方源可用
            return $patch < 45;
        }

        // PHP 5.3及更早版本
        if ($major < 5 || ($major == 5 && $minor < 4)) {
            return true;
        }

        // PHP 5.5.0 - 5.5.37 的早期版本也可能需要museum源
        if ($major == 5 && $minor == 5) {
            // 检查一些早期版本
            return $patch < 10;
        }

        return false;
    }
}
