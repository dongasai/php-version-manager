# PVM-Mirror 日志功能

PVM-Mirror 现在集成了 PVM 的完整日志机制，提供统一的日志记录和管理功能。

## 功能特性

### 1. PVM 风格的日志记录
- **命令开始记录**：记录命令名称、参数、开始时间、进程ID、用户和工作目录
- **命令结束记录**：记录结束时间、执行时长、退出代码和执行状态
- **详细过程记录**：记录命令执行过程中的重要步骤和操作

### 2. 双重日志系统
- **PVM 风格日志**：按时间组织的结构化日志文件
- **传统日志**：按类型分类的日志文件（向后兼容）

### 3. 日志文件组织
- **存储位置**：
  - **开发模式**：项目根目录的 `logs/` 目录下
  - **生产模式**：`~/.pvm/log/` 目录下
- **PVM 风格文件命名格式**：`年/月/日/时-分-秒.log`
- **传统日志文件**：`system.log`, `access.log`, `error.log`, `sync.log`, `download.log`

### 4. 日志级别支持
- **静默模式** (`-q`, `--quiet`)：只显示错误和最重要的信息
- **普通模式**：默认级别，显示一般信息
- **详细模式** (`-v`, `--verbose`)：显示详细的操作过程
- **调试模式** (`-d`, `--debug`)：显示所有调试信息

## 日志管理命令

### PVM 风格操作

#### 查看日志
```bash
# 显示当前日志文件的最后50行
pvm-mirror log show

# 显示最后100行
pvm-mirror log show --lines=100

# 显示指定日志文件
pvm-mirror log show 2025/06/04/23-15-30.log
```

#### 列出日志文件
```bash
# 列出所有日志文件
pvm-mirror log list
```

#### 查看日志路径
```bash
# 显示当前日志文件路径和日志根目录
pvm-mirror log path
```

#### 清理过期日志
```bash
# 清理30天前的日志（默认）
pvm-mirror log clear

# 清理7天前的日志
pvm-mirror log clear --days=7

# 强制清理，不询问确认
pvm-mirror log clear --days=7 --force
```

#### 实时查看日志
```bash
# 实时监控当前日志文件
pvm-mirror log tail

# 显示最后20行并实时监控
pvm-mirror log tail --lines=20
```

### 传统日志操作（向后兼容）

#### 查看传统日志
```bash
# 显示系统日志
pvm-mirror log legacy-show system 20

# 显示同步日志
pvm-mirror log legacy-show sync 50
```

#### 清空传统日志
```bash
# 清空访问日志
pvm-mirror log legacy-clear access
```

#### 查看传统日志路径
```bash
# 显示错误日志路径
pvm-mirror log legacy-path error
```

#### 显示日志类型
```bash
# 显示所有可用的传统日志类型
pvm-mirror log types
```

## 使用示例

### 基本使用
```bash
# 以详细模式运行同步命令
pvm-mirror -v sync php

# 以调试模式运行服务器命令
pvm-mirror -d server start

# 以静默模式运行清理命令
pvm-mirror -q clean
```

### 日志查看
```bash
# 查看最近的操作日志
pvm-mirror log show

# 查看详细的同步日志
pvm-mirror log show --lines=200

# 列出所有日志文件
pvm-mirror log list

# 实时监控日志
pvm-mirror log tail
```

### 日志管理
```bash
# 清理一周前的日志
pvm-mirror log clear --days=7

# 强制清理过期日志
pvm-mirror log clear --days=30 --force

# 查看日志存储路径
pvm-mirror log path
```

## 日志内容示例

### 命令开始记录
```
=== 命令开始 ===
命令: sync
参数: php 8.3
开始时间: 2025-06-04 23:15:30
PID: 12345
用户: developer
工作目录: /data/wwwroot/php/pvm
```

### 详细过程记录
```
[2025-06-04 23:15:30] [INFO] [SYNC] 开始指定内容同步
[2025-06-04 23:15:30] [INFO] [SYNC] 同步类型: php, 版本: 8.3
[2025-06-04 23:15:31] [INFO] [SYNC] 开始预检查阶段
[2025-06-04 23:15:35] [INFO] [SYNC] 预检查完成，需要下载 5 个文件
[2025-06-04 23:15:35] [INFO] [SYNC] 开始下载阶段
[2025-06-04 23:16:45] [SUCCESS] 完整镜像同步完成
```

### 命令结束记录
```
=== 命令结束 ===
结束时间: 2025-06-04 23:16:45
执行时长: 75秒
退出代码: 0
状态: 成功
```

## 开发模式与生产模式

### 开发模式检测
PVM-Mirror 会自动检测是否在开发模式下运行，检测条件包括：

1. **工作目录检查**：当前工作目录是否在项目目录内
2. **项目文件检查**：是否存在 `composer.json`、`bin/pvm-mirror`、`srcMirror/` 目录
3. **开发环境标识**：是否存在 `docker/`、`tests/` 目录或 `docker-compose.yml` 文件

### 日志存储策略
- **开发模式**：日志存储在项目根目录的 `logs/` 目录下，便于开发调试
- **生产模式**：日志存储在用户主目录的 `~/.pvm/log/` 目录下，避免污染项目目录

## 配置选项

### 启用/禁用文件日志
```php
// 禁用文件日志
\Mirror\Log\Logger::setFileLoggingEnabled(false);

// 启用文件日志
\Mirror\Log\Logger::setFileLoggingEnabled(true);
```

### 检查日志状态
```php
// 检查文件日志是否启用
$enabled = \Mirror\Log\Logger::isFileLoggingEnabled();

// 获取当前日志文件路径
$logFile = \Mirror\Log\LogManager::getCurrentPvmLogFile();
```

## 集成说明

### 在命令中添加日志记录
```php
use Mirror\Log\Logger;
use Mirror\Log\LogManager;

// 控制台输出和文件日志
Logger::info("开始执行操作");
Logger::verbose("详细操作信息");
Logger::success("操作成功完成");
Logger::error("操作失败");

// 仅文件日志
LogManager::pvmInfo("详细的内部操作", "OPERATION");
LogManager::pvmDebug("调试信息", "DEBUG");
LogManager::pvmWarning("警告信息");
LogManager::pvmError("错误信息");
LogManager::pvmSuccess("成功信息");
```

### 日志级别控制
```php
// 设置日志级别
Logger::setLevel(Logger::VERBOSE);

// 解析命令行参数中的日志级别
$logLevel = Logger::parseLogLevel($argv);
Logger::setLevel($logLevel);
```

## 注意事项

1. **磁盘空间**：日志文件会占用磁盘空间，建议定期清理
2. **权限要求**：确保 PVM-Mirror 有权限在日志目录下创建文件
3. **性能影响**：文件日志记录对性能影响很小，但在高频操作时可能有轻微影响
4. **向后兼容**：传统日志功能仍然可用，不会影响现有的日志记录

## 故障排除

### 日志文件无法创建
- 检查日志目录权限
- 确保磁盘空间充足
- 检查文件系统是否支持创建深层目录结构

### 日志内容不完整
- 检查命令是否正常结束
- 查看是否有异常中断
- 确认文件日志功能是否启用

### 日志文件过多
- 使用 `pvm-mirror log clear` 命令清理过期日志
- 设置合适的保留天数
- 考虑实现自动清理机制
