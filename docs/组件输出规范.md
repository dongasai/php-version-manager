# PVM 组件输出级别规范

## 概述

本文档详细定义了PVM各个组件在不同输出级别下的具体行为规范。

## 输出级别映射表

| 组件 | 方法 | 静默模式 | 默认模式 | 详细模式 |
|------|------|----------|----------|----------|
| **OsDriver** | updatePackageCache() | ❌ | ✅ 基本信息 | ✅ 完整输出 |
| **OsDriver** | installPackages() | ❌ | ✅ 包列表 | ✅ 安装过程 |
| **OsDriver** | isPackageInstalled() | ❌ | ❌ | ✅ 检查过程 |
| **VersionDriver** | installDependencies() | ❌ | ✅ 进度信息 | ✅ 详细过程 |
| **VersionDriver** | downloadSource() | ❌ | ✅ 进度条 | ✅ URL+详情 |
| **VersionDriver** | extractSource() | ❌ | ✅ 基本信息 | ✅ 解压详情 |
| **VersionDriver** | configure() | ❌ | ✅ 配置阶段 | ✅ 配置选项 |
| **VersionDriver** | compile() | ❌ | ✅ 编译阶段 | ✅ 编译输出 |
| **VersionDriver** | install() | ❌ | ✅ 安装阶段 | ✅ 安装详情 |
| **VersionInstaller** | install() | ✅ 结果 | ✅ 主要步骤 | ✅ 完整流程 |

## 详细规范

### 1. 操作系统驱动 (OsDriver)

#### updatePackageCache()

**静默模式**
```
# 无输出，除非发生错误
```

**默认模式**
```
更新软件包列表...
软件包列表更新成功
```

**详细模式**
```
更新软件包列表...
执行命令: sudo apt-get update
  命中:1 https://mirrors.cloud.tencent.com/ubuntu jammy InRelease
  命中:2 https://mirrors.cloud.tencent.com/ubuntu jammy-updates InRelease
  命中:3 https://mirrors.cloud.tencent.com/ubuntu jammy-security InRelease
  忽略:4 https://esm.ubuntu.com/apps/ubuntu jammy-apps-security InRelease
  忽略:5 https://esm.ubuntu.com/apps/ubuntu jammy-apps-updates InRelease
  正在读取软件包列表...
软件包列表更新成功
```

#### installPackages()

**静默模式**
```
# 无输出，除非发生错误
```

**默认模式**
```
安装依赖包: libmcrypt-dev libreadline-dev
依赖包安装成功
```

**详细模式**
```
检查已安装的依赖包...
  build-essential: 已安装
  libxml2-dev: 已安装
  libssl-dev: 已安装
  libmcrypt-dev: 未安装
  libreadline-dev: 未安装
安装依赖包: libmcrypt-dev libreadline-dev
执行命令: sudo apt-get install -y libmcrypt-dev libreadline-dev
  正在读取软件包列表...
  正在分析软件包的依赖关系树...
  正在读取状态信息...
  将会同时安装下列软件：
    libmcrypt4
  下列【新】软件包将被安装：
    libmcrypt-dev libmcrypt4 libreadline-dev
  升级了 0 个软件包，新安装了 3 个软件包，要卸载 0 个软件包，有 32 个软件包未被升级。
  需要下载 234 kB 的归档。
  解压缩后会消耗 1,234 kB 的额外空间。
  获取:1 http://archive.ubuntu.com/ubuntu jammy/universe amd64 libmcrypt4 amd64 2.5.8-7 [63.4 kB]
  获取:2 http://archive.ubuntu.com/ubuntu jammy/universe amd64 libmcrypt-dev amd64 2.5.8-7 [170 kB]
  已下载 234 kB，耗时 1秒 (234 kB/s)
  正在选中未选择的软件包 libmcrypt4:amd64。
  (正在读取数据库 ... 系统当前共安装有 123456 个文件和目录。)
  准备解压 .../libmcrypt4_2.5.8-7_amd64.deb  ...
  正在解压 libmcrypt4:amd64 (2.5.8-7) ...
  正在设置 libmcrypt4:amd64 (2.5.8-7) ...
  正在处理用于 libc-bin (2.35-0ubuntu3.1) 的触发器 ...
依赖包安装成功
```

### 2. 版本驱动 (VersionDriver)

#### installDependencies()

**静默模式**
```
# 无输出，除非发生错误
```

**默认模式**
```
安装系统依赖...
系统依赖安装完成
```

**详细模式**
```
安装系统依赖...
检测到操作系统: Ubuntu 22.04
检测到包管理器: apt
获取基础依赖列表...
基础依赖: build-essential libxml2-dev libssl-dev libsqlite3-dev zlib1g-dev libcurl4-openssl-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libxpm-dev
添加PHP 7.1特有依赖: libmcrypt-dev libreadline-dev
最终依赖列表: build-essential libxml2-dev libssl-dev libsqlite3-dev zlib1g-dev libcurl4-openssl-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libxpm-dev libmcrypt-dev libreadline-dev
[调用 OsDriver::updatePackageCache() - 详细模式输出]
[调用 OsDriver::installPackages() - 详细模式输出]
系统依赖安装完成
```

#### downloadSource()

**静默模式**
```
# 无输出，除非发生错误
```

**默认模式**
```
下载PHP 7.1.33 源码...
下载进度: [████████████████████████████████] 100%
源码下载完成
```

**详细模式**
```
下载PHP 7.1.33 源码...
下载URL: https://www.php.net/distributions/php-7.1.33.tar.gz
目标文件: /tmp/pvm_php_7.1.33_1234567890/php-7.1.33.tar.gz
开始下载...
文件大小: 15.2 MB
下载进度: [████████████████████████████████] 100% (15.2 MB / 15.2 MB)
下载速度: 2.3 MB/s
下载耗时: 6.6 秒
验证文件完整性...
MD5校验: 通过
源码下载完成
```
