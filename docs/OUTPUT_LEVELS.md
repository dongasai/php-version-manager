# PVM 输出级别文档

## 概述

PVM 支持三个主要的输出级别，用于控制安装过程中的日志详细程度：

- **静默模式 (Silent)**：最少输出，只显示关键信息和错误
- **默认模式 (Normal)**：显示主要操作和结果
- **详细模式 (Verbose)**：显示完整的执行过程和命令输出

## 使用方法

```bash
# 静默模式
./bin/pvm install --silent -y 7.1
./bin/pvm install -s -y 7.1

# 默认模式（无需参数）
./bin/pvm install -y 7.1

# 详细模式
./bin/pvm install --verbose -y 7.1
./bin/pvm install -v -y 7.1
```

## 各级别输出内容对比

### 1. 版本检测和确认阶段

#### 静默模式
```
自动匹配到PHP版本 7.1.33
警告: PHP版本 7.1.33 在当前系统上只有部分支持
自动确认安装
```

#### 默认模式
```
自动匹配到PHP版本 7.1.33
警告: PHP版本 7.1.33 在当前系统上只有部分支持
已知问题:
  - 已不再受官方支持，可能存在安全风险
  - 在某些新版本系统上可能无法正常编译
自动确认安装
```

#### 详细模式
```
自动匹配到PHP版本 7.1.33
检测到操作系统: Ubuntu 22.04
选择版本驱动: PHP71\Ubuntu
警告: PHP版本 7.1.33 在当前系统上只有部分支持
已知问题:
  - 已不再受官方支持，可能存在安全风险
  - 在某些新版本系统上可能无法正常编译
  - 某些扩展可能不兼容
自动确认安装
开始安装流程...
```

### 2. 系统依赖安装阶段

#### 静默模式
```
系统依赖安装完成
```

#### 默认模式
```
安装系统依赖...
更新软件包列表...
软件包列表更新成功
安装依赖包: libmcrypt-dev libreadline-dev
依赖包安装成功
系统依赖安装完成
```

#### 详细模式
```
安装系统依赖...
检测到包管理器: apt
更新软件包列表...
执行命令: sudo apt-get update
  命中:1 https://mirrors.cloud.tencent.com/ubuntu jammy InRelease
  命中:2 https://mirrors.cloud.tencent.com/ubuntu jammy-updates InRelease
  命中:3 https://mirrors.cloud.tencent.com/ubuntu jammy-security InRelease
  忽略:4 https://esm.ubuntu.com/apps/ubuntu jammy-apps-security InRelease
  ...
软件包列表更新成功
检查已安装的依赖包...
  build-essential: 已安装
  libxml2-dev: 已安装
  libssl-dev: 已安装
  ...
安装依赖包: libmcrypt-dev libreadline-dev
执行命令: sudo apt-get install -y libmcrypt-dev libreadline-dev
  正在读取软件包列表...
  正在分析软件包的依赖关系树...
  正在读取状态信息...
  ...
依赖包安装成功
系统依赖安装完成
```

### 3. PHP 源码下载阶段

#### 静默模式
```
PHP 7.1.33 安装成功
```

#### 默认模式
```
下载PHP 7.1.33 源码...
源码下载完成
解压源码...
配置编译选项...
编译安装PHP...
PHP 7.1.33 安装成功
```

#### 详细模式
```
下载PHP 7.1.33 源码...
下载URL: https://www.php.net/distributions/php-7.1.33.tar.gz
下载进度: [████████████████████████████████] 100%
源码下载完成 (15.2 MB)
解压源码...
解压到: /tmp/pvm_php_7.1.33_1234567890
配置编译选项...
执行命令: ./configure --prefix=/home/user/.pvm/versions/7.1.33 --enable-fpm --with-mysql --with-pdo-mysql ...
  checking for grep that handles long lines and -e... /usr/bin/grep
  checking for egrep... /usr/bin/grep -E
  checking for a sed that does not truncate output... /usr/bin/sed
  ...
编译安装PHP...
执行命令: make -j4
  /bin/bash /tmp/pvm_php_7.1.33_1234567890/libtool --silent --preserve-dup-deps --mode=compile ...
  ...
执行命令: make install
  Installing PHP SAPI module:       fpm
  Installing PHP CGI binary:        /home/user/.pvm/versions/7.1.33/bin/
  ...
配置PHP...
创建配置文件: /home/user/.pvm/versions/7.1.33/etc/php.ini
创建FPM配置: /home/user/.pvm/versions/7.1.33/etc/php-fpm.conf
PHP 7.1.33 安装成功
```
