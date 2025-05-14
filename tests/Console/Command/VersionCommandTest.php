<?php

namespace Tests\Console\Command;

/**
 * 版本命令测试类
 */
class VersionCommandTest extends AbstractCommandTest
{
    /**
     * 测试版本命令
     */
    public function testVersionCommand()
    {
        // 执行版本命令
        $process = $this->executePvmCommand('version');
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含当前PHP版本
        $this->assertCommandOutputContains($process, 'PHP');
    }
    
    /**
     * 测试版本列表命令
     */
    public function testVersionListCommand()
    {
        // 执行版本列表命令
        $process = $this->executePvmCommand('version', ['list']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含已安装的PHP版本
        $this->assertCommandOutputContains($process, '已安装的PHP版本');
    }
    
    /**
     * 测试可用版本命令
     */
    public function testVersionAvailableCommand()
    {
        // 执行可用版本命令
        $process = $this->executePvmCommand('version', ['available']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含可用的PHP版本
        $this->assertCommandOutputContains($process, '可用的PHP版本');
    }
    
    /**
     * 测试版本检查命令
     */
    public function testVersionCheckCommand()
    {
        // 执行版本检查命令
        $process = $this->executePvmCommand('version', ['check', '8.1.0']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含兼容性信息
        $this->assertCommandOutputContains($process, '兼容性');
    }
    
    /**
     * 测试版本依赖命令
     */
    public function testVersionDepsCommand()
    {
        // 执行版本依赖命令
        $process = $this->executePvmCommand('version', ['deps', '8.1.0']);
        
        // 验证命令执行成功
        $this->assertCommandSuccessful($process);
        
        // 验证输出包含依赖信息
        $this->assertCommandOutputContains($process, '依赖');
    }
    
    /**
     * 测试无效子命令
     */
    public function testInvalidSubcommand()
    {
        // 执行无效子命令
        $process = $this->executePvmCommand('version', ['invalid']);
        
        // 验证命令执行失败
        $this->assertCommandFailed($process);
        
        // 验证输出包含错误信息
        $this->assertCommandOutputContains($process, '错误');
    }
}
