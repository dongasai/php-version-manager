# PVM 镜像应用开发管理

本文档描述了 PVM 镜像应用（pvm-mirror）的开发管理流程、架构和功能规划。

## 项目概述

PVM 镜像应用是 PHP Version Manager (PVM) 的配套工具，用于镜像 PVM 项目涉及的所有需要下载的内容，包括 PHP 源码包、PECL 扩展包、特定扩展的 GitHub 源码和 Composer 包等。通过搭建本地镜像，可以加速 PHP 版本和扩展的下载和安装，特别适合网络环境不佳的地区。

## 目录结构

```
pvm-mirror/
├── bin/                    # 可执行脚本
│   ├── pvm-mirror          # 主程序
│   └── sync.sh             # 同步脚本
├── config/                 # 配置文件
│   ├── mirror.php          # 镜像内容配置
│   └── runtime.php         # 运行时配置
├── data/                   # 镜像数据
│   ├── php/                # PHP 源码镜像
│   ├── pecl/               # PECL 扩展镜像
│   ├── extensions/         # 特定扩展镜像
│   │   ├── redis/          # Redis 扩展镜像
│   │   ├── memcached/      # Memcached 扩展镜像
│   │   └── xdebug/         # Xdebug 扩展镜像
│   └── composer/           # Composer 镜像
├── logs/                   # 日志文件
├── public/                 # Web 服务根目录
│   ├── index.php           # 下载站点首页
│   ├── php/                # PHP 源码镜像（符号链接到 data/php）
│   ├── pecl/               # PECL 扩展镜像（符号链接到 data/pecl）
│   ├── extensions/         # 特定扩展镜像（符号链接到 data/extensions）
│   └── composer/           # Composer 镜像（符号链接到 data/composer）
├── srcMirror/              # 源代码
│   ├── Application.php     # 应用程序类
│   ├── Autoloader.php      # 自动加载器
│   ├── Command/            # 命令类
│   │   ├── AbstractCommand.php  # 抽象命令类
│   │   ├── CleanCommand.php     # 清理命令
│   │   ├── ConfigCommand.php    # 配置命令
│   │   ├── HelpCommand.php      # 帮助命令
│   │   ├── ServerCommand.php    # 服务器命令
│   │   ├── StatusCommand.php    # 状态命令
│   │   └── SyncCommand.php      # 同步命令
│   ├── Config/             # 配置类
│   │   └── ConfigManager.php    # 配置管理器
│   ├── Mirror/             # 镜像类
│   │   ├── PhpMirror.php        # PHP 镜像类
│   │   ├── PeclMirror.php       # PECL 镜像类
│   │   ├── ExtensionMirror.php  # 扩展镜像类
│   │   ├── ComposerMirror.php   # Composer 镜像类
│   │   └── MirrorStatus.php     # 镜像状态类
│   ├── Server/             # 服务器类
│   │   └── ServerManager.php    # 服务器管理器
│   ├── Utils/              # 工具类
│   │   ├── FileUtils.php        # 文件工具类
│   │   └── HttpUtils.php        # HTTP 工具类
│   └── Web/                # Web 相关类
│       ├── Controller.php       # 控制器
│       └── templates/           # 模板文件
└── tests/                  # 测试文件
    └── bats/               # Bats 测试
        └── pvm-mirror.bats      # 镜像应用测试
```

## 当前开发阶段

当前 pvm-mirror 项目处于功能完善阶段，主要任务包括：

1. 完善镜像同步功能
   - 优化下载性能
   - 增加并行下载支持
   - 增加断点续传支持
   - 增加镜像完整性检查

2. 增强服务器功能
   - 完善 Web 界面
   - 增加 API 接口
   - 增加基本访问控制
   - 增加 IP 白名单功能

3. 增强配置管理
   - 增加配置验证
   - 增加配置导入/导出
   - 增加配置向导

4. 增强监控和日志
   - 增加详细的日志记录
   - 增加简单状态监控
   - 增加基本统计功能

## 开发计划

### 第1阶段：基础功能（已完成）

- [x] 1-1 创建项目基础结构
  - [x] 1-1-1 设计目录结构
  - [x] 1-1-2 创建自动加载器
  - [x] 1-1-3 创建命令行框架

- [x] 1-2 实现配置管理
  - [x] 1-2-1 实现镜像配置
  - [x] 1-2-2 实现运行时配置
  - [x] 1-2-3 实现配置命令

- [x] 1-3 实现镜像同步
  - [x] 1-3-1 实现 PHP 源码镜像
  - [x] 1-3-2 实现 PECL 扩展镜像
  - [x] 1-3-3 实现特定扩展镜像
  - [x] 1-3-4 实现 Composer 镜像

- [x] 1-4 实现 Web 服务
  - [x] 1-4-1 实现服务器管理
  - [x] 1-4-2 实现基本 Web 界面

### 第2阶段：功能增强（当前阶段）

- [ ] 2-1 优化镜像同步
  - [ ] 2-1-1 实现并行下载
  - [ ] 2-1-2 实现断点续传
  - [ ] 2-1-3 实现增量同步
  - [ ] 2-1-4 实现镜像完整性检查

- [ ] 2-2 增强 Web 界面
  - [ ] 2-2-1 实现响应式设计
  - [ ] 2-2-2 实现镜像浏览


- [ ] 2-3 实现简单 API 接口
  - [ ] 2-3-1 设计简单 API 规范
  - [ ] 2-3-2 实现镜像查询 API
  - [ ] 2-3-3 实现镜像状态 API

- [ ] 2-4 增强配置管理
  - [ ] 2-4-1 实现配置验证
  - [ ] 2-4-2 实现配置导入/导出
  - [ ] 2-4-3 实现配置向导

### 第3阶段：安全与性能

- [x] 3-1 增强安全性
  - [x] 3-1-1 实现基本访问控制
  - [x] 3-1-2 实现IP白名单功能

- [ ] 3-2 优化性能
  - [x] 3-2-1 实现缓存机制
  - [x] 3-2-2 实现资源限制
  - [ ] 3-2-3 实现负载均衡

- [ ] 3-3 增强监控和日志
  - [ ] 3-3-1 实现详细日志记录
  - [ ] 3-3-2 实现简单状态监控
  - [ ] 3-3-3 实现基本统计功能

### 第4阶段：集成与发布

- [ ] 4-1 与 PVM 集成
  - [ ] 4-1-1 实现自动镜像配置
  - [ ] 4-1-2 实现镜像健康检查
  - [ ] 4-1-3 实现镜像自动切换

- [ ] 4-2 完善文档
  - [ ] 4-2-1 编写用户手册
  - [ ] 4-2-2 编写简单接口文档
  - [ ] 4-2-3 编写部署指南

- [ ] 4-3 准备发布
  - [ ] 4-3-1 创建发布包
  - [ ] 4-3-2 创建安装脚本
  - [ ] 4-3-3 创建 Docker 镜像

## 命令行接口

pvm-mirror 提供以下命令：

1. `sync` - 同步镜像内容
   ```
   pvm-mirror sync
   ```

2. `status` - 显示镜像状态
   ```
   pvm-mirror status
   ```

3. `clean` - 清理过期镜像
   ```
   pvm-mirror clean
   ```

4. `server` - 管理镜像服务器
   ```
   pvm-mirror server start [端口]  # 启动服务器
   pvm-mirror server stop          # 停止服务器
   pvm-mirror server status        # 显示服务器状态
   pvm-mirror server restart [端口] # 重启服务器
   ```

5. `config` - 管理配置
   ```
   pvm-mirror config get <key>             # 获取配置项
   pvm-mirror config set <key> <value>     # 设置配置项
   pvm-mirror config list [runtime|mirror] # 列出配置
   pvm-mirror config edit [runtime|mirror] # 编辑配置文件
   ```

6. `security` - 管理安全设置
   ```
   pvm-mirror security status                # 显示安全设置状态
   pvm-mirror security enable                # 启用访问控制
   pvm-mirror security disable               # 禁用访问控制
   pvm-mirror security whitelist enable      # 启用IP白名单
   pvm-mirror security whitelist disable     # 禁用IP白名单
   pvm-mirror security whitelist add <IP>    # 添加IP到白名单
   pvm-mirror security whitelist remove <IP> # 从白名单中移除IP
   pvm-mirror security whitelist list        # 列出白名单
   pvm-mirror security auth enable           # 启用基本认证
   pvm-mirror security auth disable          # 禁用基本认证
   pvm-mirror security auth add <用户名> <密码> # 添加用户
   pvm-mirror security auth remove <用户名>   # 移除用户
   pvm-mirror security auth list             # 列出用户
   pvm-mirror security log show [行数]       # 显示访问日志
   pvm-mirror security log clear             # 清空访问日志
   pvm-mirror security log path              # 显示日志文件路径
   ```

7. `cache` - 管理缓存
   ```
   pvm-mirror cache status    # 显示缓存状态
   pvm-mirror cache enable    # 启用缓存
   pvm-mirror cache disable   # 禁用缓存
   pvm-mirror cache clear     # 清空所有缓存
   pvm-mirror cache clean     # 清理过期缓存
   ```

8. `resource` - 管理系统资源
   ```
   pvm-mirror resource status                        # 显示资源状态
   pvm-mirror resource enable                        # 启用资源限制
   pvm-mirror resource disable                       # 禁用资源限制
   pvm-mirror resource set <key> <value>             # 设置资源限制
   pvm-mirror resource monitor                       # 监控资源使用情况
   ```

9. `help` - 显示帮助信息
   ```
   pvm-mirror help [命令]
   ```

## 开发规范

### 代码风格

- 遵循 PSR-1 和 PSR-2 代码风格规范
- 使用 4 个空格缩进
- 类名使用 PascalCase
- 方法名和变量名使用 camelCase
- 常量使用全大写加下划线分隔

### 文档规范

- 所有类、方法和属性都应该有 PHPDoc 注释
- 复杂的逻辑应该有行内注释
- 所有公共 API 都应该有详细的文档

### 测试规范

- 使用 Bats 进行命令行测试
- 所有新功能都应该有对应的测试
- 修复 bug 时应该添加相应的测试用例

## 贡献指南

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m '添加一些功能'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

## 版本控制

项目使用语义化版本控制 (SemVer)：

- 主版本号：不兼容的 API 更改
- 次版本号：向后兼容的功能添加
- 修订号：向后兼容的 bug 修复
