<?php

namespace Tests\Console\Command;

/**
 * 安装命令测试类
 */
class InstallCommandTest extends AbstractCommandTest
{
    /**
     * 测试安装命令帮助信息
     */
    public function testInstallCommandHelp()
    {
        // 执行安装命令（不带参数，应显示帮助）
        $process = $this->executePvmCommand('install');
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含帮助信息
        $this->assertCommandOutputContains($process, '用法: pvm install');
        $this->assertCommandOutputContains($process, '选项:');
        $this->assertCommandOutputContains($process, '示例:');
    }
    
    /**
     * 测试安装命令参数验证
     */
    public function testInstallCommandValidation()
    {
        // 执行安装命令（带无效版本）
        $process = $this->executePvmCommand('install', ['invalid']);
        
        // 验证命令执行失败
        $this->assertCommandFailed($process);
        
        // 验证输出包含错误信息
        $this->assertCommandOutputContains($process, '错误');
    }
    
    /**
     * 测试安装命令选项
     */
    public function testInstallCommandOptions()
    {
        // 执行安装命令（带--help选项）
        $process = $this->executePvmCommand('install', ['--help']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含帮助信息
        $this->assertCommandOutputContains($process, '用法: pvm install');
        
        // 执行安装命令（带--from-source选项）
        $process = $this->executePvmCommand('install', ['7.4.33', '--from-source', '--no-verify']);
        
        // 这里我们不验证命令执行成功，因为实际安装可能需要很长时间
        // 但我们可以验证命令开始执行
        $this->assertCommandOutputContains($process, '从源码安装');
    }
    
    /**
     * 测试安装命令版本检查
     */
    public function testInstallCommandVersionCheck()
    {
        // 执行安装命令（检查版本）
        $process = $this->executePvmCommand('install', ['7.4.33', '--check-only']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含版本检查信息
        $this->assertCommandOutputContains($process, '版本检查');
    }
    
    /**
     * 测试安装命令依赖检查
     */
    public function testInstallCommandDependencyCheck()
    {
        // 执行安装命令（检查依赖）
        $process = $this->executePvmCommand('install', ['7.4.33', '--check-deps-only']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含依赖检查信息
        $this->assertCommandOutputContains($process, '依赖检查');
    }
}
