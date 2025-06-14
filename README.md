# PHP Version Manager

## 项目简介
PHP Version Manager (PVM) 是一个用于管理 PHP Cli 版本的工具，支持版本的创建、更新和删除。该工具旨在简化 PHP 版本的管理过程，提供易于使用的接口和功能。

## 功能
- 创建新的 PHP 版本
- 更新现有的 PHP 版本
- 删除不再需要的 PHP 版本
- 在不同的 PHP 版本之间切换
- 支持项目级别的 PHP 版本管理
- 管理 PHP 扩展
- Composer管理

## 系统要求

- 支持 Linux 发行版：Ubuntu、Debian、CentOS、Fedora、Alpine 等
- 支持架构：x86_64 (AMD64) 和 ARM (aarch64, armv7)
- 支持 PHP 5.4 及以上版本（基础运行环境需要 PHP 7.1 及以上版本）

**注意：** 用户不需要预先安装 PHP。我们提供了一个独立的安装脚本，可以自动安装必要的基础 PHP 版本。

## 支持的PHP版本

PVM支持以下 PHP 版本：

- PHP 5.4.x（已不再受官方支持）
- PHP 5.5.x（已不再受官方支持）
- PHP 5.6.x（已不再受官方支持）
- PHP 7.1.x（已不再受官方支持）
- PHP 7.2.x（已不再受官方支持）
- PHP 7.3.x（已不再受官方支持）
- PHP 7.4.x（安全支持到 2022-11-28）
- PHP 8.0.x（安全支持到 2023-11-26）
- PHP 8.1.x（安全支持到 2024-11-25）
- PHP 8.2.x（安全支持到 2025-12-08）
- PHP 8.3.x（安全支持到 2026-12-31）

不同的 Linux 发行版和架构对这些 PHP 版本的支持程度不同。使用 `pvm supported` 命令可以查看当前系统支持的 PHP 版本。

有关支持的详细信息，请参阅 [SUPPORTED_VERSIONS.md](docs/SUPPORTED_VERSIONS.md) 文档。

## 安装

### 快速安装

使用一键安装脚本：

```bash
curl -fsSL https://gitee.com/Dongasai/php-version-manager/raw/main/install.sh | bash
```

或者从 GitHub：

```bash
curl -fsSL https://raw.githubusercontent.com/dongasai/php-version-manager/main/install.sh | bash
```

### 自定义安装

```bash
# 下载安装脚本
wget https://gitee.com/Dongasai/php-version-manager/raw/main/install.sh

# 查看安装选项
bash install.sh --help

# 自定义安装目录
bash install.sh --dir=/opt/pvm

# 指定仓库源
bash install.sh --source=github
```

### 安装后配置

安装完成后，重新加载 shell 配置：

```bash
source ~/.bashrc
# 或者
source ~/.zshrc
```

验证安装：

```bash
pvm --version
```

## 快速入门

### 5 分钟上手指南

1. **安装 PVM**
   ```bash
   curl -fsSL https://gitee.com/Dongasai/php-version-manager/raw/main/install.sh | bash
   source ~/.bashrc
   ```

2. **查看可用的 PHP 版本**
   ```bash
   pvm supported
   ```

3. **安装 PHP 版本**
   ```bash
   pvm install 8.3    # 安装最新的 PHP 8.3
   ```

4. **切换 PHP 版本**
   ```bash
   pvm use 8.3        # 永久切换到 PHP 8.3
   php -v             # 验证版本
   ```

5. **安装常用扩展**
   ```bash
   pvm ext install redis
   pvm ext install swoole
   ```

6. **安装 Composer**
   ```bash
   pvm composer install
   composer --version
   ```

### 第一个项目

创建一个简单的 PHP 项目来测试 PVM：

```bash
# 创建项目目录
mkdir my-php-project
cd my-php-project

# 创建一个简单的 PHP 文件
cat > index.php << 'EOF'
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Extensions: " . implode(', ', get_loaded_extensions()) . "\n";
EOF

# 运行项目
php index.php
```

## 使用方法

### 基本命令

#### 查看帮助
```bash
pvm help                    # 显示帮助信息
pvm help <command>          # 显示特定命令的帮助
```

#### 查看版本信息
```bash
pvm version                 # 显示 PVM 版本
pvm status                  # 显示 PVM 状态信息
```

### 交互式操作

PVM 提供了丰富的交互式操作功能，让管理更加简单直观：

#### 主交互式菜单
```bash
pvm interactive             # 启动主交互式菜单
```

#### 安装向导
```bash
pvm install-wizard          # 启动安装向导，提供引导式安装
```

#### 专门的管理菜单
```bash
pvm version-menu            # PHP版本管理菜单
pvm extension-menu          # 扩展管理菜单
pvm composer-menu           # Composer管理菜单
pvm service-menu            # 服务管理菜单
```

这些交互式命令提供：
- 友好的菜单界面
- 智能的选项推荐
- 实时的操作反馈
- 详细的帮助信息

### PHP 版本管理

#### 查看可用版本
```bash
pvm supported               # 查看当前系统支持的 PHP 版本
```

#### 安装 PHP 版本
```bash
pvm install 8.3             # 安装最新的 PHP 8.3.x
pvm install 8.2.15          # 安装指定的 PHP 版本
pvm install 7.4             # 安装最新的 PHP 7.4.x
```

#### 查看已安装版本
```bash
pvm list                    # 列出所有已安装的 PHP 版本
```

#### 切换 PHP 版本

**永久切换（全局）：**
```bash
pvm use 8.3                 # 永久切换到 PHP 8.3
```

**临时切换（当前会话）：**
```bash
pvm switch 8.2              # 仅在当前终端会话中切换到 PHP 8.2
```

#### 删除 PHP 版本
```bash
pvm remove 7.4              # 删除 PHP 7.4
```

### PHP 扩展管理

#### 查看扩展
```bash
pvm ext list                # 查看已安装的扩展
pvm ext list --available    # 查看可安装的扩展
```

#### 安装扩展
```bash
pvm ext install redis       # 安装 Redis 扩展
pvm ext install redis --version=5.3.7  # 安装指定版本的扩展
pvm ext install swoole      # 安装 Swoole 扩展
```

#### 管理扩展
```bash
pvm ext enable redis        # 启用扩展
pvm ext disable redis       # 禁用扩展
pvm ext remove redis        # 删除扩展
```

### Composer 管理

#### 安装 Composer
```bash
pvm composer install                    # 为当前 PHP 版本安装最新 Composer 2.x
pvm composer install --version=1       # 安装 Composer 1.x
pvm composer install --version=2.5.8   # 安装指定版本
pvm composer install --php=8.2         # 为指定 PHP 版本安装 Composer
```

#### 管理 Composer
```bash
pvm composer list                       # 列出已安装的 Composer
pvm composer use 2                      # 切换到 Composer 2.x
pvm composer remove --version=1        # 删除 Composer 1.x
```

#### 配置 Composer
```bash
# 配置国内镜像
pvm composer config repo.packagist.org.url https://mirrors.aliyun.com/composer
pvm composer config repo.packagist.org.url https://packagist.phpcomposer.com
```

### 服务管理

#### PHP-FPM 服务
```bash
pvm service fpm start       # 启动 PHP-FPM
pvm service fpm stop        # 停止 PHP-FPM
pvm service fpm restart     # 重启 PHP-FPM
pvm service fpm status      # 查看 PHP-FPM 状态
```

#### Web 服务器配置
```bash
# 配置 Nginx 虚拟主机
pvm service nginx install example.com /var/www/html
pvm service nginx install example.com /var/www/html --port=8080
pvm service nginx uninstall example.com

# 配置 Apache 虚拟主机
pvm service apache install example.com /var/www/html
pvm service apache uninstall example.com
```

### 配置管理

#### PHP 配置
```bash
pvm config list             # 查看当前 PHP 配置
pvm config set memory_limit 256M       # 设置内存限制
pvm config set upload_max_filesize 50M # 设置上传文件大小限制
pvm config-menu             # 交互式配置菜单
```

#### 环境变量管理
```bash
pvm env list                # 查看环境变量
pvm env set KEY=VALUE       # 设置环境变量
pvm env unset KEY           # 删除环境变量
```

### 缓存管理

```bash
pvm cache clear             # 清理所有缓存
pvm cache clear downloads   # 清理下载缓存
pvm cache clear builds      # 清理构建缓存
pvm cache info              # 查看缓存信息
```

### 监控和诊断

#### 系统监控
```bash
pvm monitor                 # 监控 PHP 进程和系统资源
pvm monitor --php=8.3       # 监控指定版本的 PHP 进程
```

#### 系统诊断
```bash
pvm doctor                  # 诊断系统问题
pvm security check          # 安全检查
```

### Web 管理界面

启动 Web 管理界面：

```bash
pvm web                     # 启动 Web 界面（默认端口 8080）
pvm web --port=9000         # 指定端口启动
```

通过浏览器访问 `http://localhost:8080` 进行图形化管理。

### 更新 PVM

```bash
pvm update                  # 更新 PVM 到最新版本
```

### 初始化环境

```bash
pvm init                    # 初始化 PVM 运行环境
```

## 常用场景

### 场景 1：开发环境快速切换

```bash
# 为不同项目使用不同 PHP 版本
cd /path/to/legacy-project
pvm switch 7.4              # 临时切换到 PHP 7.4

cd /path/to/modern-project
pvm switch 8.3              # 临时切换到 PHP 8.3
```

### 场景 2：测试多版本兼容性

```bash
# 安装多个版本进行测试
pvm install 7.4
pvm install 8.1
pvm install 8.2
pvm install 8.3

# 在不同版本下运行测试
pvm switch 7.4 && php test.php
pvm switch 8.1 && php test.php
pvm switch 8.2 && php test.php
pvm switch 8.3 && php test.php
```

### 场景 3：生产环境部署

```bash
# 安装生产环境所需版本和扩展
pvm install 8.2
pvm use 8.2
pvm ext install redis
pvm ext install swoole
pvm composer install

# 配置 PHP-FPM
pvm service fpm start
pvm service nginx install mysite.com /var/www/html
```

## 命令参考

### 主要命令一览表

| 命令 | 功能描述 | 示例 |
|------|----------|------|
| `pvm help` | 显示帮助信息 | `pvm help install` |
| `pvm version` | 显示版本信息 | `pvm version` |
| `pvm status` | 显示 PVM 状态 | `pvm status` |
| `pvm init` | 初始化 PVM 环境 | `pvm init` |
| `pvm update` | 更新 PVM | `pvm update` |

### 交互式命令一览表

| 命令 | 功能描述 | 示例 |
|------|----------|------|
| `pvm interactive` | 主交互式菜单 | `pvm interactive` |
| `pvm install-wizard` | 安装向导 | `pvm install-wizard` |
| `pvm version-menu` | 版本管理菜单 | `pvm version-menu` |
| `pvm extension-menu` | 扩展管理菜单 | `pvm extension-menu` |
| `pvm composer-menu` | Composer管理菜单 | `pvm composer-menu` |
| `pvm service-menu` | 服务管理菜单 | `pvm service-menu` |

### PHP 版本管理命令

| 命令 | 功能描述 | 示例 |
|------|----------|------|
| `pvm supported` | 查看支持的版本 | `pvm supported` |
| `pvm list` | 列出已安装版本 | `pvm list` |
| `pvm install <version>` | 安装 PHP 版本 | `pvm install 8.3` |
| `pvm use <version>` | 永久切换版本 | `pvm use 8.2` |
| `pvm switch <version>` | 临时切换版本 | `pvm switch 7.4` |
| `pvm remove <version>` | 删除 PHP 版本 | `pvm remove 7.4` |

### 扩展管理命令

| 命令 | 功能描述 | 示例 |
|------|----------|------|
| `pvm ext list` | 列出已安装扩展 | `pvm ext list` |
| `pvm ext install <name>` | 安装扩展 | `pvm ext install redis` |
| `pvm ext enable <name>` | 启用扩展 | `pvm ext enable redis` |
| `pvm ext disable <name>` | 禁用扩展 | `pvm ext disable redis` |
| `pvm ext remove <name>` | 删除扩展 | `pvm ext remove redis` |

### Composer 管理命令

| 命令 | 功能描述 | 示例 |
|------|----------|------|
| `pvm composer install` | 安装 Composer | `pvm composer install --version=2` |
| `pvm composer list` | 列出 Composer | `pvm composer list` |
| `pvm composer use <version>` | 切换 Composer | `pvm composer use 2` |
| `pvm composer remove` | 删除 Composer | `pvm composer remove --version=1` |
| `pvm composer config` | 配置 Composer | `pvm composer config <key> <value>` |

### 服务管理命令

| 命令 | 功能描述 | 示例 |
|------|----------|------|
| `pvm service fpm start` | 启动 PHP-FPM | `pvm service fpm start` |
| `pvm service fpm stop` | 停止 PHP-FPM | `pvm service fpm stop` |
| `pvm service fpm restart` | 重启 PHP-FPM | `pvm service fpm restart` |
| `pvm service fpm status` | 查看 FPM 状态 | `pvm service fpm status` |
| `pvm service nginx install` | 配置 Nginx | `pvm service nginx install site.com /var/www` |

### 配置和工具命令

| 命令 | 功能描述 | 示例 |
|------|----------|------|
| `pvm config list` | 查看 PHP 配置 | `pvm config list` |
| `pvm config set` | 设置 PHP 配置 | `pvm config set memory_limit 256M` |
| `pvm config-menu` | 交互式配置 | `pvm config-menu` |
| `pvm cache clear` | 清理缓存 | `pvm cache clear` |
| `pvm monitor` | 监控进程 | `pvm monitor` |
| `pvm doctor` | 系统诊断 | `pvm doctor` |
| `pvm web` | 启动 Web 界面 | `pvm web --port=8080` |

## 常见问题

### 安装相关

**Q: 安装失败，提示权限不足？**

A: 确保有足够的权限安装系统依赖，或使用 sudo 运行安装脚本：
```bash
sudo bash install.sh
```

**Q: 如何更改安装目录？**

A: 使用 `--dir` 参数指定安装目录：
```bash
bash install.sh --dir=/opt/pvm
```

### 使用相关

**Q: 切换版本后，php 命令仍然是旧版本？**

A: 重新加载 shell 配置：
```bash
source ~/.bashrc
# 或重新打开终端
```

**Q: 如何查看当前使用的 PHP 版本？**

A: 使用以下命令：
```bash
php -v
pvm list  # 查看所有版本，当前版本会有标记
```

**Q: 扩展安装失败？**

A: 检查系统依赖是否完整：
```bash
pvm doctor  # 诊断系统问题
```

### 故障排除

**Q: PVM 命令不可用？**

A: 检查 PATH 环境变量：
```bash
echo $PATH | grep pvm
# 如果没有，手动添加到 ~/.bashrc
export PATH="$HOME/.pvm/bin:$PATH"
```

**Q: 如何完全卸载 PVM？**

A: 删除 PVM 目录和配置：
```bash
rm -rf ~/.pvm
# 从 ~/.bashrc 中删除 PVM 相关配置
```

## 开发与测试

### 使用开发容器

我们提供了一个基于PHP 7.1的开发容器，用于开发和测试。

1. 构建开发容器并进入容器的shell：
   ```
   make dev
   ```

2. 仅进入开发容器的shell：
   ```
   make shell
   ```

3. 在开发容器中运行命令：
   ```
   make run CMD="test-all"    # 运行所有测试
   make run CMD="composer install"  # 安装依赖
   ```

4. 在开发容器中运行测试：
   ```
   make test
   ```

### 使用Docker进行测试

我们提供了Docker环境来测试PVM在不同的Linux发行版和架构上的兼容性。

1. 构建所有容器：
   ```
   make build
   ```

2. 在特定环境中运行测试：
   ```
   make test-ubuntu    # 测试Ubuntu环境
   make test-debian    # 测试Debian环境
   make test-centos    # 测试CentOS环境
   make test-fedora    # 测试Fedora环境
   make test-alpine    # 测试Alpine环境
   make test-arm64     # 测试ARM64架构
   ```

3. 在所有环境中运行测试：
   ```
   make test-all
   ```

4. 清理容器：
   ```
   make clean
   ```

## 🚀 GitHub CI 自动构建

PVM-Mirror 支持 GitHub Actions 自动构建 Docker 镜像并推送到 GitHub Container Registry (GHCR)。

### 📦 预构建镜像

我们提供了多个预构建的 Docker 镜像，可以直接使用：

```bash
# 拉取最新稳定版
docker pull ghcr.io/your-username/pvm/pvm-mirror:latest

# 运行 PVM-Mirror 服务
docker run -d -p 34403:34403 \
  -e PVM_MIRROR_ENV=production \
  ghcr.io/your-username/pvm/pvm-mirror:latest

# 访问服务
curl http://localhost:34403/
```

### 🏷️ 可用镜像标签

| 标签 | 说明 | 更新时机 |
|------|------|----------|
| `latest` | 最新稳定版 | 每次 master 分支更新 |
| `dev` | 开发版本 | 每次 develop 分支更新 |
| `v1.2.3` | 特定版本 | 发布新版本时 |
| `stable` | 最新发布版 | 创建 Release 时 |

### 🔧 自动构建特性

- ✅ **多架构支持**: linux/amd64, linux/arm64
- ✅ **自动标签管理**: 基于分支和标签自动生成镜像标签
- ✅ **安全扫描**: 使用 Trivy 进行漏洞扫描
- ✅ **构建缓存**: 优化构建速度
- ✅ **测试验证**: 自动测试构建的镜像
- ✅ **构建证明**: SLSA 构建证明确保供应链安全

### 📖 详细文档

- [GitHub CI Docker 配置文档](docs/GitHub-CI-Docker.md)
- [快速设置指南](docs/快速设置GitHub-CI.md)
- [Docker 使用文档](docker/pvm-mirror/README.md)


## 相关文档

- [快速入门指南](docs/快速入门.md) - 详细的入门教程
- [用户手册](docs/用户手册.md) - 完整的用户手册
- [交互操作指南](docs/交互操作指南.md) - 交互式功能使用指南
- [支持的版本](docs/SUPPORTED_VERSIONS.md) - 支持的 PHP 版本详情
- [版本切换原理](docs/版本切换.md) - 版本切换的技术原理
- [Composer 管理](docs/Composer.md) - Composer 管理详细说明
- [用户界面](docs/用户界面.md) - 命令行和 Web 界面说明
- [支持的扩展](docs/支持的扩展.md) - 支持的 PHP 扩展列表

## 社区和支持

### 获取帮助

- **GitHub Issues**: [提交问题](https://github.com/dongasai/php-version-manager/issues)
- **Gitee Issues**: [提交问题](https://gitee.com/Dongasai/php-version-manager/issues)
- **文档**: 查看 `docs/` 目录下的详细文档

### 参与贡献

我们欢迎各种形式的贡献：

1. **报告问题**: 发现 bug 或有改进建议
2. **提交代码**: 修复问题或添加新功能
3. **完善文档**: 改进文档或添加示例
4. **分享经验**: 分享使用经验和最佳实践

#### 贡献步骤

1. Fork 项目到你的账户
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建 Pull Request

### 版本发布

PVM 遵循语义化版本控制：

- **主版本号**: 不兼容的 API 修改
- **次版本号**: 向下兼容的功能性新增
- **修订号**: 向下兼容的问题修正

### 路线图

- [ ] 支持更多 Linux 发行版
- [ ] 添加 Windows 支持
- [ ] 改进 Web 管理界面
- [ ] 添加插件系统
- [ ] 性能优化和缓存改进
- [x] 集成 CI/CD 工具 (GitHub Actions)

## 许可证

该项目遵循 MIT 许可证。有关详细信息，请查看 [LICENSE](LICENSE) 文件。

## 致谢

感谢所有为 PVM 项目做出贡献的开发者和用户。

特别感谢：
- PHP 官方团队提供的优秀语言
- 各 Linux 发行版维护者
- 开源社区的支持和反馈

---

**PHP Version Manager (PVM)** - 让 PHP 版本管理变得简单高效！

如果这个项目对你有帮助，请给我们一个 ⭐️