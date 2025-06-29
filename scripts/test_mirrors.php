<?php
/**
 * PVM镜像站测试脚本 - PHP版本
 * 测试一个PHP版本的完整下载和解压过程
 */

// 尝试加载自动加载文件
$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../src/autoload.php',
];

foreach ($autoloadFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
        break;
    }
}

class MirrorTester
{
    private $testVersion = '8.2.17';
    private $tempDir;

    // 测试的镜像源
    private $mirrors = [
        'pvm-mirror-local' => 'http://localhost:34403/php',
        'pvm-mirror-remote' => 'http://pvm.2sxo.com/php',
        'official' => 'https://www.php.net/distributions',
    ];

    public function __construct()
    {
        $this->tempDir = sys_get_temp_dir() . '/pvm_mirror_test_' . time();

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * 简单的日志方法
     */
    private function log($message, $level = 'info')
    {
        $colors = [
            'info' => "\033[0;34m",    // 蓝色
            'success' => "\033[0;32m", // 绿色
            'warning' => "\033[1;33m", // 黄色
            'error' => "\033[0;31m",   // 红色
            'reset' => "\033[0m"       // 重置
        ];

        $color = $colors[$level] ?? $colors['info'];
        echo $color . $message . $colors['reset'] . "\n";
    }
    
    public function __destruct()
    {
        // 清理临时目录
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }
    
    /**
     * 运行所有测试
     */
    public function runTests()
    {
        $this->log("=== PVM镜像站测试开始 ===", 'info');
        $this->log("测试PHP版本: {$this->testVersion}", 'info');
        $this->log("临时目录: {$this->tempDir}", 'info');
        echo "\n";

        $results = [];

        foreach ($this->mirrors as $name => $baseUrl) {
            $this->log("测试镜像源: {$name} ({$baseUrl})", 'info');
            $result = $this->testMirror($name, $baseUrl);
            $results[$name] = $result;
            echo "\n" . str_repeat('-', 60) . "\n";
        }

        $this->showSummary($results);
        $this->generateFixScript($results);
    }
    
    /**
     * 测试单个镜像源
     */
    private function testMirror($name, $baseUrl)
    {
        $filename = "php-{$this->testVersion}.tar.gz";
        $url = $baseUrl . '/' . $filename;
        $localFile = $this->tempDir . '/' . $filename;
        
        $result = [
            'name' => $name,
            'url' => $url,
            'available' => false,
            'downloadable' => false,
            'extractable' => false,
            'error' => null,
            'file_size' => 0,
            'download_time' => 0
        ];
        
        try {
            // 1. 检查文件是否存在
            $this->log("1. 检查文件可用性: {$url}");
            if (!$this->checkFileExists($url)) {
                $result['error'] = '文件不存在或无法访问';
                $this->log("✗ 文件不可用", 'error');
                return $result;
            }

            $result['available'] = true;
            $this->log("✓ 文件可用", 'success');

            // 2. 下载文件
            $this->log("2. 开始下载文件...");
            $startTime = microtime(true);

            if (!$this->downloadFile($url, $localFile)) {
                $result['error'] = '下载失败';
                $this->log("✗ 下载失败", 'error');
                return $result;
            }

            $result['downloadable'] = true;
            $result['download_time'] = microtime(true) - $startTime;
            $result['file_size'] = filesize($localFile);

            $this->log("✓ 下载成功", 'success');
            $this->log("文件大小: " . $this->formatBytes($result['file_size']));
            $this->log("下载耗时: " . number_format($result['download_time'], 2) . "秒");

            // 3. 验证文件格式
            $this->log("3. 验证文件格式...");
            if (!$this->verifyFileFormat($localFile)) {
                $result['error'] = '文件格式错误，可能下载了错误页面';
                $this->log("✗ 文件格式验证失败", 'error');
                $this->showFileContent($localFile);
                return $result;
            }

            $this->log("✓ 文件格式正确 (gzip压缩包)", 'success');

            // 4. 尝试解压
            $this->log("4. 测试解压...");
            if (!$this->testExtraction($localFile)) {
                $result['error'] = '解压失败';
                $this->log("✗ 解压失败", 'error');
                return $result;
            }

            $result['extractable'] = true;
            $this->log("✓ 解压测试成功", 'success');

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            $this->log("测试异常: " . $e->getMessage(), 'error');
        }
        
        return $result;
    }
    
    /**
     * 检查文件是否存在
     */
    private function checkFileExists($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    /**
     * 下载文件
     */
    private function downloadFile($url, $localFile)
    {
        $ch = curl_init();
        $fp = fopen($localFile, 'w+');
        
        if (!$fp) {
            return false;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5分钟超时
        curl_setopt($ch, CURLOPT_USERAGENT, 'PVM Mirror Tester/1.0');
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        fclose($fp);
        
        return $result !== false && $httpCode === 200 && filesize($localFile) > 0;
    }
    
    /**
     * 验证文件格式
     */
    private function verifyFileFormat($file)
    {
        if (!file_exists($file) || filesize($file) < 100) {
            return false;
        }
        
        // 检查文件头是否为gzip格式
        $handle = fopen($file, 'rb');
        $header = fread($handle, 10);
        fclose($handle);
        
        // gzip文件头: 1f 8b
        return substr($header, 0, 2) === "\x1f\x8b";
    }
    
    /**
     * 测试解压
     */
    private function testExtraction($file)
    {
        $extractDir = $this->tempDir . '/extract_test';
        if (!is_dir($extractDir)) {
            mkdir($extractDir, 0755, true);
        }
        
        // 使用tar命令测试解压前几个文件
        $command = "cd " . escapeshellarg($extractDir) . " && tar -tzf " . escapeshellarg($file) . " | head -5";
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);
        
        return $returnCode === 0 && !empty($output);
    }
    
    /**
     * 显示文件内容（用于调试）
     */
    private function showFileContent($file)
    {
        if (filesize($file) > 0) {
            $this->log("文件内容预览（前500字节）:");
            $content = file_get_contents($file, false, null, 0, 500);
            echo "----------------------------------------\n";
            echo $content . "\n";
            echo "----------------------------------------\n";
        }
    }
    
    /**
     * 显示测试总结
     */
    private function showSummary($results)
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        $this->log("=== 测试总结 ===");

        foreach ($results as $result) {
            $status = $result['extractable'] ? '✓ 完全可用' :
                     ($result['downloadable'] ? '⚠ 下载可用但有问题' :
                     ($result['available'] ? '⚠ 文件存在但无法下载' : '✗ 不可用'));

            echo sprintf("%-15s: %s\n", $result['name'], $status);

            if ($result['error']) {
                echo sprintf("%-15s  错误: %s\n", '', $result['error']);
            }

            if ($result['file_size'] > 0) {
                echo sprintf("%-15s  大小: %s, 耗时: %.2fs\n", '',
                    $this->formatBytes($result['file_size']),
                    $result['download_time']);
            }
        }
    }
    
    /**
     * 生成修复脚本
     */
    private function generateFixScript($results)
    {
        $workingMirrors = array_filter($results, function($result) {
            return $result['extractable'];
        });
        
        $brokenMirrors = array_filter($results, function($result) {
            return !$result['extractable'];
        });
        
        if (!empty($brokenMirrors)) {
            echo "\n" . str_repeat('=', 60) . "\n";
            $this->log("发现问题镜像源，建议修复:", 'warning');

            foreach ($brokenMirrors as $result) {
                echo "- {$result['name']}: {$result['error']}\n";
            }

            if (!empty($workingMirrors)) {
                echo "\n可用的镜像源:\n";
                foreach ($workingMirrors as $result) {
                    echo "- {$result['name']}: 完全可用\n";
                }
            }

            echo "\n建议执行以下命令修复:\n";
            echo "php bin/pvm mirror disable  # 禁用镜像源，使用官方源\n";
        } else {
            $this->log("所有镜像源都工作正常！", 'success');
        }
    }
    
    /**
     * 格式化字节数
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 递归删除目录
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

// 运行测试
if (php_sapi_name() === 'cli') {
    $tester = new MirrorTester();
    $tester->runTests();
}
