<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\Security\SignatureVerifier;
use VersionManager\Core\Security\PermissionManager;
use VersionManager\Core\Security\SecurityUpdater;
use VersionManager\Core\VersionSwitcher;

/**
 * 安全命令类
 */
class SecurityCommand implements CommandInterface
{
    /**
     * 签名验证器
     *
     * @var SignatureVerifier
     */
    private $signatureVerifier;
    
    /**
     * 权限管理器
     *
     * @var PermissionManager
     */
    private $permissionManager;
    
    /**
     * 安全更新器
     *
     * @var SecurityUpdater
     */
    private $securityUpdater;
    
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
        $this->signatureVerifier = new SignatureVerifier();
        $this->permissionManager = new PermissionManager();
        $this->securityUpdater = new SecurityUpdater();
        $this->versionSwitcher = new VersionSwitcher();
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
            echo $this->getUsage() . PHP_EOL;
            return 0;
        }
        
        $subcommand = array_shift($args);
        
        switch ($subcommand) {
            case 'check':
                return $this->checkSecurity($args);
            case 'update':
                return $this->updateSecurity($args);
            case 'verify':
                return $this->verifySignature($args);
            case 'fix-permissions':
                return $this->fixPermissions($args);
            default:
                echo "错误: 未知的子命令 '{$subcommand}'" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 检查安全更新
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function checkSecurity(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $version = isset($args[0]) ? $args[0] : null;
        
        if ($version) {
            // 检查指定版本的安全更新
            $updateInfo = $this->securityUpdater->checkSecurityUpdate($version);
            
            if ($updateInfo) {
                echo "PHP版本 {$version} 有安全更新可用:" . PHP_EOL;
                echo "  最新版本: {$updateInfo['latest_version']}" . PHP_EOL;
                
                if (!empty($updateInfo['security_fixes'])) {
                    echo "  安全修复:" . PHP_EOL;
                    foreach ($updateInfo['security_fixes'] as $fix) {
                        echo "    - {$fix}" . PHP_EOL;
                    }
                }
                
                if (!empty($updateInfo['release_date'])) {
                    echo "  发布日期: {$updateInfo['release_date']}" . PHP_EOL;
                }
                
                if (!empty($updateInfo['update_url'])) {
                    echo "  更新公告: {$updateInfo['update_url']}" . PHP_EOL;
                }
            } else {
                echo "PHP版本 {$version} 没有安全更新可用" . PHP_EOL;
            }
        } else {
            // 检查所有已安装版本的安全更新
            $updates = $this->securityUpdater->checkAllSecurityUpdates();
            
            if (empty($updates)) {
                echo "所有已安装的PHP版本都是最新的" . PHP_EOL;
                return 0;
            }
            
            echo "以下PHP版本有安全更新可用:" . PHP_EOL;
            
            foreach ($updates as $version => $updateInfo) {
                echo "PHP {$version} -> {$updateInfo['latest_version']}" . PHP_EOL;
                
                if (!empty($updateInfo['security_fixes'])) {
                    echo "  安全修复:" . PHP_EOL;
                    foreach ($updateInfo['security_fixes'] as $fix) {
                        echo "    - {$fix}" . PHP_EOL;
                    }
                }
            }
        }
        
        return 0;
    }
    
    /**
     * 应用安全更新
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function updateSecurity(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取PHP版本
        $version = isset($args[0]) ? $args[0] : null;
        
        if ($version) {
            // 更新指定版本
            try {
                $success = $this->securityUpdater->applySecurityUpdate($version);
                
                if ($success) {
                    echo "PHP版本 {$version} 已成功更新" . PHP_EOL;
                } else {
                    echo "PHP版本 {$version} 没有安全更新可用" . PHP_EOL;
                }
            } catch (\Exception $e) {
                echo "错误: " . $e->getMessage() . PHP_EOL;
                return 1;
            }
        } else {
            // 更新所有版本
            $results = $this->securityUpdater->applyAllSecurityUpdates();
            
            if (empty($results)) {
                echo "所有已安装的PHP版本都是最新的" . PHP_EOL;
                return 0;
            }
            
            echo "安全更新结果:" . PHP_EOL;
            
            foreach ($results as $version => $result) {
                if ($result['success']) {
                    echo "PHP {$version}: {$result['message']}" . PHP_EOL;
                } else {
                    echo "PHP {$version}: 失败 - {$result['message']}" . PHP_EOL;
                }
            }
        }
        
        return 0;
    }
    
    /**
     * 验证签名
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function verifySignature(array $args)
    {
        if (count($args) < 1) {
            echo "错误: 请指定要验证的文件" . PHP_EOL;
            return 1;
        }
        
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取文件路径
        $filePath = $args[0];
        
        // 获取验证类型
        $type = isset($options['type']) ? $options['type'] : 'php';
        
        // 获取版本
        $version = isset($options['version']) ? $options['version'] : '';
        
        // 获取扩展名称
        $extension = isset($options['extension']) ? $options['extension'] : '';
        
        try {
            if ($type === 'php') {
                $success = $this->signatureVerifier->verifyPhpSignature($filePath, $version);
            } elseif ($type === 'extension') {
                $success = $this->signatureVerifier->verifyExtensionSignature($filePath, $extension, $version);
            } else {
                echo "错误: 未知的验证类型 '{$type}'" . PHP_EOL;
                return 1;
            }
            
            if ($success) {
                echo "签名验证成功" . PHP_EOL;
            } else {
                echo "签名验证失败" . PHP_EOL;
                return 1;
            }
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
            return 1;
        }
        
        return 0;
    }
    
    /**
     * 修复权限
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    private function fixPermissions(array $args)
    {
        // 解析选项
        $options = $this->parseOptions($args);
        
        // 获取目录路径
        $dirPath = isset($args[0]) ? $args[0] : getenv('HOME') . '/.pvm';
        
        // 检查目录是否存在
        if (!is_dir($dirPath)) {
            echo "错误: 目录 '{$dirPath}' 不存在" . PHP_EOL;
            return 1;
        }
        
        // 修复权限
        echo "修复目录 '{$dirPath}' 的权限..." . PHP_EOL;
        
        $recursive = isset($options['recursive']) ? $options['recursive'] : true;
        
        if ($recursive) {
            $success = $this->permissionManager->setSecurePermissionsRecursive($dirPath);
        } else {
            $success = $this->permissionManager->setSecureDirPermission($dirPath);
        }
        
        if ($success) {
            echo "权限修复成功" . PHP_EOL;
        } else {
            echo "权限修复失败" . PHP_EOL;
            return 1;
        }
        
        return 0;
    }
    
    /**
     * 解析选项
     *
     * @param array $args 命令参数
     * @return array
     */
    private function parseOptions(array &$args)
    {
        $options = [];
        $newArgs = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                
                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;
                }
            } else {
                $newArgs[] = $arg;
            }
        }
        
        $args = $newArgs;
        return $options;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '管理安全相关功能';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm security <子命令> [选项] [参数]...

管理安全相关功能。

子命令:
  check [版本]             检查安全更新
  update [版本]            应用安全更新
  verify <文件>            验证文件签名
  fix-permissions [目录]   修复权限

选项:
  --type=<类型>            验证类型，可以是php或extension，默认为php
  --version=<版本>         PHP版本或扩展版本
  --extension=<扩展>       扩展名称
  --recursive              递归修复权限，默认为true

示例:
  pvm security check
  pvm security check 7.4.33
  pvm security update
  pvm security update 7.4.33
  pvm security verify php-7.4.33.tar.gz --type=php --version=7.4.33
  pvm security fix-permissions
  pvm security fix-permissions ~/.pvm/versions/7.4.33
USAGE;
    }
}
