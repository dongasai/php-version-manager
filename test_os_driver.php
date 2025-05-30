<?php

try {
    echo "Testing basic functionality...\n";

    // 测试parse_ini_file
    echo "Testing parse_ini_file...\n";
    if (file_exists('/etc/os-release')) {
        $osRelease = parse_ini_file('/etc/os-release');
        echo "OS Release parsed successfully\n";
        print_r($osRelease);
    } else {
        echo "/etc/os-release not found\n";
    }

    echo "Test completed\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
