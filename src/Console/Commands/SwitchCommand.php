<?php

namespace VersionManager\Console\Commands;

use VersionManager\Console\CommandInterface;
use VersionManager\Core\VersionSwitcher;

/**
 * 临时切换PHP版本命令类
 */
class SwitchCommand implements CommandInterface
{
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
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args)
    {
        // 如果没有参数，显示帮助信息
        if (empty($args)) {
            echo $this->getUsage() . PHP_EOL;
            return 1;
        }
        
        // 获取版本
        $version = $args[0];
        
        // 检查版本是否已安装
        if (!$this->versionSwitcher->isVersionInstalled($version)) {
            echo "错误: PHP版本 {$version} 未安装" . PHP_EOL;
            
            // 显示已安装的版本
            $installedVersions = $this->versionSwitcher->getInstalledVersions();
            if (!empty($installedVersions)) {
                echo "已安装的PHP版本:" . PHP_EOL;
                foreach ($installedVersions as $installedVersion) {
                    echo "  " . $installedVersion . PHP_EOL;
                }
            }
            
            return 1;
        }
        
        // 获取PHP二进制文件路径
        $phpBin = $this->versionSwitcher->getBinaryPath($version);
        
        // 获取PHP版本信息
        $phpVersion = trim(shell_exec("{$phpBin} -r \"echo PHP_VERSION;\""));
        
        // 生成临时切换的命令
        $exportCommand = "export PATH=\"" . dirname($phpBin) . ":$PATH\"";
        
        // 显示如何临时切换版本的说明
        echo "要在当前终端会话中临时切换到PHP {$phpVersion}，请执行以下命令:" . PHP_EOL;
        echo PHP_EOL;
        echo $exportCommand . PHP_EOL;
        echo PHP_EOL;
        echo "或者，您可以使用以下命令直接执行:" . PHP_EOL;
        echo PHP_EOL;
        echo "eval \$(pvm switch {$version})" . PHP_EOL;
        echo PHP_EOL;
        echo "注意: 此更改仅对当前终端会话有效，关闭终端后将恢复到默认PHP版本" . PHP_EOL;
        
        // 检查是否使用了--eval选项
        if (in_array('--eval', $args) || in_array('-e', $args)) {
            // 直接输出export命令，用于eval执行
            echo $exportCommand;
        }
        
        return 0;
    }
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return '临时切换PHP版本（仅对当前终端会话有效）';
    }
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage()
    {
        return <<<USAGE
用法: pvm switch <版本> [选项]

临时切换PHP版本（仅对当前终端会话有效）。

参数:
  <版本>                  要切换到的PHP版本

选项:
  --eval, -e              输出可直接用于eval执行的命令

示例:
  pvm switch 7.4.30       # 显示如何临时切换到PHP 7.4.30的说明
  eval \$(pvm switch 7.4.30 --eval)  # 临时切换到PHP 7.4.30
USAGE;
    }
}
