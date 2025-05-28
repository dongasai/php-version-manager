<?php

namespace VersionManager\Console\Commands;

use VersionManager\Core\Config\PvmMirrorConfig;

/**
 * PVM镜像源管理命令
 */
class PvmMirrorCommand
{
    /**
     * PVM镜像配置
     *
     * @var PvmMirrorConfig
     */
    private $mirrorConfig;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->mirrorConfig = new PvmMirrorConfig();
    }
    
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        if (empty($args)) {
            return $this->showStatus();
        }
        
        $action = array_shift($args);
        
        switch ($action) {
            case 'status':
                return $this->showStatus();
                
            case 'enable':
                return $this->enableMirror();
                
            case 'disable':
                return $this->disableMirror();
                
            case 'set':
                return $this->setMirror($args);
                
            case 'add':
                return $this->addFallback($args);
                
            case 'remove':
                return $this->removeFallback($args);
                
            case 'test':
                return $this->testMirror($args);
                
            case 'config':
                return $this->showConfig();
                
            case 'help':
            case '--help':
            case '-h':
                return $this->showHelp();
                
            default:
                echo "错误: 未知的操作 '{$action}'" . PHP_EOL;
                echo "使用 'pvm pvm-mirror help' 查看帮助信息" . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 显示镜像状态
     *
     * @return int
     */
    private function showStatus()
    {
        echo "PVM镜像源状态:" . PHP_EOL;
        echo "=============" . PHP_EOL;
        
        $summary = $this->mirrorConfig->getConfigSummary();
        
        // 状态
        $status = $summary['enabled'] ? '已启用' : '已禁用';
        $statusColor = $summary['enabled'] ? "\033[32m" : "\033[31m";
        echo "状态: {$statusColor}{$status}\033[0m" . PHP_EOL;
        
        // 主镜像源
        echo "主镜像源: " . $summary['mirror_url'] . PHP_EOL;
        
        // 备用镜像源数量
        echo "备用镜像源: " . $summary['fallback_count'] . " 个" . PHP_EOL;
        
        // 配置信息
        echo "连接超时: " . $summary['timeout'] . " 秒" . PHP_EOL;
        echo "SSL验证: " . ($summary['verify_ssl'] ? '启用' : '禁用') . PHP_EOL;
        echo "自动回退: " . ($summary['auto_fallback'] ? '启用' : '禁用') . PHP_EOL;
        
        // 如果启用了镜像源，显示测试结果
        if ($summary['enabled']) {
            echo PHP_EOL . "连接测试:" . PHP_EOL;
            $testResult = $this->mirrorConfig->testMirror();
            
            if ($testResult['success']) {
                echo "\033[32m✓\033[0m 主镜像源连接正常 ({$testResult['response_time']}ms)" . PHP_EOL;
            } else {
                echo "\033[31m✗\033[0m 主镜像源连接失败: {$testResult['error']}" . PHP_EOL;
            }
        }
        
        return 0;
    }
    
    /**
     * 启用镜像源
     *
     * @return int
     */
    private function enableMirror()
    {
        if ($this->mirrorConfig->enable()) {
            echo "\033[32m✓\033[0m PVM镜像源已启用" . PHP_EOL;
            echo "所有下载将优先使用PVM镜像源" . PHP_EOL;
            return 0;
        } else {
            echo "\033[31m✗\033[0m 启用PVM镜像源失败" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 禁用镜像源
     *
     * @return int
     */
    private function disableMirror()
    {
        if ($this->mirrorConfig->disable()) {
            echo "\033[32m✓\033[0m PVM镜像源已禁用" . PHP_EOL;
            echo "所有下载将使用官方源" . PHP_EOL;
            return 0;
        } else {
            echo "\033[31m✗\033[0m 禁用PVM镜像源失败" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 设置主镜像源
     *
     * @param array $args 参数
     * @return int
     */
    private function setMirror(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定镜像源地址" . PHP_EOL;
            echo "用法: pvm pvm-mirror set <URL>" . PHP_EOL;
            return 1;
        }
        
        $url = $args[0];
        
        if ($this->mirrorConfig->setMirrorUrl($url)) {
            echo "\033[32m✓\033[0m 主镜像源已设置为: {$url}" . PHP_EOL;
            
            // 测试新镜像源
            echo "测试新镜像源连接..." . PHP_EOL;
            $testResult = $this->mirrorConfig->testMirror($url);
            
            if ($testResult['success']) {
                echo "\033[32m✓\033[0m 镜像源连接正常 ({$testResult['response_time']}ms)" . PHP_EOL;
            } else {
                echo "\033[33m⚠\033[0m 镜像源连接失败: {$testResult['error']}" . PHP_EOL;
                echo "建议检查镜像源地址是否正确" . PHP_EOL;
            }
            
            return 0;
        } else {
            echo "\033[31m✗\033[0m 设置镜像源失败: 无效的URL格式" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 添加备用镜像源
     *
     * @param array $args 参数
     * @return int
     */
    private function addFallback(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定备用镜像源地址" . PHP_EOL;
            echo "用法: pvm pvm-mirror add <URL>" . PHP_EOL;
            return 1;
        }
        
        $url = $args[0];
        
        if ($this->mirrorConfig->addFallbackMirror($url)) {
            echo "\033[32m✓\033[0m 备用镜像源已添加: {$url}" . PHP_EOL;
            return 0;
        } else {
            echo "\033[31m✗\033[0m 添加备用镜像源失败: 无效的URL格式" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 移除备用镜像源
     *
     * @param array $args 参数
     * @return int
     */
    private function removeFallback(array $args)
    {
        if (empty($args)) {
            echo "错误: 请指定要移除的备用镜像源地址" . PHP_EOL;
            echo "用法: pvm pvm-mirror remove <URL>" . PHP_EOL;
            return 1;
        }
        
        $url = $args[0];
        
        if ($this->mirrorConfig->removeFallbackMirror($url)) {
            echo "\033[32m✓\033[0m 备用镜像源已移除: {$url}" . PHP_EOL;
            return 0;
        } else {
            echo "\033[31m✗\033[0m 移除备用镜像源失败: 镜像源不存在" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 测试镜像源
     *
     * @param array $args 参数
     * @return int
     */
    private function testMirror(array $args)
    {
        $url = empty($args) ? null : $args[0];
        
        if ($url === null) {
            echo "测试所有镜像源:" . PHP_EOL;
            echo "===============" . PHP_EOL;
            
            // 测试主镜像源
            $mainUrl = $this->mirrorConfig->getMirrorUrl();
            echo "主镜像源: {$mainUrl}" . PHP_EOL;
            $result = $this->mirrorConfig->testMirror($mainUrl);
            $this->printTestResult($result);
            
            // 测试备用镜像源
            $fallbacks = $this->mirrorConfig->getFallbackMirrors();
            foreach ($fallbacks as $fallbackUrl) {
                echo PHP_EOL . "备用镜像源: {$fallbackUrl}" . PHP_EOL;
                $result = $this->mirrorConfig->testMirror($fallbackUrl);
                $this->printTestResult($result);
            }
        } else {
            echo "测试镜像源: {$url}" . PHP_EOL;
            $result = $this->mirrorConfig->testMirror($url);
            $this->printTestResult($result);
        }
        
        return 0;
    }
    
    /**
     * 打印测试结果
     *
     * @param array $result 测试结果
     */
    private function printTestResult(array $result)
    {
        if ($result['success']) {
            echo "\033[32m✓\033[0m 连接成功" . PHP_EOL;
            echo "  响应时间: {$result['response_time']}ms" . PHP_EOL;
            echo "  HTTP状态码: {$result['http_code']}" . PHP_EOL;
        } else {
            echo "\033[31m✗\033[0m 连接失败" . PHP_EOL;
            echo "  错误信息: {$result['error']}" . PHP_EOL;
            if ($result['http_code'] > 0) {
                echo "  HTTP状态码: {$result['http_code']}" . PHP_EOL;
            }
        }
    }
    
    /**
     * 显示详细配置
     *
     * @return int
     */
    private function showConfig()
    {
        echo "PVM镜像源详细配置:" . PHP_EOL;
        echo "=================" . PHP_EOL;
        
        $summary = $this->mirrorConfig->getConfigSummary();
        
        echo "启用状态: " . ($summary['enabled'] ? '是' : '否') . PHP_EOL;
        echo "主镜像源: " . $summary['mirror_url'] . PHP_EOL;
        
        $fallbacks = $this->mirrorConfig->getFallbackMirrors();
        echo "备用镜像源:" . PHP_EOL;
        if (empty($fallbacks)) {
            echo "  (无)" . PHP_EOL;
        } else {
            foreach ($fallbacks as $i => $url) {
                echo "  " . ($i + 1) . ". {$url}" . PHP_EOL;
            }
        }
        
        echo "连接超时: " . $summary['timeout'] . " 秒" . PHP_EOL;
        echo "SSL验证: " . ($summary['verify_ssl'] ? '启用' : '禁用') . PHP_EOL;
        echo "自动回退到官方源: " . ($summary['auto_fallback'] ? '启用' : '禁用') . PHP_EOL;
        
        return 0;
    }
    
    /**
     * 显示帮助信息
     *
     * @return int
     */
    private function showHelp()
    {
        echo <<<USAGE
PVM镜像源管理

用法: pvm pvm-mirror <操作> [参数]

操作:
  status                  显示镜像源状态（默认操作）
  enable                  启用PVM镜像源
  disable                 禁用PVM镜像源
  set <URL>               设置主镜像源地址
  add <URL>               添加备用镜像源
  remove <URL>            移除备用镜像源
  test [URL]              测试镜像源连接（不指定URL则测试所有）
  config                  显示详细配置
  help                    显示帮助信息

说明:
  PVM镜像源是统一的下载源，用于下载PHP源码、PECL扩展、Composer等。
  启用后，所有下载都会优先使用PVM镜像源，提高下载速度。
  如果镜像源不可用，会自动回退到官方源。

示例:
  pvm pvm-mirror status
  pvm pvm-mirror enable
  pvm pvm-mirror set http://pvm.2sxo.com
  pvm pvm-mirror add http://localhost:34403
  pvm pvm-mirror test
  pvm pvm-mirror test http://pvm.2sxo.com

USAGE;
        
        return 0;
    }
}
