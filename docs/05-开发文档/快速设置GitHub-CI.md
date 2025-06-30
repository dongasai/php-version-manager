# 快速设置 GitHub CI 自动构建

本指南帮助您快速设置PVM-Mirror的GitHub CI自动构建功能。

## 🚀 一键设置

### 1. 推送代码到GitHub

```bash
# 添加所有文件
git add .

# 提交更改
git commit -m "添加GitHub CI自动构建配置"

# 推送到GitHub
git push origin master
```

### 2. 配置GitHub仓库

**启用GitHub Actions:**
1. 进入GitHub仓库页面
2. 点击 "Actions" 标签页
3. 如果提示启用Actions，点击 "I understand my workflows, go ahead and enable them"

**配置Packages权限:**
1. 进入 Settings → Actions → General
2. 在 "Workflow permissions" 部分选择 "Read and write permissions"
3. 勾选 "Allow GitHub Actions to create and approve pull requests"

### 3. 触发首次构建

**方法一：推送代码**
```bash
# 对Docker相关文件做任何修改
echo "# Updated $(date)" >> docker/pvm-mirror/README.md
git add docker/pvm-mirror/README.md
git commit -m "触发Docker构建"
git push origin master
```

**方法二：手动触发**
1. 进入 Actions → "Docker Build and Push"
2. 点击 "Run workflow"
3. 选择分支并点击 "Run workflow"

## 📦 镜像使用

### 构建完成后的镜像地址

```bash
# 最新版本 (master分支)
ghcr.io/your-username/pvm/pvm-mirror:latest

# 开发版本 (develop分支)
ghcr.io/your-username/pvm/pvm-mirror:dev

# 特定版本 (发布标签)
ghcr.io/your-username/pvm/pvm-mirror:v1.0.0
```

### 拉取和运行镜像

```bash
# 拉取最新镜像
docker pull ghcr.io/your-username/pvm/pvm-mirror:latest

# 运行容器
docker run -d -p 34403:34403 \
  -e PVM_MIRROR_ENV=production \
  ghcr.io/your-username/pvm/pvm-mirror:latest

# 访问服务
curl http://localhost:34403/
```

## 🏷️ 版本发布

### 创建发布版本

```bash
# 创建版本标签
git tag v1.0.0
git push origin v1.0.0

# 或者在GitHub上创建Release
# 1. 进入仓库页面
# 2. 点击 "Releases" → "Create a new release"
# 3. 输入标签版本 (如 v1.0.0)
# 4. 填写发布说明
# 5. 点击 "Publish release"
```

### 自动生成的镜像标签

发布 `v1.2.3` 版本时，会自动生成：
- `ghcr.io/your-username/pvm/pvm-mirror:v1.2.3`
- `ghcr.io/your-username/pvm/pvm-mirror:1.2`
- `ghcr.io/your-username/pvm/pvm-mirror:1`
- `ghcr.io/your-username/pvm/pvm-mirror:stable`

## 🔧 本地开发

### 使用管理脚本

```bash
cd docker/pvm-mirror

# 构建本地镜像
./manage-images.sh build dev

# 测试镜像
./manage-images.sh test dev

# 运行容器
./manage-images.sh run dev

# 推送到GHCR (需要先登录)
./manage-images.sh login
./manage-images.sh push dev
```

### 环境变量配置

```bash
# 设置仓库信息
export DOCKER_REGISTRY="ghcr.io"
export DOCKER_REPOSITORY="your-username/pvm"
export DOCKER_USERNAME="your-username"
export DOCKER_PASSWORD="your-github-token"

# 使用环境变量
./manage-images.sh build latest --push
```

## 🔍 监控构建

### 查看构建状态

1. **GitHub Actions页面:**
   - 进入仓库 → Actions
   - 查看工作流运行状态
   - 点击具体运行查看详细日志

2. **GitHub Packages页面:**
   - 进入仓库 → Packages
   - 查看已发布的镜像版本
   - 查看下载统计和安全扫描结果

### 构建失败排查

```bash
# 本地测试构建
cd docker/pvm-mirror
docker build -t test -f Dockerfile ../../

# 检查工作流配置
./scripts/validate-github-actions.sh

# 查看详细构建日志
docker build --progress=plain -t test -f Dockerfile ../../
```

## 🛡️ 安全特性

### 自动安全扫描

- ✅ **Trivy漏洞扫描**: 每次构建自动扫描容器漏洞
- ✅ **SLSA构建证明**: 发布版本包含构建证明
- ✅ **权限最小化**: 使用最小必要权限
- ✅ **安全报告**: 扫描结果上传到Security标签页

### 查看安全报告

1. 进入仓库 → Security → Code scanning alerts
2. 查看Trivy扫描发现的漏洞
3. 根据建议修复安全问题

## 📊 使用统计

### 查看镜像统计

```bash
# 安装GitHub CLI
gh auth login

# 查看包信息
gh api repos/:owner/:repo/packages/container/pvm-mirror

# 查看工作流运行历史
gh run list --workflow=docker-build.yml

# 查看最新运行状态
gh run view --web
```

## 🚨 常见问题

### Q: 构建失败，提示权限不足
**A:** 检查仓库Settings → Actions → General → Workflow permissions，确保选择了"Read and write permissions"

### Q: 无法推送到GHCR
**A:** 确保：
1. 仓库是公开的，或者
2. 已正确配置包的可见性设置
3. GitHub Token有packages:write权限

### Q: 镜像构建时间过长
**A:** 
1. 检查网络连接
2. 使用构建缓存 (已自动配置)
3. 考虑优化Dockerfile

### Q: 如何自定义镜像标签
**A:** 
1. 手动触发工作流时输入自定义标签
2. 修改工作流文件中的标签策略
3. 使用本地管理脚本构建

## 📝 下一步

1. **自定义配置**: 根据需要修改工作流文件
2. **添加测试**: 扩展测试覆盖范围
3. **监控设置**: 配置构建失败通知
4. **文档更新**: 保持文档与代码同步

## 🔗 相关链接

- [GitHub Actions文档](https://docs.github.com/en/actions)
- [GitHub Container Registry](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
- [Docker最佳实践](https://docs.docker.com/develop/dev-best-practices/)
- [Trivy安全扫描](https://github.com/aquasecurity/trivy)
