# PHP Version Manager 开发文档

## 项目概述

PHP Version Manager (PVM) 是一个用于管理 PHP CLI 版本的工具，支持版本的创建、更新和删除。该工具旨在简化 PHP 版本的管理过程，提供易于使用的接口和功能。

## 项目结构

```
php-version-manager/
├── src/                  # 源代码目录
│   ├── Core/             # 核心功能
│   ├── Commands/         # 命令行命令
│   ├── Utils/            # 工具类
│   └── Exceptions/       # 异常处理
├── tests/                # 测试目录
├── composer.json         # Composer配置
├── LICENSE               # 许可证文件
└── README.md             # 项目说明
```

## 技术栈

- PHP >= 7.1
- Monolog (日志管理)
- PHPUnit (单元测试)

## 依赖关系

### PHP依赖问题

由于PHP Version Manager (PVM)本身是用PHP编写的，因此存在一个逻辑上的循环依赖问题：用户需要已经安装PHP才能使用PVM来管理PHP版本。

为了解决这个问题，我们采用以下解决方案：

1. **提供独立的安装脚本**：创建一个不依赖PHP的Shell脚本，可以检测系统是否已安装PHP，如果没有，则自动安装一个基础版本的PHP。

2. **混合方法**：使用Bash脚本实现初始安装和基本功能，一旦安装了第一个PHP版本，就可以使用PHP实现的更高级功能。

这样，用户只需要运行一个简单的安装命令，不需要预先安装PHP，就可以开始使用PVM工具。

### 第三方仓库问题

为了确保安全性和兼容性，PVM避免使用第三方软件仓库（如PPA）来安装PHP和依赖项。相反，我们使用各个Linux发行版的官方仓库来安装基础PHP版本。

这种方法有以下优点：

1. **安全性**：避免了添加未经验证的第三方软件源
2. **兼容性**：提高了与不同发行版的兼容性
3. **稳定性**：使用经过充分测试的官方包

## 核心功能模块

### 1. 版本管理

- **创建版本**：支持从源代码或预编译包创建新的PHP版本
- **更新版本**：更新现有PHP版本到新版本
- **删除版本**：移除不再需要的PHP版本
- **切换版本**：在不同PHP版本之间快速切换

### 2. Composer管理

- 为每个PHP版本管理独立的Composer环境
- 支持Composer依赖的安装、更新和移除

### 3. 配置管理

- PHP配置文件(php.ini)的管理
- 扩展管理和配置

## 开发指南

### 环境设置

1. 克隆仓库：
   ```
   git clone https://github.com/dongasai/php-version-manager.git
   cd php-version-manager
   ```

2. 安装依赖：
   ```
   composer install
   ```

### 编码规范

- 遵循PSR-12编码规范
- 使用类型提示和返回类型声明
- 编写详细的文档注释

### 测试

- 为所有功能编写单元测试
- 运行测试：
  ```
  ./vendor/bin/phpunit
  ```

## API文档

### 核心类

#### VersionManager

主要负责PHP版本的管理。

```php
// 示例用法
$manager = new VersionManager\Core\VersionManager();
$manager->install('7.4.0');
$manager->use('7.4.0');
$manager->remove('7.1.0');
```

#### ComposerManager

管理不同PHP版本的Composer环境。

```php
// 示例用法
$composer = new VersionManager\Core\ComposerManager('7.4.0');
$composer->install('package/name');
```

## 贡献指南

1. Fork项目
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建Pull Request

## 打包为CLI工具

### 项目结构

PHP Version Manager被设计为一个可执行的CLI工具，其目录结构如下：

```
php-version-manager/
├── bin/                 # 可执行文件目录
│   └── pvm              # 主要的CLI入口文件
├── src/                 # 源代码目录
│   ├── Console/         # 命令行相关代码
│   │   ├── Commands/    # 具体命令实现
│   │   └── Application.php # 应用程序主类
│   ├── Core/            # 核心功能
│   ├── Utils/           # 工具类
│   └── Exceptions/      # 异常处理
├── tests/               # 测试目录
├── composer.json        # Composer配置
├── LICENSE              # 许可证文件
└── README.md            # 项目说明
```

### 配置Composer

要将项目打包为CLI工具，需要在`composer.json`中添加`bin`配置：

```json
{
    "name": "php-version-manager",
    "description": "A simple PHP CLI version management tool.",
    "type": "library",
    "require": {
        "php": ">=7.1",
        "monolog/monolog": "^2.0"
    },
    "autoload": {
        "psr-4": {
            "VersionManager\\": "src/"
        }
    },
    "bin": ["bin/pvm"],
    "minimum-stability": "stable"
}
```

### 创建可执行文件

在`bin/pvm`文件中，我们需要创建一个可执行的PHP脚本，它将作为命令行工具的入口点：

```php
#!/usr/bin/env php
<?php

// 自动加载
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

$loaded = false;
foreach ($autoloadPaths as $file) {
    if (file_exists($file)) {
        require $file;
        $loaded = true;
        break;
    }
}

// 引导应用程序
$app = new VersionManager\Console\Application();
$app->run();
```

确保该文件具有可执行权限：

```bash
chmod +x bin/pvm
```

### 安装和使用

用户可以通过以下方式安装和使用该工具：

#### 全局安装

```bash
composer global require php-version-manager/php-version-manager
```

确保 Composer 的全局 bin 目录在您的 PATH 中：

```bash
export PATH="$PATH:$HOME/.composer/vendor/bin"
```

#### 使用方式

安装后，用户可以直接使用 `pvm` 命令：

```bash
# 查看帮助信息
pvm help

# 安装指定版本的PHP
pvm install 7.4.0

# 切换到指定的PHP版本
pvm use 7.4.0

# 列出所有已安装的PHP版本
pvm list

# 删除指定的PHP版本
pvm remove 7.1.0
```

## 发布流程

1. 更新版本号
2. 更新CHANGELOG.md
3. 创建新的发布标签
4. 发布到Packagist

## 常见问题

### Q: 如何解决版本冲突？
A: 使用`pvm cleanup`命令可以清理冲突的版本文件。

### Q: 如何贡献新功能？
A: 请参考贡献指南，创建Pull Request提交您的贡献。

## 联系方式

如有任何问题或建议，请通过以下方式联系我们：
- 提交GitHub Issue
- 发送电子邮件至：[your-email@example.com]

## 许可证

该项目遵循MIT许可证。有关详细信息，请查看LICENSE文件。
