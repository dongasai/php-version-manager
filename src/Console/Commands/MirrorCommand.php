<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\Config\MirrorConfig;

/**
 * 镜像配置命令类
 */
class MirrorCommand implements CommandInterface
{
    /**
     * 镜像配置
     *
     * @var MirrorConfig
     */
    private $config;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->config = new MirrorConfig();
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
            return $this->showMirrors();
        }
        
        $action = array_shift($args);
        
        switch ($action) {
            case 'list':
                return $this->showMirrors();
            case 'add':
                return $this->addMirror($args);
            case 'remove':
                return $this->removeMirror($args);
            case 'set':
                return $this->setDefaultMirror($args);
            default:
                echo "错误: 未知的操作 {$action}" . PHP_EOL;
                echo $this->getUsage() . PHP_EOL;
                return 1;
        }
    }
    
    /**
     * 显示镜像列表
     *
     * @return int
     */
    private function showMirrors()
    {
        // 显示PHP镜像
        echo "PHP镜像:" . PHP_EOL;
        $defaultPhpMirror = $this->config->getDefaultPhpMirrorName();
        $phpMirrors = $this->config->getAllPhpMirrors();
        
        foreach ($phpMirrors as $name => $url) {
            $default = ($name === $defaultPhpMirror) ? ' [默认]' : '';
            echo "  * {$name}: {$url}{$default}" . PHP_EOL;
        }
        
        echo PHP_EOL;
        
        // 显示PECL镜像
        echo "PECL镜像:" . PHP_EOL;
        $defaultPeclMirror = $this->config->getDefaultPeclMirrorName();
        $peclMirrors = $this->config->getAllPeclMirrors();
        
        foreach ($peclMirrors as $name => $url) {
            $default = ($name === $defaultPeclMirror) ? ' [默认]' : '';
            echo "  * {$name}: {$url}{$default}" . PHP_EOL;
        }
        
        echo PHP_EOL;
        
        // 显示扩展镜像
        echo "扩展镜像:" . PHP_EOL;
        $extensions = ['redis', 'memcached', 'xdebug'];
        
        foreach ($extensions as $extension) {
            $defaultExtensionMirror = $this->config->getDefaultExtensionMirrorName($extension);
            $extensionMirrors = $this->config->getAllExtensionMirrors($extension);
            
            echo "  {$extension}:" . PHP_EOL;
            foreach ($extensionMirrors as $name => $url) {
                $default = ($name === $defaultExtensionMirror) ? ' [默认]' : '';
                echo "    * {$name}: {$url}{$default}" . PHP_EOL;
            }
        }
        
        echo PHP_EOL;
        
        // 显示Composer镜像
        echo "Composer镜像:" . PHP_EOL;
        $defaultComposerMirror = $this->config->getDefaultComposerMirrorName();
        $composerMirrors = $this->config->getAllComposerMirrors();
        
        foreach ($composerMirrors as $name => $url) {
            $default = ($name === $defaultComposerMirror) ? ' [默认]' : '';
            echo "  * {$name}: {$url}{$default}" . PHP_EOL;
        }
        
        return 0;
    }
    
    /**
     * 添加镜像
     *
     * @param array $args 命令参数
     * @return int
     */
    private function addMirror(array $args)
    {
        if (count($args) < 3) {
            echo "错误: 参数不足" . PHP_EOL;
            echo "用法: pvm mirror add <类型> <名称> <地址>" . PHP_EOL;
            return 1;
        }
        
        $type = array_shift($args);
        $name = array_shift($args);
        $url = array_shift($args);
        
        // 如果是扩展镜像，则需要额外的扩展名称参数
        if ($type === 'extension') {
            if (empty($args)) {
                echo "错误: 缺少扩展名称参数" . PHP_EOL;
                echo "用法: pvm mirror add extension <名称> <地址> <扩展名称>" . PHP_EOL;
                return 1;
            }
            
            $extension = array_shift($args);
            $result = $this->config->addExtensionMirror($extension, $name, $url);
            
            if ($result) {
                echo "成功添加扩展 {$extension} 的镜像 {$name}: {$url}" . PHP_EOL;
                return 0;
            } else {
                echo "添加扩展 {$extension} 的镜像 {$name} 失败" . PHP_EOL;
                return 1;
            }
        }
        
        // 其他类型的镜像
        switch ($type) {
            case 'php':
                $result = $this->config->addPhpMirror($name, $url);
                break;
            case 'pecl':
                $result = $this->config->addPeclMirror($name, $url);
                break;
            case 'composer':
                $result = $this->config->addComposerMirror($name, $url);
                break;
            default:
                echo "错误: 未知的镜像类型 {$type}" . PHP_EOL;
                return 1;
        }
        
        if ($result) {
            echo "成功添加{$type}镜像 {$name}: {$url}" . PHP_EOL;
            return 0;
        } else {
            echo "添加{$type}镜像 {$name} 失败" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 删除镜像
     *
     * @param array $args 命令参数
     * @return int
     */
    private function removeMirror(array $args)
    {
        if (count($args) < 2) {
            echo "错误: 参数不足" . PHP_EOL;
            echo "用法: pvm mirror remove <类型> <名称>" . PHP_EOL;
            return 1;
        }
        
        $type = array_shift($args);
        $name = array_shift($args);
        
        // 如果是扩展镜像，则需要额外的扩展名称参数
        if ($type === 'extension') {
            if (empty($args)) {
                echo "错误: 缺少扩展名称参数" . PHP_EOL;
                echo "用法: pvm mirror remove extension <名称> <扩展名称>" . PHP_EOL;
                return 1;
            }
            
            $extension = array_shift($args);
            $result = $this->config->removeExtensionMirror($extension, $name);
            
            if ($result) {
                echo "成功删除扩展 {$extension} 的镜像 {$name}" . PHP_EOL;
                return 0;
            } else {
                echo "删除扩展 {$extension} 的镜像 {$name} 失败" . PHP_EOL;
                return 1;
            }
        }
        
        // 其他类型的镜像
        switch ($type) {
            case 'php':
                $result = $this->config->removePhpMirror($name);
                break;
            case 'pecl':
                $result = $this->config->removePeclMirror($name);
                break;
            case 'composer':
                $result = $this->config->removeComposerMirror($name);
                break;
            default:
                echo "错误: 未知的镜像类型 {$type}" . PHP_EOL;
                return 1;
        }
        
        if ($result) {
            echo "成功删除{$type}镜像 {$name}" . PHP_EOL;
            return 0;
        } else {
            echo "删除{$type}镜像 {$name} 失败" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 设置默认镜像
     *
     * @param array $args 命令参数
     * @return int
     */
    private function setDefaultMirror(array $args)
    {
        if (count($args) < 2) {
            echo "错误: 参数不足" . PHP_EOL;
            echo "用法: pvm mirror set <类型> <名称>" . PHP_EOL;
            return 1;
        }
        
        $type = array_shift($args);
        $name = array_shift($args);
        
        // 如果是扩展镜像，则需要额外的扩展名称参数
        if ($type === 'extension') {
            if (empty($args)) {
                echo "错误: 缺少扩展名称参数" . PHP_EOL;
                echo "用法: pvm mirror set extension <名称> <扩展名称>" . PHP_EOL;
                return 1;
            }
            
            $extension = array_shift($args);
            $result = $this->config->setDefaultExtensionMirror($extension, $name);
            
            if ($result) {
                echo "成功设置扩展 {$extension} 的默认镜像为 {$name}" . PHP_EOL;
                return 0;
            } else {
                echo "设置扩展 {$extension} 的默认镜像失败" . PHP_EOL;
                return 1;
            }
        }
        
        // 其他类型的镜像
        switch ($type) {
            case 'php':
                $result = $this->config->setDefaultPhpMirror($name);
                break;
            case 'pecl':
                $result = $this->config->setDefaultPeclMirror($name);
                break;
            case 'composer':
                $result = $this->config->setDefaultComposerMirror($name);
                break;
            default:
                echo "错误: 未知的镜像类型 {$type}" . PHP_EOL;
                return 1;
        }
        
        if ($result) {
            echo "成功设置{$type}默认镜像为 {$name}" . PHP_EOL;
            return 0;
        } else {
            echo "设置{$type}默认镜像失败" . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '管理下载镜像配置';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm mirror [操作] [参数]...

管理下载镜像配置。

操作:
  list                    显示镜像列表（默认操作）
  add <类型> <名称> <地址>   添加镜像
  remove <类型> <名称>      删除镜像
  set <类型> <名称>         设置默认镜像

类型:
  php                     PHP下载镜像
  pecl                    PECL扩展下载镜像
  extension               特定扩展下载镜像（需要额外的扩展名称参数）
  composer                Composer下载镜像

示例:
  pvm mirror list
  pvm mirror add php aliyun https://mirrors.aliyun.com/php
  pvm mirror add pecl ustc https://mirrors.ustc.edu.cn/pecl
  pvm mirror add extension github https://github.com/phpredis/phpredis/archive/refs/tags redis
  pvm mirror remove php aliyun
  pvm mirror set php aliyun
  pvm mirror set extension github redis
USAGE;
    }
}
