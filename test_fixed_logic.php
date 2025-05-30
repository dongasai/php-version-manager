#!/usr/bin/env php
<?php

// 测试我们修复后的逻辑
echo "Testing our fixed Ubuntu driver logic...\n\n";

// 手动包含必要的文件，避免autoloader问题
require_once 'src/Core/Tags/TaggableInterface.php';
require_once 'src/Core/System/OsDriverInterface.php';
require_once 'src/Core/System/AbstractOsDriver.php';
require_once 'src/Core/System/Drivers/UbuntuDriver.php';

try {
    echo "Creating Ubuntu driver instance...\n";
    $driver = new \VersionManager\Core\System\Drivers\UbuntuDriver();
    echo "✅ Ubuntu driver created successfully\n\n";

    echo "Package Manager: " . $driver->getPackageManager() . "\n";
    echo "Has sudo access: " . ($driver->hasSudoAccess() ? 'yes' : 'no') . "\n\n";

    echo "Testing updatePackageCache method...\n";
    echo "This should succeed even with ESM warnings...\n\n";

    $result = $driver->updatePackageCache();

    if ($result) {
        echo "\n✅ SUCCESS: updatePackageCache returned true\n";
        echo "Our fix correctly handles ESM warnings!\n";
    } else {
        echo "\n❌ FAILED: updatePackageCache returned false\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
