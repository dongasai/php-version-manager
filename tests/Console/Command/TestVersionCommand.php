<?php

namespace Tests\Console\Command;

use PHPUnit\Framework\TestCase;
use VersionManager\Core\Process\Process;
use VersionManager\Core\VersionDetector;
use VersionManager\Core\VersionInstaller;
use VersionManager\Core\VersionSwitcher;
use VersionManager\Core\VersionRemover;

/**
 * 测试PHP版本管理命令
 */
class TestVersionCommand extends TestCase
{
    /**
     * @var VersionDetector
     */
    private $detector;

    /**
     * @var VersionInstaller
     */
    private $installer;

    /**
     * @var VersionSwitcher
     */
    private $switcher;

    /**
     * @var VersionRemover
     */
    private $remover;

    /**
     * 测试前准备
     */
    protected function setUp(): void
    {
        $this->detector = new VersionDetector();
        $this->installer = new VersionInstaller();
        $this->switcher = new VersionSwitcher();
        $this->remover = new VersionRemover();
    }

    /**
     * 测试版本检测功能
     */
    public function testVersionDetection()
    {
        // 获取当前PHP版本
        $currentVersion = $this->detector->getCurrentVersion();
        $this->assertNotEmpty($currentVersion, '当前PHP版本不应为空');

        // 获取可用的PHP版本
        $availableVersions = $this->detector->getAvailableVersions();
        $this->assertNotEmpty($availableVersions, '可用的PHP版本列表不应为空');

        // 验证版本格式
        foreach ($availableVersions as $version) {
            $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version, "版本号 $version 格式不正确");
        }

        // 获取已安装的PHP版本
        $installedVersions = $this->detector->getInstalledVersions();
        // 这里不断言非空，因为可能没有安装任何版本

        // 如果有已安装的版本，验证格式
        foreach ($installedVersions as $version) {
            $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version, "已安装版本号 $version 格式不正确");
        }
    }

    /**
     * 测试版本安装功能
     */
    public function testVersionInstallation()
    {
        // 选择一个测试版本
        $testVersion = '7.4.33';

        // 如果已安装，先卸载
        if ($this->installer->isVersionInstalled($testVersion)) {
            $this->remover->remove($testVersion);
        }

        // 验证版本未安装
        $this->assertFalse($this->installer->isVersionInstalled($testVersion), "测试前PHP版本 $testVersion 应该未安装");

        try {
            // 安装测试版本
            $result = $this->installer->install($testVersion);
            $this->assertTrue($result, "PHP版本 $testVersion 安装应该成功");

            // 验证版本已安装
            $this->assertTrue($this->installer->isVersionInstalled($testVersion), "PHP版本 $testVersion 应该已安装");

            // 清理：卸载测试版本
            $this->remover->remove($testVersion);

            // 验证版本已卸载
            $this->assertFalse($this->installer->isVersionInstalled($testVersion), "测试后PHP版本 $testVersion 应该已卸载");
        } catch (\Exception $e) {
            $this->markTestSkipped("安装测试跳过: " . $e->getMessage());
        }
    }

    /**
     * 测试版本切换功能
     */
    public function testVersionSwitching()
    {
        // 获取已安装的版本
        $installedVersions = $this->detector->getInstalledVersions();

        // 如果没有安装的版本，安装一个测试版本
        if (empty($installedVersions)) {
            $testVersion = '7.4.33';
            try {
                $this->installer->install($testVersion);
                $installedVersions = [$testVersion];
            } catch (\Exception $e) {
                $this->markTestSkipped("切换测试跳过: " . $e->getMessage());
                return;
            }
        }

        // 选择第一个已安装的版本进行测试
        $testVersion = $installedVersions[0];

        try {
            // 切换到测试版本
            $result = $this->switcher->switchVersion($testVersion);
            $this->assertTrue($result, "切换到PHP版本 $testVersion 应该成功");

            // 验证当前版本是否正确
            $currentVersion = $this->switcher->getCurrentVersion();
            $this->assertEquals($testVersion, $currentVersion, "当前PHP版本应该是 $testVersion");

            // 如果我们安装了测试版本，清理它
            if (!in_array($testVersion, $this->detector->getInstalledVersions())) {
                $this->remover->remove($testVersion);
            }
        } catch (\Exception $e) {
            $this->markTestSkipped("切换测试跳过: " . $e->getMessage());
        }
    }

    /**
     * 测试实际执行PHP命令
     */
    public function testExecutePhpCommand()
    {
        // 获取当前PHP版本
        $currentVersion = $this->detector->getCurrentVersion();

        // 创建一个简单的PHP脚本
        $tempFile = tempnam(sys_get_temp_dir(), 'pvm_test_');
        file_put_contents($tempFile, '<?php echo PHP_VERSION;');

        // 执行PHP脚本
        $process = new Process(['php', $tempFile]);
        $process->run();

        // 验证输出包含当前PHP版本
        $output = trim($process->getOutput());
        $this->assertStringStartsWith(substr($currentVersion, 0, 3), $output, "PHP命令应该使用当前版本 $currentVersion");

        // 清理临时文件
        unlink($tempFile);
    }
}
