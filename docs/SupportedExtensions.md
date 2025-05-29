# PVM-Mirror 支持的扩展列表

本文档列出了 PVM-Mirror 支持的所有 PHP 扩展，包括 PECL 扩展和 GitHub 扩展。

## 📊 扩展统计

- **PECL 扩展**: 100 个
- **GitHub 扩展**: 50 个
- **总计**: 150 个扩展

这是目前最全面的 PHP 扩展镜像解决方案，涵盖了 PHP 生态系统中几乎所有重要的扩展。

## 🔧 PECL 扩展 (100个)

### 缓存扩展 (6个)
- **apcu** - APCu 用户缓存
- **apcu_bc** - APCu 向后兼容模块
- **memcache** - Memcache 扩展
- **memcached** - Memcached 客户端扩展
- **redis** - Redis 客户端扩展
- **yac** - Yet Another Cache

### 数据库扩展 (20个)
- **cassandra** - DataStax PHP Driver for Apache Cassandra
- **dba** - Database (dbm-style) Abstraction Layer
- **interbase** - InterBase/Firebird functions
- **mongo** - MongoDB driver (legacy)
- **mongodb** - MongoDB 驱动
- **mssql** - Microsoft SQL Server functions
- **mysql** - MySQL functions (deprecated)
- **mysqli** - MySQL Improved Extension
- **oci8** - Oracle Call Interface
- **odbc** - ODBC functions
- **pdo_dblib** - PDO driver for FreeTDS/Sybase DB-lib
- **pdo_firebird** - PDO driver for Firebird
- **pdo_mysql** - PDO driver for MySQL
- **pdo_oci** - PDO driver for Oracle
- **pdo_odbc** - PDO driver for ODBC
- **pdo_pgsql** - PDO driver for PostgreSQL
- **pdo_sqlsrv** - Microsoft Drivers for PHP for SQL Server (PDO_SQLSRV)
- **pgsql** - PostgreSQL functions
- **sqlsrv** - Microsoft Drivers for PHP for SQL Server (SQLSRV)
- **sybase_ct** - Sybase CT functions

### 调试和性能扩展 (2个)
- **opcache** - Zend OPcache 操作码缓存
- **xdebug** - PHP 调试器和分析器

### 图像处理扩展 (4个)
- **exif** - Exchangeable image information
- **gd** - GD 图像处理库
- **gmagick** - GraphicsMagick binding
- **imagick** - ImageMagick 图像处理库

### 网络和通信扩展 (14个)
- **amqp** - AMQP client library
- **curl** - cURL 客户端库
- **grpc** - gRPC 通信框架
- **http** - Extended HTTP Support
- **imap** - IMAP, POP3 and NNTP functions
- **ldap** - LDAP functions
- **oauth** - OAuth consumer extension
- **smbclient** - SMB client library
- **snmp** - SNMP functions
- **soap** - SOAP functions
- **sockets** - Socket functions
- **ssh2** - SSH2 functions
- **stomp** - Stomp Client
- **swoole** - 高性能异步网络框架

### 数据格式扩展 (10个)
- **csv** - CSV functions
- **json** - JSON 数据处理
- **json_post** - JSON POST handler
- **msgpack** - MessagePack serializer
- **protobuf** - Protocol Buffers 数据序列化
- **wddx** - Web Distributed Data Exchange
- **xmldiff** - XML diff and merge
- **xmlrpc** - XML-RPC functions
- **xsl** - XSL functions
- **yaml** - YAML 数据序列化

### 字符串和编码扩展 (9个)
- **enchant** - Enchant spelling library
- **gettext** - GNU gettext
- **igbinary** - 二进制序列化
- **iconv** - 字符集转换
- **intl** - 国际化扩展
- **mbstring** - 多字节字符串处理
- **pspell** - Pspell functions
- **recode** - Recode functions
- **tidy** - Tidy HTML clean and repair

### 加密和安全扩展 (4个)
- **gnupg** - GNU Privacy Guard
- **mcrypt** - Mcrypt 加密库（已废弃）
- **openssl** - OpenSSL 加密库
- **sodium** - Sodium 现代加密库

### 数学计算扩展 (3个)
- **bcmath** - 任意精度数学运算
- **decimal** - Arbitrary precision decimal type
- **gmp** - GNU 多精度算术

### 文件和压缩扩展 (4个)
- **bz2** - Bzip2 compression
- **lzf** - LZF compression
- **phar** - PHP 归档文件
- **zip** - ZIP 文件处理

### 系统功能扩展 (8个)
- **calendar** - Calendar conversion
- **ffi** - Foreign Function Interface
- **inotify** - Inotify
- **pcntl** - Process Control
- **shmop** - Shared Memory
- **sysvmsg** - System V Message Queue
- **sysvsem** - System V Semaphore
- **sysvshm** - System V Shared Memory

### 事件处理扩展 (2个)
- **ev** - libev 事件循环
- **event** - libevent 事件库

### 框架扩展 (1个)
- **phalcon** - Phalcon 高性能框架

### 语言工具扩展 (1个)
- **parle** - Parsing and lexing

### 日志扩展 (1个)
- **seaslog** - 高性能日志扩展

### 地理和位置扩展 (2个)
- **geoip** - GeoIP location
- **geospatial** - Geospatial extension

### 脚本引擎扩展 (1个)
- **luasandbox** - Lua sandbox

### 时间处理扩展 (1个)
- **timezonedb** - Timezone database

### 安全防护扩展 (2个)
- **ioncube_loader** - ionCube PHP Encoder
- **sourceguardian** - SourceGuardian PHP encoder

### 其他工具扩展 (3个)
- **jsmin** - JavaScript minifier
- **propro** - Property proxy
- **raphf** - Resource and persistent handles

## 🐙 GitHub 扩展 (50个)

### 缓存和数据库扩展 (4个)
- **memcached** - php-memcached 客户端
- **mongodb** - MongoDB PHP 驱动
- **redis** - phpredis Redis 客户端
- **relay** - Next-generation Redis extension for PHP

### 调试和性能扩展 (9个)
- **blackfire** - Blackfire Profiler
- **ddtrace** - Datadog APM tracer
- **excimer** - Interrupting timer and low-overhead sampling profiler
- **memprof** - Memory usage profiler
- **pcov** - Code coverage driver
- **spx** - Simple profiling extension
- **tideways** - Tideways 性能分析器
- **xdebug** - Xdebug 调试器
- **xhprof** - Hierarchical Profiler

### 图像处理扩展 (2个)
- **imagick** - Imagick 图像处理
- **vips** - VIPS image processing

### 网络和通信扩展 (3个)
- **mosquitto** - Mosquitto MQTT client
- **openswoole** - Open Swoole
- **swoole** - Swoole 异步框架

### 数据格式扩展 (1个)
- **simdjson** - Fast JSON parser

### 框架扩展 (4个)
- **phalcon** - Phalcon C 扩展
- **yaf** - Yet Another Framework
- **yar** - Yet Another RPC framework
- **zephir_parser** - Zephir Parser

### 语言工具扩展 (1个)
- **php_trie** - Trie tree implementation

### 日志和监控扩展 (2个)
- **opencensus** - OpenCensus tracing
- **opentelemetry** - OpenTelemetry instrumentation

### 地理和位置扩展 (3个)
- **gearman** - Gearman job server
- **geos** - GEOS geometry engine
- **maxminddb** - MaxMind DB Reader

### 机器学习扩展 (1个)
- **tensor** - Scientific computing library

### 搜索扩展 (1个)
- **solr** - Apache Solr client

### 消息队列扩展 (3个)
- **rdkafka** - Kafka client
- **zmq** - ZeroMQ messaging
- **zookeeper** - Apache Zookeeper client

### 邮件处理扩展 (1个)
- **mailparse** - Email message manipulation

### 标记语言扩展 (1个)
- **cmark** - CommonMark parser

### 办公文档扩展 (1个)
- **xlswriter** - Excel writer

### 文件和压缩扩展 (3个)
- **lz4** - LZ4 compression
- **snappy** - Snappy compression
- **zstd** - Zstandard compression

### 系统功能扩展 (4个)
- **parallel** - Parallel concurrency API
- **uopz** - User Operations for Zend
- **uploadprogress** - Upload progress tracking
- **uuid** - UUID functions

### 安全防护扩展 (1个)
- **snuffleupagus** - Security module

### 其他工具扩展 (3个)
- **ion** - Amazon Ion data notation
- **pthreads** - Threading for PHP
- **xdiff** - File differences

## 📝 扩展说明

### 内置扩展说明
以下扩展通常是 PHP 内置的，PECL 版本主要用于：
- 旧版本 PHP 的兼容性
- 特殊配置需求
- 独立版本管理

**内置扩展列表**:
- `gd`, `curl`, `json`, `mbstring`, `openssl`, `xml`, `bcmath`, `gmp`, `intl`, `zip`, `phar`, `opcache`, `iconv`

### 版本兼容性
- **PHP 5.4+**: 支持大部分扩展
- **PHP 7.0+**: 推荐版本，完整支持
- **PHP 8.0+**: 最新特性支持

### 特殊说明
- **mcrypt**: 在 PHP 7.1 中废弃，PHP 7.2 中移除，建议使用 `sodium`
- **opcache**: PHP 5.5+ 内置，PECL 版本用于旧版本
- **sodium**: PHP 7.2+ 内置，PECL 版本用于旧版本

## 🚀 使用方式

### 同步所有扩展
```bash
./bin/pvm-mirror sync
```

### 同步指定类型
```bash
# 同步 PECL 扩展
./bin/pvm-mirror sync pecl

# 同步 GitHub 扩展
./bin/pvm-mirror sync extensions
```

### 同步指定扩展
```bash
# 同步指定 PECL 扩展
./bin/pvm-mirror sync pecl redis

# 同步指定 GitHub 扩展
./bin/pvm-mirror sync extensions swoole
```

## 📈 版本发现

所有扩展都支持自动版本发现：

```bash
# 发现所有版本
./bin/pvm-mirror discover

# 更新配置文件
./bin/pvm-mirror update-config
```

## 🔄 配置管理

每个扩展都有独立的配置文件：
- PECL 扩展: `configMirror/extensions/pecl/{extension}.php`
- GitHub 扩展: `configMirror/extensions/github/{extension}.php`

配置文件包含：
- 版本信息
- 元数据
- 过滤规则
- 发现来源
