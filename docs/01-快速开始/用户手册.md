# PHP Version Manager 用户手册

## 简介

PHP Version Manager (PVM) 是一个用于管理多个 PHP 版本的工具，允许您在同一系统上安装、切换和管理不同版本的 PHP 及其扩展。

本手册将指导您如何安装、配置和使用 PVM。

## 安装

### 系统要求

- Linux 操作系统（已测试：Ubuntu、Debian、CentOS、Fedora、Alpine）
- Bash shell
- Git
- curl 或 wget
- 编译工具（如果需要从源码编译）

### 安装步骤

1. 使用安装脚本安装：

```bash
curl -o- https://raw.githubusercontent.com/dongasai/php-version-manager/main/install.sh | bash
```

或者

```bash
wget -qO- https://raw.githubusercontent.com/dongasai/php-version-manager/main/install.sh | bash
```

2. 安装完成后，将以下内容添加到您的 `~/.bashrc`、`~/.zshrc` 或其他 shell 配置文件中：

```bash
export PVM_DIR="$HOME/.pvm"
[ -s "$PVM_DIR/shell/pvm.sh" ] && \. "$PVM_DIR/shell/pvm.sh"
```

3. 重新加载 shell 配置：

```bash
source ~/.bashrc  # 或 source ~/.zshrc
```

4. 验证安装：

```bash
pvm --version
```

## 基本使用

### 查看帮助

```bash
pvm help
```

### 列出可用命令

```bash
pvm
```

### 查看版本

```bash
pvm version
```

## PHP 版本管理

### 列出已安装的 PHP 版本

```bash
pvm list
```

### 列出可安装的 PHP 版本

```bash
pvm supported
```

### 安装 PHP 版本

```bash
pvm install 8.2.0
```

您可以指定完整版本号（如 `8.2.0`）或部分版本号（如 `8.2`），PVM 将安装最新的匹配版本。

### 切换 PHP 版本

#### 永久切换（全局）

```bash
pvm use 8.2.0
```

这将更改默认的 PHP 版本，对所有新的 shell 会话生效。

#### 临时切换（当前会话）

```bash
pvm switch 8.2.0
```

这只会在当前 shell 会话中更改 PHP 版本。

### 删除 PHP 版本

```bash
pvm remove 8.2.0
```

## PHP 扩展管理

### 列出已安装的扩展

```bash
pvm ext list
```

### 安装扩展

```bash
pvm ext install <扩展名>
```

例如：

```bash
pvm ext install redis
```

### 启用/禁用扩展

```bash
pvm ext enable <扩展名>
pvm ext disable <扩展名>
```

### 删除扩展

```bash
pvm ext remove <扩展名>
```

## Composer 管理

### 安装 Composer

```bash
pvm composer install
```

### 设置默认 Composer 版本

```bash
pvm composer default <版本>
```

例如：

```bash
pvm composer default 2.5.8
```

### 配置 Composer

```bash
pvm composer config <选项> <值>
```

例如：

```bash
pvm composer config repo.packagist composer https://mirrors.aliyun.com/composer/
```

## 配置管理

### 查看配置

```bash
pvm config list
```

### 设置配置

```bash
pvm config set <键> <值>
```

例如：

```bash
pvm config set mirror.php https://mirrors.aliyun.com/php/
```

### 使用配置菜单

```bash
pvm config-menu
```

这将显示一个交互式菜单，您可以在其中浏览和修改配置。

## Web 管理界面

PVM 提供了一个 Web 管理界面，您可以通过以下命令启动：

```bash
pvm web
```

默认情况下，Web 管理界面将在 http://127.0.0.1:8000 上启动。

您可以使用以下选项自定义主机和端口：

```bash
pvm web --host=0.0.0.0 --port=8080
```

Web 管理界面提供以下功能：

- 仪表盘：显示系统概览
- 版本管理：安装、切换和删除 PHP 版本
- 扩展管理：安装、配置和删除扩展
- Composer 管理：管理 Composer 版本和配置
- 状态监控：监控 PHP 进程和系统资源
- 设置：配置 PVM

## 状态监控

### 查看 PHP 进程

```bash
pvm monitor processes
```

### 查看 PHP-FPM 状态

```bash
pvm monitor fpm
```

### 查看系统资源使用情况

```bash
pvm monitor system
```

## 高级功能

### 初始化环境

```bash
pvm init
```

这将检查并初始化 PVM 环境，包括安装必要的依赖和配置。

### 更新 PVM

```bash
pvm update
```

### 缓存管理

```bash
pvm cache clear
```

### 安全检查

```bash
pvm security check
```

## 故障排除

### 常见问题

#### 安装 PHP 版本失败

1. 检查系统依赖：

```bash
pvm init
```

2. 检查网络连接和镜像配置：

```bash
pvm config list
```

3. 尝试使用不同的镜像：

```bash
pvm config set mirror.php https://mirrors.aliyun.com/php/
```

#### PHP 版本切换不生效

1. 确保已正确设置 PVM 环境变量：

```bash
echo $PVM_DIR
```

2. 检查 shell 配置文件中是否包含 PVM 初始化脚本：

```bash
grep -r "pvm.sh" ~/.bashrc ~/.zshrc
```

3. 重新加载 shell 配置：

```bash
source ~/.bashrc  # 或 source ~/.zshrc
```

#### 扩展安装失败

1. 检查 PHP 版本是否支持该扩展：

```bash
pvm ext info <扩展名>
```

2. 检查系统依赖：

```bash
pvm init
```

3. 尝试手动安装扩展：

```bash
pvm ext install <扩展名> --manual
```

### 获取帮助

如果您遇到问题，可以通过以下方式获取帮助：

1. 查看帮助文档：

```bash
pvm help
```

2. 检查系统状态：

```bash
pvm doctor
```

3. 访问 GitHub 仓库：https://github.com/dongasai/php-version-manager

## 附录

### 命令参考

| 命令 | 描述 |
|------|------|
| `pvm help` | 显示帮助信息 |
| `pvm version` | 显示版本信息 |
| `pvm list` | 列出已安装的 PHP 版本 |
| `pvm install <version>` | 安装指定版本的 PHP |
| `pvm use <version>` | 永久切换到指定版本的 PHP |
| `pvm switch <version>` | 临时切换到指定版本的 PHP |
| `pvm remove <version>` | 删除指定版本的 PHP |
| `pvm ext list` | 列出已安装的扩展 |
| `pvm ext install <name>` | 安装指定扩展 |
| `pvm ext remove <name>` | 删除指定扩展 |
| `pvm composer` | 管理 Composer |
| `pvm config` | 管理配置 |
| `pvm web` | 启动 Web 管理界面 |
| `pvm monitor` | 监控 PHP 进程和系统资源 |
| `pvm init` | 初始化 PVM 环境 |
| `pvm update` | 更新 PVM |
| `pvm cache` | 管理缓存 |
| `pvm security` | 安全检查 |
| `pvm doctor` | 诊断系统问题 |

### 配置参考

| 配置项 | 描述 | 默认值 |
|-------|------|-------|
| `mirror.php` | PHP 下载镜像 | `https://www.php.net/distributions/` |
| `mirror.pecl` | PECL 下载镜像 | `https://pecl.php.net/get/` |
| `mirror.composer` | Composer 下载镜像 | `https://getcomposer.org/download/` |
| `default_php` | 默认 PHP 版本 | 最新安装的版本 |
| `default_composer` | 默认 Composer 版本 | 最新安装的版本 |
| `cache_ttl` | 缓存有效期（秒） | `86400` (1天) |
| `parallel_downloads` | 并行下载数 | `3` |
| `verify_signatures` | 是否验证签名 | `true` |
