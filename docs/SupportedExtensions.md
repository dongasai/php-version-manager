# PVM-Mirror 支持的扩展列表

本文档列出了 PVM-Mirror 支持的所有 PHP 扩展，包括 PECL 扩展和 GitHub 扩展。

## 📊 扩展统计

- **PECL 扩展**: 31 个
- **GitHub 扩展**: 11 个
- **总计**: 42 个扩展

## 🔧 PECL 扩展 (31个)

### 缓存和数据库
- **redis** - Redis 客户端扩展
- **memcached** - Memcached 客户端扩展
- **mongodb** - MongoDB 驱动
- **apcu** - APCu 用户缓存

### 调试和性能
- **xdebug** - PHP 调试器和分析器
- **opcache** - Zend OPcache 操作码缓存

### 图像和媒体
- **imagick** - ImageMagick 图像处理库
- **gd** - GD 图像处理库

### 网络和通信
- **swoole** - 高性能异步网络框架
- **curl** - cURL 客户端库
- **grpc** - gRPC 通信框架

### 数据格式
- **yaml** - YAML 数据序列化
- **protobuf** - Protocol Buffers 数据序列化
- **json** - JSON 数据处理
- **xml** - XML 数据处理

### 字符串和编码
- **mbstring** - 多字节字符串处理
- **igbinary** - 二进制序列化
- **iconv** - 字符集转换

### 加密和安全
- **openssl** - OpenSSL 加密库
- **sodium** - Sodium 现代加密库
- **mcrypt** - Mcrypt 加密库（已废弃）

### 数学和计算
- **bcmath** - 任意精度数学运算
- **gmp** - GNU 多精度算术

### 国际化
- **intl** - 国际化扩展

### 文件和压缩
- **zip** - ZIP 文件处理
- **phar** - PHP 归档文件

### 框架扩展
- **phalcon** - Phalcon 高性能框架

### 事件和异步
- **event** - libevent 事件库
- **ev** - libev 事件循环
- **uv** - libuv 事件循环

### 日志和监控
- **seaslog** - 高性能日志扩展

## 🐙 GitHub 扩展 (11个)

### 缓存和数据库
- **redis** - phpredis Redis 客户端
- **memcached** - php-memcached 客户端
- **mongodb** - MongoDB PHP 驱动

### 调试和性能
- **xdebug** - Xdebug 调试器
- **tideways** - Tideways 性能分析器

### 图像和媒体
- **imagick** - Imagick 图像处理

### 网络和通信
- **swoole** - Swoole 异步框架

### 框架扩展
- **phalcon** - Phalcon C 扩展
- **yaf** - Yet Another Framework

### 语言工具
- **php-ast** - PHP 抽象语法树
- **php-ds** - PHP 数据结构

### 日志和监控
- **seaslog** - SeasLog 日志扩展

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
