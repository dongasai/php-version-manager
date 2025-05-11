# PHP Version Manager

## 项目简介
PHP Version Manager 是一个用于管理 PHP Cli 版本的工具，支持版本的创建、更新和删除。该工具旨在简化 PHP 版本的管理过程，提供易于使用的接口和功能。

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
- 支持 PHP 7.1 及以上版本

**注意：** 用户不需要预先安装 PHP。我们提供了一个独立的安装脚本，可以自动安装必要的基础 PHP 版本。

## 开发与测试

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



## 贡献
欢迎任何形式的贡献！请提交问题或拉取请求。

## 许可证
该项目遵循 MIT 许可证。有关详细信息，请查看 LICENSE 文件。