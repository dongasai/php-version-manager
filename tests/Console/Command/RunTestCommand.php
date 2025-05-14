<?php

namespace Tests\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * 运行测试命令
 */
class RunTestCommand extends Command
{
    protected static $defaultName = 'test:run';

    protected function configure()
    {
        $this
            ->setDescription('运行PVM单元测试')
            ->addArgument('test', InputArgument::OPTIONAL, '要运行的特定测试名称')
            ->addOption('all', null, InputOption::VALUE_NONE, '运行所有测试')
            ->addOption('php-version', null, InputOption::VALUE_REQUIRED, '要测试的PHP版本', '7.4.33')
            ->addOption('show-output', 'o', InputOption::VALUE_NONE, '显示测试输出');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('PHP Version Manager 测试');

        $test = $input->getArgument('test');
        $runAll = $input->getOption('all');
        $version = $input->getOption('php-version');
        $showOutput = $input->getOption('show-output');
        $verbose = $output->isVerbose();

        // 显示测试环境信息
        $this->showEnvironmentInfo($io);

        // 确定要运行的测试
        $tests = [];
        if ($runAll) {
            $tests = $this->getAllTests();
            $io->section('运行所有测试');
        } elseif ($test) {
            $tests = [$test];
            $io->section("运行测试: $test");
        } else {
            $io->section('运行默认测试');
            $tests = ['version_detector', 'version_installer', 'version_switcher'];
        }

        // 运行测试
        $results = [];
        $totalTests = count($tests);
        $passedTests = 0;

        $io->progressStart($totalTests);

        foreach ($tests as $testName) {
            $io->text("测试: $testName");

            $result = $this->runTest($testName, $version, $verbose || $showOutput, $io);
            $results[$testName] = $result;

            if ($result['success']) {
                $passedTests++;
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        // 显示测试结果
        $io->section('测试结果');

        foreach ($results as $testName => $result) {
            $status = $result['success'] ? '<info>通过</info>' : '<error>失败</error>';
            $io->text("$testName: $status");

            if (!$result['success'] || $verbose || $showOutput) {
                $io->text($result['output']);
            }
        }

        // 显示测试摘要
        $io->section('测试摘要');
        $io->text("总测试数: $totalTests");
        $io->text("通过测试数: $passedTests");
        $io->text("失败测试数: " . ($totalTests - $passedTests));

        // 返回状态码
        return $passedTests === $totalTests ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * 显示测试环境信息
     */
    private function showEnvironmentInfo(SymfonyStyle $io): void
    {
        $io->section('测试环境');

        // PHP版本
        $io->text('PHP版本: ' . PHP_VERSION);

        // 操作系统
        $os = php_uname('s') . ' ' . php_uname('r');
        $io->text('操作系统: ' . $os);

        // 当前目录
        $io->text('当前目录: ' . getcwd());

        // PVM信息
        $pvmPath = getenv('HOME') . '/.pvm';
        $pvmInstalled = is_dir($pvmPath);
        $io->text('PVM安装状态: ' . ($pvmInstalled ? '已安装' : '未安装'));

        if ($pvmInstalled) {
            // 获取已安装的PHP版本
            $versionsDir = $pvmPath . '/versions';
            if (is_dir($versionsDir)) {
                $versions = array_filter(scandir($versionsDir), function($item) {
                    return $item !== '.' && $item !== '..' && is_dir($versionsDir . '/' . $item);
                });

                if (!empty($versions)) {
                    $io->text('已安装的PHP版本: ' . implode(', ', $versions));
                } else {
                    $io->text('已安装的PHP版本: 无');
                }
            }
        }
    }

    /**
     * 获取所有可用的测试
     */
    private function getAllTests(): array
    {
        return [
            'version_detector',
            'version_installer',
            'version_switcher',
            'version_remover',
            'extension_manager',
            'composer_manager',
            'mirror_config'
        ];
    }

    /**
     * 运行指定的测试
     */
    private function runTest(string $testName, string $version, bool $verbose, SymfonyStyle $io): array
    {
        $testFile = __DIR__ . "/../../test_{$testName}.php";

        // 如果测试文件不存在，尝试其他可能的路径
        if (!file_exists($testFile)) {
            $testFile = __DIR__ . "/../../{$testName}_test.php";
        }

        // 如果仍然不存在，返回错误
        if (!file_exists($testFile)) {
            return [
                'success' => false,
                'output' => "测试文件不存在: $testFile"
            ];
        }

        // 设置环境变量
        $env = [
            'PVM_TEST_VERSION' => $version,
            'PVM_TEST_VERBOSE' => $verbose ? '1' : '0'
        ];

        // 运行测试
        $process = new Process(['php', $testFile], null, $env);
        $process->setTimeout(300); // 5分钟超时

        if ($verbose) {
            $io->text("执行命令: php $testFile");

            $process->run(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });
        } else {
            $process->run();
        }

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput() . $process->getErrorOutput()
        ];
    }
}
