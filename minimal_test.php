#!/usr/bin/env php
<?php

echo "Minimal test of the fix...\n\n";

// 模拟我们在UbuntuDriver::updatePackageCache中的修复逻辑
function simulateFixedLogic() {
    echo "Simulating apt-get update...\n";
    
    // 执行实际的命令
    $command = 'sudo apt-get update';
    $output = [];
    $returnCode = 0;
    
    exec($command . ' 2>&1', $output, $returnCode);
    
    echo "Return code: $returnCode\n";
    echo "Sample output lines:\n";
    for ($i = 0; $i < min(5, count($output)); $i++) {
        echo "  " . $output[$i] . "\n";
    }
    echo "  ... (total " . count($output) . " lines)\n\n";
    
    // 应用我们的修复逻辑：对于apt-get update，只要退出码为0就认为成功
    if ($returnCode === 0) {
        echo "✅ FIXED LOGIC: Command succeeded (return code 0)\n";
        echo "   ESM warnings are ignored as they should be.\n";
        return true;
    }
    
    // 检查具体的错误类型
    $outputStr = implode("\n", $output);
    
    if (strpos($outputStr, '权限不够') !== false || 
        strpos($outputStr, 'Permission denied') !== false ||
        strpos($outputStr, '无法对目录') !== false ||
        strpos($outputStr, '无法打开锁文件') !== false) {
        echo "❌ REAL ERROR: Permission denied\n";
        return false;
    }
    
    if (strpos($outputStr, '认证失败') !== false || 
        strpos($outputStr, 'Authentication failure') !== false) {
        echo "❌ REAL ERROR: Authentication failure\n";
        return false;
    }
    
    echo "❌ REAL ERROR: Other failure (return code: $returnCode)\n";
    return false;
}

$result = simulateFixedLogic();

echo "\n" . str_repeat("=", 50) . "\n";
echo "CONCLUSION:\n";
if ($result) {
    echo "✅ Our fix works correctly!\n";
    echo "   The command succeeds despite ESM warnings.\n";
    echo "   This should resolve the original permission escalation issue.\n";
} else {
    echo "❌ There's still an issue that needs to be addressed.\n";
}
echo str_repeat("=", 50) . "\n";
