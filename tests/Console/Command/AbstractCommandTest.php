<?php

namespace Tests\Console\Command;

use PHPUnit\Framework\TestCase;
use VersionManager\Core\Process\Process;

/**
 * 命令测试基类
 */
abstract class AbstractCommandTest extends TestCase
{
    /**
     * PVM可执行文件路径
     *
     * @var string
     */
    protected $pvmBin;

    /**
     * 测试前准备
     */
    protected function setUp(): void
    {
        // 设置PVM可执行文件路径
        $this->pvmBin = realpath(__DIR__ . '/../../../bin/pvm');

        // 确保PVM可执行文件存在
        $this->assertFileExists($this->pvmBin, 'PVM可执行文件不存在');

        // 初始化PVM环境
        $initProcess = new Process([$this->pvmBin, 'init']);
        $initProcess->run();
        $this->assertEquals(0, $initProcess->getExitCode(), 'PVM初始化失败');
    }

    /**
     * 执行PVM命令
     *
     * @param string $command 命令
     * @param array $args 参数
     * @param array $env 环境变量
     * @return Process 进程对象
     */
    protected function executePvmCommand($command, array $args = [], array $env = [])
    {
        $fullCommand = array_merge([$this->pvmBin, $command], $args);
        $process = new Process($fullCommand, null, $env);
        $process->setTimeout(300); // 5分钟超时
        $process->run();

        return $process;
    }

    /**
     * 验证命令输出包含指定文本
     *
     * @param Process $process 进程对象
     * @param string $text 要检查的文本
     * @param string $message 断言消息
     */
    protected function assertCommandOutputContains(Process $process, $text, $message = '')
    {
        $this->assertStringContainsString(
            $text,
            $process->getOutput() . $process->getErrorOutput(),
            $message ?: "命令输出应该包含 '$text'"
        );
    }

    /**
     * 验证命令输出不包含指定文本
     *
     * @param Process $process 进程对象
     * @param string $text 要检查的文本
     * @param string $message 断言消息
     */
    protected function assertCommandOutputNotContains(Process $process, $text, $message = '')
    {
        $this->assertStringNotContainsString(
            $text,
            $process->getOutput() . $process->getErrorOutput(),
            $message ?: "命令输出不应该包含 '$text'"
        );
    }

    /**
     * 验证命令执行成功
     *
     * @param Process $process 进程对象
     * @param string $message 断言消息
     */
    protected function assertCommandSuccessful(Process $process, $message = '')
    {
        $this->assertEquals(
            0,
            $process->getExitCode(),
            $message ?: "命令应该执行成功，但返回了错误: " . $process->getErrorOutput()
        );
    }

    /**
     * 验证命令执行失败
     *
     * @param Process $process 进程对象
     * @param string $message 断言消息
     */
    protected function assertCommandFailed(Process $process, $message = '')
    {
        $this->assertNotEquals(
            0,
            $process->getExitCode(),
            $message ?: "命令应该执行失败，但返回了成功"
        );
    }

    /**
     * 验证命令退出码
     *
     * @param Process $process 进程对象
     * @param int $exitCode 预期的退出码
     * @param string $message 断言消息
     */
    protected function assertCommandExitCode(Process $process, $exitCode, $message = '')
    {
        $this->assertEquals(
            $exitCode,
            $process->getExitCode(),
            $message ?: "命令应该返回退出码 $exitCode"
        );
    }

    /**
     * 验证文件存在
     *
     * @param string $path 文件路径
     * @param string $message 断言消息
     */
    protected function assertFileExistsAfterCommand($path, $message = '')
    {
        $this->assertFileExists(
            $path,
            $message ?: "文件 '$path' 应该存在"
        );
    }

    /**
     * 验证文件不存在
     *
     * @param string $path 文件路径
     * @param string $message 断言消息
     */
    protected function assertFileNotExistsAfterCommand($path, $message = '')
    {
        $this->assertFileDoesNotExist(
            $path,
            $message ?: "文件 '$path' 不应该存在"
        );
    }

    /**
     * 验证目录存在
     *
     * @param string $path 目录路径
     * @param string $message 断言消息
     */
    protected function assertDirectoryExistsAfterCommand($path, $message = '')
    {
        $this->assertDirectoryExists(
            $path,
            $message ?: "目录 '$path' 应该存在"
        );
    }

    /**
     * 验证目录不存在
     *
     * @param string $path 目录路径
     * @param string $message 断言消息
     */
    protected function assertDirectoryNotExistsAfterCommand($path, $message = '')
    {
        $this->assertDirectoryDoesNotExist(
            $path,
            $message ?: "目录 '$path' 不应该存在"
        );
    }
}
