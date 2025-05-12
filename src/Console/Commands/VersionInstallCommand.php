<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionSwitcher;

/**
 * 版本安装命令类
 */
class VersionInstallCommand implements CommandInterface
{
    /**
     * 版本切换器
     *
     * @var VersionSwitcher
     */
    private $switcher;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->switcher = new VersionSwitcher();
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
            echo "错误: 请指定要安装的PHP版本" . PHP_EOL;
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        $version = array_shift($args);
        $options = $this->parseOptions($args);
        
        try {
            echo "正在安装PHP {$version}..." . PHP_EOL;
            $this->switcher->installVersion($version, $options);
            echo "PHP {$version} 安装成功" . PHP_EOL;
            
            // 如果指定了--use选项，则切换到该版本
            if (isset($options['use']) && $options['use']) {
                echo "正在切换到PHP {$version}..." . PHP_EOL;
                $this->switcher->switchVersion($version, isset($options['global']) && $options['global']);
                echo "已切换到PHP {$version}" . PHP_EOL;
            }
            
            return 0;
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . PHP_EOL;
            return 1;
        }
    }
    
    /**
     * 解析命令选项
     *
     * @param array $args 命令参数
     * @return array 选项数组
     */
    private function parseOptions(array $args)
    {
        $options = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $option = substr($arg, 2);
                
                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;
                }
            } elseif (strpos($arg, '-') === 0) {
                $option = substr($arg, 1);
                $options[$option] = true;
            } elseif (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $options[$key] = $value;
            }
        }
        
        return $options;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '安装PHP版本';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm install <版本> [选项]

安装PHP版本。

选项:
  --use                   安装后切换到该版本
  --global                全局切换版本（与--use一起使用）
  --force                 强制安装
  --mirror=<镜像名称>       使用指定的镜像下载
  --configure=<选项>       添加额外的配置选项，多个选项用逗号分隔

示例:
  pvm install 7.4.30
  pvm install 8.0.20 --use
  pvm install 8.1.10 --use --global
  pvm install 8.2.5 --mirror=aliyun
  pvm install 7.4.30 --configure=--with-pdo-mysql,--with-mysqli
USAGE;
    }
}
