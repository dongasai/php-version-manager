# GitHub CI Docker 自动构建

本文档描述了PVM-Mirror项目的GitHub Actions CI/CD配置，用于自动构建Docker镜像并推送到GitHub Container Registry (GHCR)。

## 🚀 工作流概述

### 1. Docker Build and Push (`.github/workflows/docker-build.yml`)

**触发条件:**
- Push到`master`或`develop`分支
- 对Docker相关文件的Pull Request
- 手动触发

**功能:**
- 多架构构建 (linux/amd64, linux/arm64)
- 自动标签管理
- 安全扫描 (Trivy)
- 镜像缓存优化
- PR测试验证

**镜像标签策略:**
```
ghcr.io/your-username/pvm/pvm-mirror:latest      # master分支最新版
ghcr.io/your-username/pvm/pvm-mirror:dev         # develop分支
ghcr.io/your-username/pvm/pvm-mirror:pr-123      # PR测试镜像
ghcr.io/your-username/pvm/pvm-mirror:master-abc123  # SHA标签
```

### 2. Docker Release (`.github/workflows/docker-release.yml`)

**触发条件:**
- 推送版本标签 (`v*.*.*`)
- 发布Release

**功能:**
- 语义化版本标签
- 构建证明生成 (SLSA)
- 安全扫描报告
- 自动文档更新
- 发布说明生成

**版本标签策略:**
```
ghcr.io/your-username/pvm/pvm-mirror:v1.2.3      # 完整版本
ghcr.io/your-username/pvm/pvm-mirror:1.2         # 主次版本
ghcr.io/your-username/pvm/pvm-mirror:1           # 主版本
ghcr.io/your-username/pvm/pvm-mirror:stable      # 稳定版本
```

### 3. CI Tests (`.github/workflows/ci.yml`)

**功能:**
- PHP多版本兼容性测试 (7.4-8.3)
- Docker构建测试
- 安全扫描
- 代码语法检查

## 🔧 配置要求

### 1. 仓库设置

在GitHub仓库中需要配置以下设置：

**Actions权限:**
- Settings → Actions → General
- 启用 "Read and write permissions"
- 启用 "Allow GitHub Actions to create and approve pull requests"

**Packages权限:**
- Settings → Actions → General
- 确保 "Write packages" 权限已启用

### 2. 环境变量

工作流使用以下环境变量：

| 变量名 | 说明 | 默认值 |
|--------|------|--------|
| `REGISTRY` | 容器注册表地址 | `ghcr.io` |
| `IMAGE_NAME` | 镜像名称 | `${{ github.repository }}/pvm-mirror` |

### 3. 密钥配置

工作流使用内置的`GITHUB_TOKEN`，无需额外配置。

## 📦 使用方法

### 1. 自动构建

**开发版本:**
```bash
# 推送到develop分支触发构建
git push origin develop

# 生成镜像: ghcr.io/your-username/pvm/pvm-mirror:dev
```

**生产版本:**
```bash
# 推送到master分支触发构建
git push origin master

# 生成镜像: ghcr.io/your-username/pvm/pvm-mirror:latest
```

**发布版本:**
```bash
# 创建并推送版本标签
git tag v1.2.3
git push origin v1.2.3

# 生成镜像: ghcr.io/your-username/pvm/pvm-mirror:v1.2.3
```

### 2. 手动触发

在GitHub仓库页面：
1. 进入 Actions 标签页
2. 选择 "Docker Build and Push" 工作流
3. 点击 "Run workflow"
4. 输入自定义标签（可选）

### 3. 拉取镜像

```bash
# 拉取最新版本
docker pull ghcr.io/your-username/pvm/pvm-mirror:latest

# 拉取特定版本
docker pull ghcr.io/your-username/pvm/pvm-mirror:v1.2.3

# 拉取开发版本
docker pull ghcr.io/your-username/pvm/pvm-mirror:dev
```

### 4. 运行镜像

```bash
# 运行最新版本
docker run -d -p 34403:34403 \
  -e PVM_MIRROR_ENV=production \
  ghcr.io/your-username/pvm/pvm-mirror:latest

# 运行特定版本
docker run -d -p 34403:34403 \
  -e PVM_MIRROR_ENV=production \
  ghcr.io/your-username/pvm/pvm-mirror:v1.2.3
```

## 🛠️ 本地管理

使用提供的管理脚本进行本地开发：

```bash
cd docker/pvm-mirror

# 构建本地镜像
./manage-images.sh build dev

# 测试镜像
./manage-images.sh test dev

# 运行容器
./manage-images.sh run dev

# 清理资源
./manage-images.sh clean

# 显示帮助
./manage-images.sh help
```

## 🔒 安全特性

### 1. 漏洞扫描

- 使用 Trivy 进行容器安全扫描
- 扫描结果上传到 GitHub Security 标签页
- 阻止包含高危漏洞的镜像发布

### 2. 构建证明

- 生成 SLSA 构建证明
- 确保镜像来源可追溯
- 支持供应链安全验证

### 3. 权限控制

- 使用最小权限原则
- 仅授予必要的 GitHub Token 权限
- 支持细粒度的访问控制

## 📊 监控和日志

### 1. 构建状态

在GitHub仓库页面查看：
- Actions 标签页显示所有工作流运行状态
- 每个工作流提供详细的构建日志
- 失败时发送邮件通知

### 2. 镜像信息

在GitHub Packages页面查看：
- 所有已发布的镜像版本
- 镜像大小和下载统计
- 安全扫描结果

### 3. 使用统计

```bash
# 查看镜像下载统计
gh api repos/:owner/:repo/packages/container/pvm-mirror/versions

# 查看工作流运行历史
gh run list --workflow=docker-build.yml
```

## 🚨 故障排除

### 1. 构建失败

**常见原因:**
- Dockerfile语法错误
- 依赖包安装失败
- 网络连接问题

**解决方法:**
```bash
# 本地测试构建
cd docker/pvm-mirror
docker build -t test -f Dockerfile ../../

# 检查构建日志
docker build --progress=plain -t test -f Dockerfile ../../
```

### 2. 推送失败

**常见原因:**
- 权限不足
- 网络问题
- 镜像大小超限

**解决方法:**
- 检查 GitHub Token 权限
- 验证仓库 Packages 设置
- 优化镜像大小

### 3. 测试失败

**常见原因:**
- 端口冲突
- 环境变量错误
- 服务启动超时

**解决方法:**
```bash
# 本地测试
cd docker/pvm-mirror
./test.sh

# 检查容器日志
docker logs pvm-mirror-test
```

## 📝 最佳实践

### 1. 版本管理

- 使用语义化版本号 (v1.2.3)
- 为重大更新创建Release
- 保持向后兼容性

### 2. 镜像优化

- 使用多阶段构建减小镜像大小
- 定期更新基础镜像
- 清理不必要的文件和缓存

### 3. 安全管理

- 定期更新依赖包
- 及时修复安全漏洞
- 使用非root用户运行容器

### 4. 文档维护

- 及时更新README文档
- 记录重要的配置变更
- 提供清晰的使用示例
