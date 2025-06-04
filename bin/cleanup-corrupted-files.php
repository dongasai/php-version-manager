#!/usr/bin/env php
<?php

/**
 * 清理损坏文件脚本
 * 
 * 扫描数据目录中的所有文件，检测并删除损坏的文件
 */

// 定义根目录
define('ROOT_DIR', dirname(__DIR__));

// 包含自动加载器
require ROOT_DIR . '/srcMirror/Autoloader.php';

// 注册自动加载器
Autoloader::register();

use Mirror\Utils\FileUtils;

class CorruptedFilesCleaner
{
    private $dataDir;
    private $deletedFiles = [];
    private $checkedFiles = 0;
    private $totalSize = 0;
    private $deletedSize = 0;

    public function __construct()
    {
        $this->dataDir = ROOT_DIR . '/data';
    }

    /**
     * 运行清理
     */
    public function run()
    {
        echo "开始扫描损坏文件...\n";
        echo "数据目录: {$this->dataDir}\n\n";

        if (!is_dir($this->dataDir)) {
            echo "错误: 数据目录不存在\n";
            return 1;
        }

        $this->scanDirectory($this->dataDir);

        echo "\n扫描完成!\n";
        echo "检查文件数: {$this->checkedFiles}\n";
        echo "删除文件数: " . count($this->deletedFiles) . "\n";
        echo "释放空间: " . $this->formatSize($this->deletedSize) . "\n";

        if (!empty($this->deletedFiles)) {
            echo "\n删除的文件列表:\n";
            foreach ($this->deletedFiles as $file) {
                echo "  - {$file['path']} ({$file['size']})\n";
            }
        }

        return 0;
    }

    /**
     * 扫描目录
     */
    private function scanDirectory($dir)
    {
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->scanDirectory($path);
            } elseif (is_file($path)) {
                $this->checkFile($path);
            }
        }
    }

    /**
     * 检查文件
     */
    private function checkFile($filePath)
    {
        $this->checkedFiles++;
        $fileSize = filesize($filePath);
        $this->totalSize += $fileSize;

        // 显示进度
        if ($this->checkedFiles % 10 === 0) {
            echo "\r检查进度: {$this->checkedFiles} 文件";
        }

        // 跳过特殊文件
        $fileName = basename($filePath);
        if ($fileName === 'resource.lock' || $fileName === '.gitkeep') {
            return;
        }

        // 验证文件完整性
        if (!$this->validateFile($filePath)) {
            $relativePath = str_replace($this->dataDir . '/', '', $filePath);
            
            $this->deletedFiles[] = [
                'path' => $relativePath,
                'size' => $this->formatSize($fileSize)
            ];
            
            $this->deletedSize += $fileSize;
            
            echo "\n删除损坏文件: {$relativePath} ({$this->formatSize($fileSize)})\n";
            unlink($filePath);
        }
    }

    /**
     * 验证文件完整性
     */
    private function validateFile($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileSize = filesize($filePath);

        // 检查文件大小
        if ($fileSize === 0) {
            return false;
        }

        // 根据文件类型进行验证
        switch ($extension) {
            case 'gz':
            case 'tgz':
                return $this->validateGzipFile($filePath);
            case 'tar':
                return $this->validateTarFile($filePath);
            case 'zip':
                return $this->validateZipFile($filePath);
            case 'phar':
                return $this->validatePharFile($filePath);
            default:
                return $this->validateGenericFile($filePath);
        }
    }

    /**
     * 验证 Gzip 文件
     */
    private function validateGzipFile($filePath)
    {
        $fileSize = filesize($filePath);
        
        // PHP 源码包应该至少有几MB
        if (strpos($filePath, '/php/') !== false && $fileSize < 1024 * 1024) {
            return false;
        }

        // 检查文件头
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 2);
        fclose($handle);

        // 检查 gzip 魔数
        if ($header !== "\x1f\x8b") {
            return false;
        }

        // 使用 gzfile 验证完整性
        $lines = @gzfile($filePath);
        if ($lines === false) {
            return false;
        }

        // 对于 PHP 源码包，检查解压后的大小
        if (strpos($filePath, '/php/') !== false) {
            $totalContent = implode('', $lines);
            if (strlen($totalContent) < 1024 * 100) { // 解压后应该至少有100KB
                return false;
            }
        }

        return true;
    }

    /**
     * 验证 Tar 文件
     */
    private function validateTarFile($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        fseek($handle, 257);
        $ustar = fread($handle, 5);
        fclose($handle);

        return $ustar === 'ustar';
    }

    /**
     * 验证 ZIP 文件
     */
    private function validateZipFile($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 2);
        fclose($handle);

        return $header === 'PK';
    }

    /**
     * 验证 PHAR 文件
     */
    private function validatePharFile($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 512);
        fclose($handle);

        return strpos($header, '<?php') === 0 || 
               strpos($header, '#!/usr/bin/env php') === 0 ||
               substr($header, 0, 2) === 'PK';
    }

    /**
     * 验证通用文件
     */
    private function validateGenericFile($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 512);
        fclose($handle);

        // 检查是否为 HTML 错误页面
        if (stripos($header, '<html') !== false || stripos($header, '<!doctype html') !== false) {
            return false;
        }

        // 检查是否包含错误信息
        $lowerHeader = strtolower($header);
        $errorPatterns = ['not found', '404', 'error', 'forbidden', 'access denied'];

        foreach ($errorPatterns as $pattern) {
            if (strpos($lowerHeader, $pattern) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 格式化文件大小
     */
    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// 运行清理器
$cleaner = new CorruptedFilesCleaner();
$exitCode = $cleaner->run();
exit($exitCode);
