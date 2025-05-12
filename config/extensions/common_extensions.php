<?php

use VersionManager\Core\Extension\ExtensionType;

/**
 * 常用扩展配置
 *
 * 返回格式：[扩展名] = [扩展信息]
 */
return [
    // 数据库扩展
    'mysqli' => [
        'name' => 'mysqli',
        'description' => 'MySQL Improved Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [
            'default_socket' => '/tmp/mysql.sock',
            'default_host' => 'localhost',
            'default_user' => 'root',
            'default_pw' => '',
            'default_port' => '3306',
            'default_charset' => 'utf8mb4',
        ],
    ],
    'pdo' => [
        'name' => 'pdo',
        'description' => 'PHP Data Objects',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'pdo_mysql' => [
        'name' => 'pdo_mysql',
        'description' => 'PDO MySQL Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => ['pdo'],
        'config' => [
            'default_socket' => '/tmp/mysql.sock',
        ],
    ],
    'pdo_sqlite' => [
        'name' => 'pdo_sqlite',
        'description' => 'PDO SQLite Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'pdo_pgsql' => [
        'name' => 'pdo_pgsql',
        'description' => 'PDO PostgreSQL Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => ['pdo'],
        'config' => [],
    ],

    // 图形处理扩展
    'gd' => [
        'name' => 'gd',
        'description' => 'GD Graphics Library',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [
            'jpeg_ignore_warning' => '1',
        ],
    ],
    'exif' => [
        'name' => 'exif',
        'description' => 'Exchangeable Image Information',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'imagick' => [
        'name' => 'imagick',
        'description' => 'ImageMagick Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 缓存扩展
    'redis' => [
        'name' => 'redis',
        'description' => 'Redis Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [
            'arrays.names' => '',
            'arrays.hosts' => '',
            'arrays.timeout' => '0',
            'arrays.persistent' => '0',
            'arrays.auth' => '',
            'arrays.serializer' => 'none',
            'arrays.autorehash' => '0',
            'arrays.connecttimeout' => '0',
            'arrays.readtimeout' => '0',
        ],
    ],
    'memcached' => [
        'name' => 'memcached',
        'description' => 'Memcached Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [
            'sess_prefix' => 'memc.sess.',
            'sess_lock_wait' => '150000',
            'sess_lock_max_wait' => '0',
            'sess_lock_expire' => '0',
            'sess_consistent_hash' => '1',
            'sess_binary' => '1',
            'sess_persistent' => '0',
            'sess_number_of_replicas' => '0',
            'sess_randomize_replica_read' => '0',
            'sess_remove_failed' => '0',
            'sess_connect_timeout' => '1000',
            'sess_sasl_username' => '',
            'sess_sasl_password' => '',
        ],
    ],
    'apcu' => [
        'name' => 'apcu',
        'description' => 'APC User Cache',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [
            'enable' => '1',
            'shm_segments' => '1',
            'shm_size' => '32M',
            'ttl' => '0',
            'gc_ttl' => '3600',
            'entries_hint' => '4096',
            'slam_defense' => '1',
        ],
    ],

    // 网络扩展
    'curl' => [
        'name' => 'curl',
        'description' => 'cURL Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [
            'cainfo' => '',
        ],
    ],
    'openssl' => [
        'name' => 'openssl',
        'description' => 'OpenSSL Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'soap' => [
        'name' => 'soap',
        'description' => 'SOAP Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],

    // XML扩展
    'xml' => [
        'name' => 'xml',
        'description' => 'XML Parser',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'dom' => [
        'name' => 'dom',
        'description' => 'Document Object Model',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'simplexml' => [
        'name' => 'simplexml',
        'description' => 'SimpleXML Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],

    // 国际化扩展
    'mbstring' => [
        'name' => 'mbstring',
        'description' => 'Multibyte String Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [
            'language' => 'neutral',
            'internal_encoding' => '',
            'http_input' => '',
            'http_output' => '',
        ],
    ],
    'intl' => [
        'name' => 'intl',
        'description' => 'Internationalization Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],

    // 其他扩展
    'zip' => [
        'name' => 'zip',
        'description' => 'Zip Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'fileinfo' => [
        'name' => 'fileinfo',
        'description' => 'Fileinfo Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'json' => [
        'name' => 'json',
        'description' => 'JavaScript Object Notation',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'opcache' => [
        'name' => 'opcache',
        'description' => 'OPcache Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [
            'enable' => '1',
            'memory_consumption' => '128',
            'interned_strings_buffer' => '8',
            'max_accelerated_files' => '10000',
            'revalidate_freq' => '2',
            'save_comments' => '1',
            'validate_timestamps' => '1',
        ],
        'zend_extension' => true,
    ],
    'xdebug' => [
        'name' => 'xdebug',
        'description' => 'Xdebug Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [
            'mode' => 'develop',
            'client_host' => 'localhost',
            'client_port' => '9003',
            'idekey' => 'PHPSTORM',
            'start_with_request' => 'yes',
            'discover_client_host' => 'yes',
            'max_nesting_level' => '256',
        ],
        'zend_extension' => true,
    ],

    // 消息队列扩展
    'amqp' => [
        'name' => 'amqp',
        'description' => 'AMQP Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'rdkafka' => [
        'name' => 'rdkafka',
        'description' => 'Kafka Client Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 压缩和归档扩展
    'bz2' => [
        'name' => 'bz2',
        'description' => 'BZip2 Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'lz4' => [
        'name' => 'lz4',
        'description' => 'LZ4 Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'lzf' => [
        'name' => 'lzf',
        'description' => 'LZF Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'zstd' => [
        'name' => 'zstd',
        'description' => 'Zstandard Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 数据库相关扩展
    'mongodb' => [
        'name' => 'mongodb',
        'description' => 'MongoDB Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'pgsql' => [
        'name' => 'pgsql',
        'description' => 'PostgreSQL Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'pdo_dblib' => [
        'name' => 'pdo_dblib',
        'description' => 'PDO DBLIB Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'pdo_firebird' => [
        'name' => 'pdo_firebird',
        'description' => 'PDO Firebird Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'pdo_oci' => [
        'name' => 'pdo_oci',
        'description' => 'PDO OCI Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'pdo_odbc' => [
        'name' => 'pdo_odbc',
        'description' => 'PDO ODBC Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'pdo_sqlsrv' => [
        'name' => 'pdo_sqlsrv',
        'description' => 'PDO SQLSRV Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => ['pdo'],
        'config' => [],
    ],
    'sqlsrv' => [
        'name' => 'sqlsrv',
        'description' => 'SQLSRV Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'oci8' => [
        'name' => 'oci8',
        'description' => 'OCI8 Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'odbc' => [
        'name' => 'odbc',
        'description' => 'ODBC Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],

    // 图像处理相关扩展
    'gmagick' => [
        'name' => 'gmagick',
        'description' => 'GraphicsMagick Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'vips' => [
        'name' => 'vips',
        'description' => 'VIPS Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 加密和安全相关扩展
    'mcrypt' => [
        'name' => 'mcrypt',
        'description' => 'Mcrypt Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'sodium' => [
        'name' => 'sodium',
        'description' => 'Sodium Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'gnupg' => [
        'name' => 'gnupg',
        'description' => 'GnuPG Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'ssh2' => [
        'name' => 'ssh2',
        'description' => 'SSH2 Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 文本处理相关扩展
    'enchant' => [
        'name' => 'enchant',
        'description' => 'Enchant Spelling Library Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'pspell' => [
        'name' => 'pspell',
        'description' => 'Pspell Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'tidy' => [
        'name' => 'tidy',
        'description' => 'Tidy Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],

    // 系统相关扩展
    'pcntl' => [
        'name' => 'pcntl',
        'description' => 'Process Control Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'shmop' => [
        'name' => 'shmop',
        'description' => 'Shared Memory Operations',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'sysvmsg' => [
        'name' => 'sysvmsg',
        'description' => 'System V Message Queue',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'sysvsem' => [
        'name' => 'sysvsem',
        'description' => 'System V Semaphore',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'sysvshm' => [
        'name' => 'sysvshm',
        'description' => 'System V Shared Memory',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'inotify' => [
        'name' => 'inotify',
        'description' => 'Inotify Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 性能分析相关扩展
    'xhprof' => [
        'name' => 'xhprof',
        'description' => 'XHProf Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'pcov' => [
        'name' => 'pcov',
        'description' => 'PCOV Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'memprof' => [
        'name' => 'memprof',
        'description' => 'Memory Profiler Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 网络相关扩展
    'sockets' => [
        'name' => 'sockets',
        'description' => 'Sockets Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'swoole' => [
        'name' => 'swoole',
        'description' => 'Swoole Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'openswoole' => [
        'name' => 'openswoole',
        'description' => 'Open Swoole Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'http' => [
        'name' => 'http',
        'description' => 'HTTP Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'oauth' => [
        'name' => 'oauth',
        'description' => 'OAuth Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 序列化相关扩展
    'igbinary' => [
        'name' => 'igbinary',
        'description' => 'Igbinary Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'msgpack' => [
        'name' => 'msgpack',
        'description' => 'MessagePack Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'yaml' => [
        'name' => 'yaml',
        'description' => 'YAML Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 数学相关扩展
    'bcmath' => [
        'name' => 'bcmath',
        'description' => 'BCMath Arbitrary Precision Mathematics',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'gmp' => [
        'name' => 'gmp',
        'description' => 'GNU Multiple Precision',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],

    // 日历相关扩展
    'calendar' => [
        'name' => 'calendar',
        'description' => 'Calendar Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'timezonedb' => [
        'name' => 'timezonedb',
        'description' => 'Timezone Database Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // XML相关扩展
    'xsl' => [
        'name' => 'xsl',
        'description' => 'XSL Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'xmlrpc' => [
        'name' => 'xmlrpc',
        'description' => 'XML-RPC Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'xmldiff' => [
        'name' => 'xmldiff',
        'description' => 'XML Diff Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],

    // 其他扩展
    'imap' => [
        'name' => 'imap',
        'description' => 'IMAP Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'ldap' => [
        'name' => 'ldap',
        'description' => 'LDAP Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'snmp' => [
        'name' => 'snmp',
        'description' => 'SNMP Extension',
        'type' => ExtensionType::BUILTIN,
        'dependencies' => [],
        'config' => [],
    ],
    'uuid' => [
        'name' => 'uuid',
        'description' => 'UUID Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
    'xdiff' => [
        'name' => 'xdiff',
        'description' => 'XDiff Extension',
        'type' => ExtensionType::PECL,
        'dependencies' => [],
        'config' => [],
    ],
];
