#!/usr/bin/env php
<?php

// 简单测试，不使用autoloader，直接测试apt-get update
echo "Testing apt-get update directly...\n";

// 模拟我们修复的逻辑
function testAptUpdate() {
    $command = 'sudo apt-get update';
    
    echo "Executing: $command\n";
    
    $output = [];
    $returnCode = 0;
    
    exec($command . ' 2>&1', $output, $returnCode);
    
    echo "Return code: $returnCode\n";
    echo "Output:\n";
    foreach ($output as $line) {
        echo "  $line\n";
    }
    
    // 应用我们的修复逻辑
    if ($returnCode === 0) {
        echo "\n✅ SUCCESS: apt-get update succeeded (return code 0)\n";
        echo "Even with warnings, this should be considered successful.\n";
        return true;
    }
    
    // 检查是否是权限问题
    $outputStr = implode("\n", $output);
    if (strpos($outputStr, '权限不够') !== false || 
        strpos($outputStr, 'Permission denied') !== false ||
        strpos($outputStr, '无法对目录') !== false ||
        strpos($outputStr, '无法打开锁文件') !== false) {
        echo "\n❌ FAILED: Permission denied\n";
        return false;
    }
    
    // 检查是否是认证失败
    if (strpos($outputStr, '认证失败') !== false || 
        strpos($outputStr, 'Authentication failure') !== false) {
        echo "\n❌ FAILED: Authentication failure\n";
        return false;
    }
    
    // 检查是否是网络问题（ESM源连接超时等）
    if (strpos($outputStr, '连接超时') !== false || 
        strpos($outputStr, 'Connection timed out') !== false ||
        strpos($outputStr, '无法连接') !== false ||
        strpos($outputStr, 'Could not connect') !== false) {
        echo "\n⚠️  WARNING: Network issues detected, but should continue\n";
        return true;
    }
    
    echo "\n❌ FAILED: Unknown error\n";
    return false;
}

$result = testAptUpdate();
echo "\nFinal result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
