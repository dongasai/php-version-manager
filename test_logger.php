#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

echo "Testing Logger System...\n\n";

// 测试不同的日志级别
$levels = [
    \VersionManager\Core\Logger\LogLevel::SILENT => 'SILENT',
    \VersionManager\Core\Logger\LogLevel::NORMAL => 'NORMAL', 
    \VersionManager\Core\Logger\LogLevel::VERBOSE => 'VERBOSE',
    \VersionManager\Core\Logger\LogLevel::DEBUG => 'DEBUG'
];

foreach ($levels as $level => $name) {
    echo "=== Testing $name level ===\n";
    \VersionManager\Core\Logger\Logger::setLevel($level);
    
    echo "Current level: " . \VersionManager\Core\Logger\Logger::getLevel() . "\n";
    
    \VersionManager\Core\Logger\Logger::silent("This is a silent message");
    \VersionManager\Core\Logger\Logger::info("This is an info message");
    \VersionManager\Core\Logger\Logger::verbose("This is a verbose message");
    \VersionManager\Core\Logger\Logger::debug("This is a debug message");
    \VersionManager\Core\Logger\Logger::success("This is a success message");
    \VersionManager\Core\Logger\Logger::warning("This is a warning message");
    \VersionManager\Core\Logger\Logger::error("This is an error message");
    
    echo "\n";
}

echo "Logger test completed.\n";
