<?php

// 从 docs/php.md 文件中提取所有扩展名称
$content = file_get_contents('docs/php.md');

// 使用正则表达式匹配扩展名称
preg_match_all('/^\| ([a-z_0-9]+) \|/m', $content, $matches);

$extensions = $matches[1];

// 移除表头
$extensions = array_filter($extensions, function($ext) {
    return $ext !== 'Extension';
});

// 去重并排序
$extensions = array_unique($extensions);
sort($extensions);

echo "找到 " . count($extensions) . " 个扩展:\n\n";

// 按功能分类
$categories = [
    'cache' => ['apcu', 'apcu_bc', 'memcache', 'memcached', 'redis', 'relay', 'yac'],
    'database' => ['cassandra', 'dba', 'interbase', 'mongo', 'mongodb', 'mssql', 'mysql', 'mysqli', 'oci8', 'odbc', 'pdo_dblib', 'pdo_firebird', 'pdo_mysql', 'pdo_oci', 'pdo_odbc', 'pdo_pgsql', 'pdo_sqlsrv', 'pgsql', 'sqlsrv', 'sybase_ct'],
    'debug' => ['blackfire', 'ddtrace', 'excimer', 'memprof', 'opcache', 'pcov', 'spx', 'xdebug', 'xhprof'],
    'image' => ['exif', 'gd', 'gmagick', 'imagick', 'vips'],
    'network' => ['amqp', 'curl', 'grpc', 'http', 'imap', 'ldap', 'mosquitto', 'oauth', 'openswoole', 'smbclient', 'snmp', 'soap', 'sockets', 'ssh2', 'stomp', 'swoole'],
    'format' => ['csv', 'json_post', 'msgpack', 'protobuf', 'simdjson', 'wddx', 'xmldiff', 'xmlrpc', 'xsl', 'yaml'],
    'string' => ['enchant', 'gettext', 'igbinary', 'iconv', 'intl', 'mbstring', 'pspell', 'recode', 'tidy'],
    'crypto' => ['gnupg', 'mcrypt', 'openssl', 'sodium'],
    'math' => ['bcmath', 'decimal', 'gmp'],
    'file' => ['bz2', 'lz4', 'lzf', 'snappy', 'zip', 'zstd'],
    'system' => ['calendar', 'ffi', 'inotify', 'parallel', 'pcntl', 'shmop', 'sysvmsg', 'sysvsem', 'sysvshm', 'uopz', 'uploadprogress', 'uuid'],
    'event' => ['ev', 'event'],
    'framework' => ['phalcon', 'yaf', 'yar', 'zephir_parser'],
    'language' => ['ast', 'ds', 'parle', 'php_trie'],
    'log' => ['seaslog'],
    'geo' => ['gearman', 'geoip', 'geos', 'geospatial', 'maxminddb'],
    'ml' => ['tensor'],
    'search' => ['solr'],
    'queue' => ['rdkafka', 'zmq', 'zookeeper'],
    'mail' => ['mailparse'],
    'markup' => ['cmark'],
    'script' => ['luasandbox'],
    'office' => ['xlswriter'],
    'time' => ['timezonedb'],
    'security' => ['snuffleupagus', 'sourceguardian', 'ioncube_loader'],
    'monitoring' => ['opencensus', 'opentelemetry'],
    'other' => ['ion', 'jsmin', 'propro', 'pthreads', 'raphf', 'xdiff']
];

// 分类显示
foreach ($categories as $category => $categoryExtensions) {
    $found = array_intersect($extensions, $categoryExtensions);
    if (!empty($found)) {
        echo ucfirst($category) . " (" . count($found) . "): " . implode(', ', $found) . "\n";
    }
}

// 找出未分类的扩展
$categorized = [];
foreach ($categories as $categoryExtensions) {
    $categorized = array_merge($categorized, $categoryExtensions);
}
$uncategorized = array_diff($extensions, $categorized);
if (!empty($uncategorized)) {
    echo "\nUncategorized (" . count($uncategorized) . "): " . implode(', ', $uncategorized) . "\n";
}

echo "\n总计: " . count($extensions) . " 个扩展\n";

// 保存到文件
file_put_contents('all_extensions.txt', implode("\n", $extensions));
echo "\n扩展列表已保存到 all_extensions.txt\n";
