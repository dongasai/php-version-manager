<?php

// 自动加载
require_once __DIR__ . '/../vendor/autoload.php';

use VersionManager\Core\Process\Process;

// 设置颜色
$GREEN = "\033[32m";
$RED = "\033[31m";
$YELLOW = "\033[33m";
$BLUE = "\033[34m";
$NC = "\033[0m"; // No Color

echo "{$BLUE}===== PVM 命令测试 ====={$NC}\n\n";

// 测试环境信息
echo "{$BLUE}测试环境信息:{$NC}\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "操作系统: " . php_uname('s') . ' ' . php_uname('r') . "\n";
echo "当前目录: " . getcwd() . "\n\n";

// PVM可执行文件路径
$pvmBin = realpath(__DIR__ . '/../bin/pvm');

// 确保PVM可执行文件存在
if (!file_exists($pvmBin)) {
    echo "{$RED}错误: PVM可执行文件不存在{$NC}\n";
    exit(1);
}

// 测试命令
$commands = [
    'help' => ['help'],
    'version' => ['version'],
    'version list' => ['version', 'list'],
    'version available' => ['version', 'available'],
    'list' => ['list'],
    'ext list' => ['ext', 'list'],
    'cache list' => ['cache', 'list'],
    'security check' => ['security', 'check'],
    'env list' => ['env', 'list'],
];

$results = [];
$totalTests = count($commands);
$passedTests = 0;

foreach ($commands as $name => $args) {
    echo "{$BLUE}测试 {$name} 命令...{$NC}\n";

    // 执行命令
    $fullCommand = array_merge([$pvmBin], $args);
    $process = new Process($fullCommand);
    $process->setTimeout(60); // 1分钟超时
    $process->run();

    // 显示命令详情
    echo "  命令: " . implode(' ', $fullCommand) . "\n";
    echo "  退出码: " . $process->getExitCode() . "\n";

    // 验证命令执行成功
    if ($process->getExitCode() === 0) {
        echo "  命令执行成功\n";

        // 显示命令输出的前几行
        $output = explode("\n", $process->getOutput());
        $outputLines = array_slice($output, 0, 3);
        foreach ($outputLines as $line) {
            if (trim($line)) {
                echo "  输出: {$line}\n";
            }
        }

        if (count($output) > 3) {
            echo "  ...(更多输出省略)...\n";
        }

        $passedTests++;
        $results[$name] = true;
    } else {
        echo "  {$RED}命令执行失败: {$process->getExitCode()}{$NC}\n";
        echo "  错误: " . $process->getErrorOutput() . "\n";
        $results[$name] = false;
    }

    echo "\n";
}

// 显示测试结果摘要
echo "{$BLUE}测试结果摘要:{$NC}\n";
echo "总测试数: {$totalTests}\n";
echo "通过测试数: {$passedTests}\n";
echo "失败测试数: " . ($totalTests - $passedTests) . "\n\n";

// 显示详细测试结果
echo "{$BLUE}详细测试结果:{$NC}\n";
foreach ($results as $commandName => $passed) {
    echo "{$commandName}: ";

    if ($passed) {
        echo "{$GREEN}通过{$NC}\n";
    } else {
        echo "{$RED}失败{$NC}\n";
    }
}

echo "\n{$BLUE}===== 测试完成 ====={$NC}\n";
