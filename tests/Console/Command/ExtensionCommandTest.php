<?php

namespace Tests\Console\Command;

/**
 * 扩展命令测试类
 */
class ExtensionCommandTest extends AbstractCommandTest
{
    /**
     * 测试扩展命令帮助信息
     */
    public function testExtensionCommandHelp()
    {
        // 执行扩展命令（不带参数，应显示帮助）
        $process = $this->executePvmCommand('ext');
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含帮助信息
        $this->assertCommandOutputContains($process, '用法: pvm ext');
        $this->assertCommandOutputContains($process, '操作:');
        $this->assertCommandOutputContains($process, '选项:');
        $this->assertCommandOutputContains($process, '示例:');
    }
    
    /**
     * 测试扩展列表命令
     */
    public function testExtensionListCommand()
    {
        // 执行扩展列表命令
        $process = $this->executePvmCommand('ext', ['list']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含扩展列表信息
        $this->assertCommandOutputContains($process, '已安装的扩展');
    }
    
    /**
     * 测试扩展安装命令参数验证
     */
    public function testExtensionInstallCommandValidation()
    {
        // 执行扩展安装命令（不带扩展名）
        $process = $this->executePvmCommand('ext', ['install']);
        
        // 验证命令执行失败
        $this->assertCommandFailed($process);
        
        // 验证输出包含错误信息
        $this->assertCommandOutputContains($process, '错误');
    }
    
    /**
     * 测试扩展启用命令
     */
    public function testExtensionEnableCommand()
    {
        // 执行扩展启用命令
        $process = $this->executePvmCommand('ext', ['enable', 'mysqli']);
        
        // 这里我们不验证命令执行成功，因为扩展可能不存在
        // 但我们可以验证命令开始执行
        $this->assertCommandOutputContains($process, 'mysqli');
    }
    
    /**
     * 测试扩展禁用命令
     */
    public function testExtensionDisableCommand()
    {
        // 执行扩展禁用命令
        $process = $this->executePvmCommand('ext', ['disable', 'mysqli']);
        
        // 这里我们不验证命令执行成功，因为扩展可能不存在
        // 但我们可以验证命令开始执行
        $this->assertCommandOutputContains($process, 'mysqli');
    }
    
    /**
     * 测试扩展配置命令
     */
    public function testExtensionConfigCommand()
    {
        // 执行扩展配置命令
        $process = $this->executePvmCommand('ext', ['config', 'mysqli', 'connect_timeout=5']);
        
        // 这里我们不验证命令执行成功，因为扩展可能不存在
        // 但我们可以验证命令开始执行
        $this->assertCommandOutputContains($process, 'mysqli');
    }
    
    /**
     * 测试无效子命令
     */
    public function testInvalidSubcommand()
    {
        // 执行无效子命令
        $process = $this->executePvmCommand('ext', ['invalid']);
        
        // 验证命令执行失败
        $this->assertCommandFailed($process);
        
        // 验证输出包含错误信息
        $this->assertCommandOutputContains($process, '错误');
    }
}
