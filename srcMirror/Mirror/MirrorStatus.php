<?php

namespace Mirror\Mirror;

/**
 * 镜像状态管理类
 */
class MirrorStatus
{
    /**
     * 获取镜像状态
     *
     * @return array
     */
    public function getStatus()
    {
        $phpDir = ROOT_DIR . '/data/php';
        $peclDir = ROOT_DIR . '/data/pecl';
        $extensionsDir = ROOT_DIR . '/data/extensions';
        $composerDir = ROOT_DIR . '/data/composer';
        
        $phpFiles = is_dir($phpDir) ? glob($phpDir . '/*.tar.gz') : [];
        $peclFiles = is_dir($peclDir) ? glob($peclDir . '/*.tgz') : [];
        
        $extensionFiles = [];
        if (is_dir($extensionsDir)) {
            $extensionDirs = glob($extensionsDir . '/*', GLOB_ONLYDIR);
            foreach ($extensionDirs as $dir) {
                $files = glob($dir . '/*.tar.gz');
                $extensionFiles = array_merge($extensionFiles, $files);
            }
        }
        
        $composerFiles = is_dir($composerDir) ? glob($composerDir . '/*.phar') : [];
        
        $allFiles = array_merge($phpFiles, $peclFiles, $extensionFiles, $composerFiles);
        $totalSize = 0;
        $lastUpdate = 0;
        
        foreach ($allFiles as $file) {
            $totalSize += filesize($file);
            $mtime = filemtime($file);
            if ($mtime > $lastUpdate) {
                $lastUpdate = $mtime;
            }
        }
        
        return [
            'php_files' => count($phpFiles),
            'pecl_files' => count($peclFiles),
            'extension_files' => count($extensionFiles),
            'composer_files' => count($composerFiles),
            'total_files' => count($allFiles),
            'total_size' => $totalSize,
            'last_update' => $lastUpdate,
        ];
    }

    /**
     * 获取PHP源码包列表
     *
     * @return array
     */
    public function getPhpList()
    {
        $phpDir = ROOT_DIR . '/data/php';
        $files = is_dir($phpDir) ? glob($phpDir . '/*.tar.gz') : [];
        
        $result = [];
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/php-([0-9.]+)\.tar\.gz/', $filename, $matches)) {
                $version = $matches[1];
                $majorVersion = explode('.', $version)[0] . '.' . explode('.', $version)[1];
                
                if (!isset($result[$majorVersion])) {
                    $result[$majorVersion] = [];
                }
                
                $result[$majorVersion][] = [
                    'version' => $version,
                    'filename' => $filename,
                    'size' => filesize($file),
                    'url' => '/php/' . $filename,
                ];
            }
        }
        
        return $result;
    }

    /**
     * 获取PECL扩展包列表
     *
     * @return array
     */
    public function getPeclList()
    {
        $peclDir = ROOT_DIR . '/data/pecl';
        $files = is_dir($peclDir) ? glob($peclDir . '/*.tgz') : [];
        
        $result = [];
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/([a-zA-Z0-9_]+)-([0-9.]+)\.tgz/', $filename, $matches)) {
                $extension = $matches[1];
                $version = $matches[2];
                
                if (!isset($result[$extension])) {
                    $result[$extension] = [];
                }
                
                $result[$extension][] = [
                    'version' => $version,
                    'filename' => $filename,
                    'size' => filesize($file),
                    'url' => '/pecl/' . $filename,
                ];
            }
        }
        
        return $result;
    }

    /**
     * 获取特定扩展源码列表
     *
     * @return array
     */
    public function getExtensionsList()
    {
        $extensionsDir = ROOT_DIR . '/data/extensions';
        $result = [];
        
        if (is_dir($extensionsDir)) {
            $extensionDirs = glob($extensionsDir . '/*', GLOB_ONLYDIR);
            
            foreach ($extensionDirs as $dir) {
                $extension = basename($dir);
                $files = glob($dir . '/*.tar.gz');
                
                $result[$extension] = [];
                
                foreach ($files as $file) {
                    $filename = basename($file);
                    
                    $result[$extension][] = [
                        'filename' => $filename,
                        'size' => filesize($file),
                        'url' => '/extensions/' . $extension . '/' . $filename,
                    ];
                }
            }
        }
        
        return $result;
    }

    /**
     * 获取Composer包列表
     *
     * @return array
     */
    public function getComposerList()
    {
        $composerDir = ROOT_DIR . '/data/composer';
        $files = is_dir($composerDir) ? glob($composerDir . '/*.phar') : [];
        
        $result = [];
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/composer-([0-9.]+)\.phar/', $filename, $matches)) {
                $version = $matches[1];
                
                $result[] = [
                    'version' => $version,
                    'filename' => $filename,
                    'size' => filesize($file),
                    'url' => '/composer/' . $filename,
                ];
            }
        }
        
        return $result;
    }
}
