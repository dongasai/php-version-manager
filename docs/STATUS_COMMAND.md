# PVM Status 命令使用指南

## 概述

`pvm status` 命令用于显示PVM程序本身的状态信息，包括版本信息、镜像配置、已安装PHP版本、目录状态等。

## 使用方法

```bash
# 显示基本状态信息
pvm status

# 显示详细状态信息
pvm status --verbose
pvm status -v

# 以JSON格式输出状态信息
pvm status --json
```

## 输出内容

### 基本信息
- **PVM版本**：当前PVM程序的版本号
- **运行环境**：操作系统类型和架构
- **PHP版本**：当前运行的PHP版本
- **用户目录**：当前用户的主目录
- **PVM目录**：PVM的安装目录
- **初始化状态**：PVM是否已正确初始化

### PHP版本信息
- **已安装版本数**：通过PVM安装的PHP版本数量
- **已安装版本**：具体的版本列表
- **当前版本**：当前激活的PHP版本
- **全局版本**：全局设置的PHP版本
- **系统版本**：系统默认的PHP版本

### 镜像配置
- **PVM镜像源**：是否启用PVM自建镜像源
- **主镜像地址**：主要镜像源的URL
- **备用镜像数**：备用镜像源的数量
- **备用镜像列表**：具体的备用镜像URL（详细模式）
- **PHP下载源**：当前使用的PHP下载源
- **PECL下载源**：当前使用的PECL下载源
- **Composer下载源**：当前使用的Composer下载源

### 目录信息
- **PVM根目录**：存在状态和大小
- **PHP版本目录**：存在状态和大小
- **配置目录**：存在状态和大小
- **缓存目录**：存在状态和大小
- **日志目录**：存在状态和大小

### 配置文件信息（详细模式）
- **PVM镜像配置**：文件存在状态、大小、修改时间
- **镜像配置**：文件存在状态、大小、修改时间
- **全局配置**：文件存在状态、大小、修改时间
- **环境配置**：文件存在状态、大小、修改时间

## 输出示例

### 基本模式
```
PVM 状态信息
==================

基本信息:
  PVM版本: 1.0.0
  运行环境: Linux x86_64
  PHP版本: 8.1.2-1ubuntu2.21
  用户目录: /home/user
  PVM目录: /home/user/.pvm
  初始化状态: 已初始化

PHP版本信息:
  已安装版本数: 2
  已安装版本: 7.4.33, 8.1.27
  当前版本: 8.1.27
  全局版本: 8.1.27
  系统版本: 8.1.2

镜像配置:
  PVM镜像源: 启用
  主镜像地址: https://pvm.2sxo.com
  备用镜像数: 1
  PHP下载源: aliyun
  PECL下载源: official
  Composer下载源: aliyun

目录信息:
  PVM根目录: 存在 (156.7 MB)
  PHP版本目录: 存在 (156.2 MB)
  配置目录: 存在 (2.41 KB)
  缓存目录: 存在 (0 B)
  日志目录: 存在 (0 B)
```

### 详细模式
在基本模式的基础上，还会显示：
- 备用镜像源的具体URL列表
- 配置文件的详细信息（大小、修改时间）

### JSON模式
```json
{
    "pvm": {
        "version": "1.0.0",
        "os": "Linux x86_64",
        "php_version": "8.1.2-1ubuntu2.21",
        "home_directory": "/home/user",
        "pvm_directory": "/home/user/.pvm",
        "initialized": true
    },
    "php": {
        "installed_count": 2,
        "installed_versions": ["7.4.33", "8.1.27"],
        "current_version": "8.1.27",
        "global_version": "8.1.27",
        "system_version": "8.1.2"
    },
    "mirror": {
        "pvm_mirror": {
            "enabled": true,
            "main_url": "https://pvm.2sxo.com",
            "fallback_mirrors": ["http://localhost:34403"]
        },
        "traditional_mirrors": {
            "php": "aliyun",
            "pecl": "official",
            "composer": "aliyun"
        }
    },
    "directories": {
        "pvm_root": {
            "path": "/home/user/.pvm",
            "exists": true,
            "size": 164234567
        },
        ...
    },
    "config_files": {
        "pvm_mirror": {
            "path": "/home/user/.pvm/config/pvm-mirror.php",
            "exists": true,
            "size": 356,
            "modified_time": 1748443315
        },
        ...
    }
}
```

## 使用场景

### 1. 快速检查PVM状态
```bash
pvm status
```
用于快速了解PVM的基本状态，包括版本、已安装PHP版本、镜像配置等。

### 2. 故障排查
```bash
pvm status --verbose
```
当遇到问题时，使用详细模式可以获取更多信息，帮助诊断问题。

### 3. 自动化脚本
```bash
pvm status --json
```
在自动化脚本中使用JSON格式，便于程序解析和处理状态信息。

### 4. 系统监控
可以将status命令集成到监控系统中，定期检查PVM的状态。

## 注意事项

1. **权限要求**：status命令不需要特殊权限，普通用户即可执行
2. **性能影响**：命令执行速度很快，对系统性能影响极小
3. **目录大小计算**：大目录的大小计算可能需要一些时间
4. **JSON格式**：JSON输出中的时间戳为Unix时间戳格式

## 相关命令

- `pvm list` - 查看已安装的PHP版本
- `pvm monitor system` - 查看系统资源使用情况
- `pvm pvm-mirror status` - 查看镜像源状态
- `pvm service status` - 查看PHP服务状态
