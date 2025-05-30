# Composer 管理

PVM 提供了一个统一的命令行接口来管理 Composer，包括安装、卸载和版本切换等功能。

## 命令概览

```bash
pvm composer <子命令> [选项] [参数]
```

### 子命令

- `install` - 安装 Composer
- `remove` - 卸载 Composer
- `use` - 切换 Composer 版本
- `list` - 列出已安装的 Composer
- `config` - 配置 Composer
- `info` - 显示 Composer 信息
- `help` - 显示帮助信息

### 全局选项

- `--php=<版本>` - 指定 PHP 版本，默认为当前版本
- `--version=<版本>` - 指定 Composer 版本，默认为 '2'

## 安装 Composer

安装指定版本的 Composer。

```bash
pvm composer install [选项]
```

### 选项

- `--php=<版本>` - 指定 PHP 版本，默认为当前版本
- `--version=<版本>` - 指定 Composer 版本，可以是 '1'、'2' 或具体版本号，默认为 '2'
- `--default` - 设置为默认 Composer
- `--mirror=<镜像名称>` - 使用指定的镜像下载

### 示例

```bash
# 为当前 PHP 版本安装最新的 Composer 2.x
pvm composer install

# 为当前 PHP 版本安装最新的 Composer 1.x
pvm composer install --version=1

# 为当前 PHP 版本安装指定版本的 Composer
pvm composer install --version=2.3.10

# 为指定 PHP 版本安装 Composer
pvm composer install --php=7.4.30

# 安装并设置为默认版本
pvm composer install --default

# 使用指定镜像安装
pvm composer install --mirror=aliyun
```

## 卸载 Composer

卸载指定版本的 Composer。

```bash
pvm composer remove [选项]
```

### 选项

- `--php=<版本>` - 指定 PHP 版本，默认为当前版本
- `--version=<版本>` - 指定要删除的 Composer 版本（必需）

### 示例

```bash
# 卸载当前 PHP 版本的 Composer 1.x
pvm composer remove --version=1

# 卸载指定 PHP 版本的 Composer 2.x
pvm composer remove --php=7.4.30 --version=2
```

## 切换 Composer 版本

切换到指定版本的 Composer。

```bash
pvm composer use <版本> [选项]
```

### 选项

- `--php=<版本>` - 指定 PHP 版本，默认为当前版本

### 示例

```bash
# 切换到 Composer 2.x
pvm composer use 2

# 切换到指定 PHP 版本的 Composer 1.x
pvm composer use 1 --php=7.4.30
```

## 列出已安装的 Composer

列出所有已安装的 Composer 版本。

```bash
pvm composer list [选项]
```

### 选项

- `--php=<版本>` - 指定 PHP 版本，如果不指定则列出所有 PHP 版本的 Composer

### 示例

```bash
# 列出所有已安装的 Composer
pvm composer list

# 列出指定 PHP 版本的 Composer
pvm composer list --php=7.4.30
```

## 配置 Composer

配置 Composer 的各项设置。

```bash
pvm composer config [选项] [配置项]
```

### 选项

- `--php=<版本>` - 指定 PHP 版本，默认为当前版本
- `--version=<版本>` - 指定 Composer 版本，默认为默认版本

### 示例

```bash
# 显示当前 Composer 配置
pvm composer config

# 配置 Composer 镜像
pvm composer config repo.packagist.org.url=https://mirrors.aliyun.com/composer

# 配置 Composer 超时时间
pvm composer config process-timeout=600

# 配置指定 PHP 版本的 Composer
pvm composer config --php=7.4.30 repo.packagist.org.url=https://mirrors.aliyun.com/composer
```

## 显示 Composer 信息

显示指定 Composer 的详细信息。

```bash
pvm composer info [选项]
```

### 选项

- `--php=<版本>` - 指定 PHP 版本，默认为当前版本
- `--version=<版本>` - 指定 Composer 版本，默认为默认版本

### 示例

```bash
# 显示当前 Composer 信息
pvm composer info

# 显示指定 PHP 版本的 Composer 信息
pvm composer info --php=7.4.30

# 显示指定版本的 Composer 信息
pvm composer info --version=1
```

## 常见问题

### 如何为每个 PHP 版本安装不同的 Composer？

PVM 允许为每个 PHP 版本安装独立的 Composer。您可以使用 `--php` 选项指定要安装 Composer 的 PHP 版本：

```bash
# 为 PHP 7.4 安装 Composer 2.x
pvm composer install --php=7.4.30

# 为 PHP 8.0 安装 Composer 2.x
pvm composer install --php=8.0.28

# 为 PHP 8.1 安装 Composer 1.x
pvm composer install --php=8.1.20 --version=1
```

### 如何设置默认的 Composer？

您可以在安装时使用 `--default` 选项将 Composer 设置为默认版本，或者使用 `use` 命令切换默认版本：

```bash
# 安装并设置为默认版本
pvm composer install --default

# 将已安装的 Composer 设置为默认版本
pvm composer use 2
```

### 如何使用国内镜像安装 Composer？

您可以使用 `--mirror` 选项指定要使用的镜像：

```bash
# 使用阿里云镜像安装 Composer
pvm composer install --mirror=aliyun
```

### 如何配置 Composer 使用国内镜像？

您可以使用 `config` 命令配置 Composer 使用国内镜像：

```bash
# 配置使用阿里云镜像
pvm composer config repo.packagist.org.url=https://mirrors.aliyun.com/composer

# 配置使用腾讯云镜像
pvm composer config repo.packagist.org.url=https://mirrors.cloud.tencent.com/composer
```
