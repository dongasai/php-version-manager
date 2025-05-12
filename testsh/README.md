# PHP版本管理测试脚本

这个目录包含了一系列用于测试PHP版本管理器(PVM)的shell脚本，可以用来安装、配置和测试不同版本的PHP和PHP扩展。

## 脚本列表

### 主要脚本

- `php_version_manager.sh` - PHP版本管理脚本，用于安装、切换和卸载不同版本的PHP
- `php_extension_manager.sh` - PHP扩展管理脚本，用于安装、启用、禁用和卸载PHP扩展
- `php_environment_tester.sh` - PHP环境测试脚本，用于测试PHP环境和扩展功能
- `php_batch_operations.sh` - PHP批量操作脚本，用于批量安装PHP版本和扩展
- `run_all_tests.sh` - 运行所有测试脚本，并生成测试报告

### 特定版本安装脚本

- `install_php_5.6.sh` - 安装PHP 5.6版本及其常用扩展
- `install_php_7.4.sh` - 安装PHP 7.4版本及其常用扩展
- `install_php_8.1.sh` - 安装PHP 8.1版本及其常用扩展

### 特定扩展安装脚本

- `install_redis_extension.sh` - 为多个PHP版本安装Redis扩展
- `install_xdebug_extension.sh` - 为多个PHP版本安装Xdebug扩展

## 使用方法

### 准备工作

1. 确保已安装PVM（PHP版本管理器）
2. 确保脚本具有执行权限：

```bash
chmod +x testsh/*.sh
```

### PHP版本管理

```bash
# 安装指定版本的PHP
./php_version_manager.sh install 7.4.33

# 安装所有支持的PHP版本
./php_version_manager.sh install-all

# 切换到指定版本的PHP
./php_version_manager.sh use 7.4.33

# 卸载指定版本的PHP
./php_version_manager.sh remove 7.4.33

# 列出已安装的PHP版本
./php_version_manager.sh list

# 列出支持的PHP版本
./php_version_manager.sh supported
```

### PHP扩展管理

```bash
# 安装指定的PHP扩展
./php_extension_manager.sh install redis

# 为指定版本安装PHP扩展
./php_extension_manager.sh install redis 7.4.33

# 安装常用PHP扩展
./php_extension_manager.sh install-common

# 启用指定的PHP扩展
./php_extension_manager.sh enable xdebug

# 禁用指定的PHP扩展
./php_extension_manager.sh disable xdebug

# 卸载指定的PHP扩展
./php_extension_manager.sh remove redis

# 列出已安装的PHP扩展
./php_extension_manager.sh list
```

### PHP环境测试

```bash
# 显示PHP环境信息
./php_environment_tester.sh info

# 测试指定扩展的功能
./php_environment_tester.sh test-extension redis

# 测试所有已安装扩展的功能
./php_environment_tester.sh test-all

# 执行PHP性能基准测试
./php_environment_tester.sh benchmark

# 创建phpinfo页面
./php_environment_tester.sh create-phpinfo
```

### PHP批量操作

```bash
# 批量安装指定的PHP版本
./php_batch_operations.sh install-versions 7.4.33,8.0.30,8.1.27

# 批量安装指定的PHP扩展
./php_batch_operations.sh install-extensions redis,xdebug,mongodb

# 设置开发环境
./php_batch_operations.sh setup-dev-env 7.4.33

# 设置生产环境
./php_batch_operations.sh setup-prod-env 7.4.33

# 清理临时文件和缓存
./php_batch_operations.sh cleanup
```

### 特定版本安装

```bash
# 安装PHP 5.6
./install_php_5.6.sh

# 安装PHP 7.4
./install_php_7.4.sh

# 安装PHP 8.1
./install_php_8.1.sh
```

### 特定扩展安装

```bash
# 安装Redis扩展
./install_redis_extension.sh

# 安装Xdebug扩展
./install_xdebug_extension.sh
```

### 运行所有测试

```bash
# 运行所有测试脚本，并生成测试报告
./run_all_tests.sh
```

## 注意事项

1. 这些脚本需要在Linux环境下运行
2. 需要先安装PVM（PHP版本管理器）
3. 某些脚本可能需要root权限才能运行
4. 安装PHP扩展可能需要额外的系统依赖
5. 测试报告将保存在`test_reports`目录中

## 故障排除

如果遇到问题，请检查以下几点：

1. 确保PVM已正确安装
2. 确保脚本具有执行权限
3. 检查系统是否满足安装PHP和扩展的依赖要求
4. 检查磁盘空间是否足够
5. 查看脚本输出的错误信息

## 贡献

欢迎提交问题和改进建议！
