# PHP版本管理系统设计文档

## 1. 概述

PHP版本管理系统（PVM）是一个用于管理多个PHP版本的工具，允许用户安装、切换和删除不同版本的PHP。本文档详细描述了版本安装、切换和版本驱动的设计与实现。

## 2. 系统架构

系统采用驱动适配器模式，主要由以下几个核心组件组成：

1. **版本安装器（VersionInstaller）**：负责协调版本安装过程
2. **版本切换器（VersionSwitcher）**：负责在不同PHP版本之间切换
3. **版本驱动（VersionDriver）**：负责特定PHP版本的安装和管理
4. **版本检测器（VersionDetector）**：负责检测系统环境和已安装的PHP版本

### 2.1 组件关系图

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  命令行界面     │────▶│  版本安装器     │────▶│  版本驱动工厂   │
└─────────────────┘     └─────────────────┘     └─────────────────┘
                               │                        │
                               ▼                        ▼
                        ┌─────────────────┐     ┌─────────────────┐
                        │  版本切换器     │     │  具体版本驱动   │
                        └─────────────────┘     └─────────────────┘
                               │                        │
                               ▼                        ▼
                        ┌─────────────────┐     ┌─────────────────┐
                        │  版本检测器     │     │  系统环境管理   │
                        └─────────────────┘     └─────────────────┘
```

## 3. 版本驱动设计

### 3.1 驱动适配器模式

版本驱动采用适配器模式，为不同的PHP版本提供特定的安装和管理逻辑。

#### 3.1.1 驱动接口

```php
interface VersionDriverInterface
{
    /**
     * 检查版本是否支持
     */
    public function isSupported($version);

    /**
     * 安装PHP版本
     */
    public function install($version, array $options = []);

    /**
     * 删除PHP版本
     */
    public function remove($version, array $options = []);

    /**
     * 获取驱动信息
     */
    public function getInfo();
}
```

#### 3.1.2 抽象驱动基类

```php
abstract class AbstractVersionDriver implements VersionDriverInterface
{
    protected $name;
    protected $description;
    protected $versionsDir;

    public function __construct($name, $description)
    {
        $this->name = $name;
        $this->description = $description;
        $this->versionsDir = getenv('HOME') . '/.pvm/versions';
    }

    // 通用方法实现...
}
```

#### 3.1.3 具体驱动类

系统提供了多种具体驱动类，按照版本组织在子目录中：

```
src/Core/Version/Drivers/
├── PHP54/
│   ├── Base.php
│   ├── Ubuntu.php
│   └── CentOS.php
├── PHP55/
│   ├── Base.php
│   └── ...
├── PHP56/
│   ├── Base.php
│   └── ...
├── PHP71/
│   ├── Base.php
│   ├── Ubuntu.php
│   └── ...
├── PHP72/
│   ├── Base.php
│   └── ...
└── PHP73/
    ├── Base.php
    └── ...
```

每个版本目录包含：

1. **Base.php**：该版本的基础驱动类，实现通用功能
2. **特定系统驱动**：如Ubuntu.php、CentOS.php等，处理特定操作系统的适配

这种结构使得驱动类按照版本和系统类型进行清晰的组织，便于维护和扩展。

### 3.2 驱动工厂

驱动工厂负责创建和管理版本驱动实例：

```php
class VersionDriverFactory
{
    private static $drivers = [];

    /**
     * 获取适合指定版本的驱动
     */
    public static function getDriver($version = null, $system = null)
    {
        // 初始化驱动列表
        if (empty(self::$drivers)) {
            self::initDrivers();
        }

        // 如果没有指定版本，返回通用驱动
        if ($version === null) {
            return self::$drivers['generic'];
        }

        // 解析版本号
        list($major, $minor, $patch) = explode('.', $version);
        $versionKey = "php{$major}{$minor}";

        // 检查是否有特定系统的驱动
        if ($system !== null && isset(self::$drivers["{$versionKey}_{$system}"])) {
            return self::$drivers["{$versionKey}_{$system}"];
        }

        // 检查是否有版本的基础驱动
        if (isset(self::$drivers[$versionKey])) {
            return self::$drivers[$versionKey];
        }

        // 如果没有找到匹配的驱动，返回通用驱动
        return self::$drivers['generic'];
    }

    /**
     * 初始化驱动列表
     */
    private static function initDrivers()
    {
        // 通用驱动
        self::$drivers['generic'] = new GenericVersionDriver();

        // PHP 5.4 驱动
        self::$drivers['php54'] = new \VersionManager\Core\Version\Drivers\PHP54\Base();
        self::$drivers['php54_ubuntu'] = new \VersionManager\Core\Version\Drivers\PHP54\Ubuntu();
        self::$drivers['php54_centos'] = new \VersionManager\Core\Version\Drivers\PHP54\CentOS();

        // PHP 5.5 驱动
        self::$drivers['php55'] = new \VersionManager\Core\Version\Drivers\PHP55\Base();

        // PHP 5.6 驱动
        self::$drivers['php56'] = new \VersionManager\Core\Version\Drivers\PHP56\Base();

        // PHP 7.1 驱动
        self::$drivers['php71'] = new \VersionManager\Core\Version\Drivers\PHP71\Base();
        self::$drivers['php71_ubuntu'] = new \VersionManager\Core\Version\Drivers\PHP71\Ubuntu();

        // PHP 7.2 驱动
        self::$drivers['php72'] = new \VersionManager\Core\Version\Drivers\PHP72\Base();

        // PHP 7.3 驱动
        self::$drivers['php73'] = new \VersionManager\Core\Version\Drivers\PHP73\Base();

        // 动态加载驱动
        self::loadDriversFromDirectory();
    }

    /**
     * 从目录中动态加载驱动
     */
    private static function loadDriversFromDirectory()
    {
        $driversDir = __DIR__ . '/../Version/Drivers';

        // 遍历版本目录
        foreach (glob($driversDir . '/*', GLOB_ONLYDIR) as $versionDir) {
            $versionName = basename($versionDir);

            // 加载基础驱动
            $baseDriverClass = "\\VersionManager\\Core\\Version\\Drivers\\{$versionName}\\Base";
            if (class_exists($baseDriverClass)) {
                $versionKey = strtolower($versionName);
                self::$drivers[$versionKey] = new $baseDriverClass();

                // 加载特定系统驱动
                foreach (glob($versionDir . '/*.php') as $driverFile) {
                    $driverName = basename($driverFile, '.php');
                    if ($driverName !== 'Base') {
                        $driverClass = "\\VersionManager\\Core\\Version\\Drivers\\{$versionName}\\{$driverName}";
                        if (class_exists($driverClass)) {
                            $systemKey = strtolower($driverName);
                            self::$drivers["{$versionKey}_{$systemKey}"] = new $driverClass();
                        }
                    }
                }
            }
        }
    }
}
```

## 4. 版本安装流程

### 4.1 安装过程

1. 用户通过命令行界面请求安装特定版本的PHP
2. 版本安装器接收请求并验证版本格式
3. 版本安装器检查版本是否已安装
4. 版本安装器检测当前系统环境（操作系统类型、架构等）
5. 版本安装器通过驱动工厂获取适合该版本和系统的驱动
   - 首先尝试获取特定版本和系统的驱动（如PHP71_Ubuntu）
   - 如果不存在，则获取版本的基础驱动（如PHP71\Base）
   - 如果仍不存在，则使用通用驱动
6. 版本安装器将安装任务委托给选定的驱动
7. 驱动执行具体的安装步骤：
   - 下载PHP源码或二进制包
   - 安装系统依赖
   - 配置和编译PHP（如果从源码安装）
   - 安装PHP到指定目录
   - 配置PHP环境
8. 安装完成后，版本安装器更新版本缓存和配置

### 4.2 安装选项

安装过程支持多种选项，包括：

- **from_source**：是否从源码安装
- **configure_options**：自定义配置选项
- **use_cache**：是否使用缓存
- **use_multi_thread**：是否使用多线程下载
- **thread_count**：线程数
- **keep_source**：是否保留源码
- **mirror**：使用的下载站点

## 5. 版本切换设计

### 5.1 切换机制

版本切换通过以下机制实现：

1. **符号链接**：在系统路径中创建指向特定PHP版本的符号链接
2. **环境变量**：设置环境变量指向当前活动的PHP版本
3. **Shell集成**：提供Shell脚本集成，实现自动版本切换

### 5.2 切换级别

系统支持两种级别的版本切换：

1. **全局切换**：设置系统范围的默认PHP版本
2. **项目级切换**：为特定项目设置PHP版本，通过`.php-version`文件实现

### 5.3 切换流程

1. 用户请求切换到特定PHP版本
2. 版本切换器验证该版本是否已安装
3. 版本切换器更新符号链接和环境变量
4. 如果是全局切换，更新全局版本配置文件
5. 如果是项目级切换，在项目目录创建或更新`.php-version`文件

## 6. 版本驱动实现细节

### 6.1 通用版本驱动

通用版本驱动实现了对PHP 7.1及以上版本的支持，主要功能包括：

1. 从源码或二进制包安装PHP
2. 配置PHP环境
3. 管理PHP扩展
4. 删除PHP版本

### 6.2 PHP 5.x版本驱动

PHP 5.x版本驱动专门处理PHP 5.4-5.6版本的特殊需求，包括：

1. 特定的配置选项
2. 旧版本依赖处理
3. 特殊的编译参数
4. 兼容性修复

### 6.3 特定版本驱动

系统为每个PHP版本提供了特定的驱动类，存放在相应的子目录中：

#### 6.3.1 基础驱动类

每个版本目录中的`Base.php`实现了该版本的基础功能：

```php
namespace VersionManager\Core\Version\Drivers\PHP71;

use VersionManager\Core\Version\AbstractVersionDriver;

class Base extends AbstractVersionDriver
{
    public function __construct()
    {
        parent::__construct('php71', 'PHP 7.1 版本驱动');
    }

    public function isSupported($version)
    {
        // 只支持PHP 7.1.x版本
        return preg_match('/^7\.1\.\d+$/', $version);
    }

    protected function getConfigureOptions($version, array $options = [])
    {
        // 获取基本配置选项
        $configureOptions = parent::getConfigureOptions($version, $options);

        // 添加PHP 7.1特定的配置选项
        $php71Options = [
            '--with-mcrypt',
            '--enable-gd-native-ttf',
        ];

        return array_merge($configureOptions, $php71Options);
    }
}
```

#### 6.3.2 系统特定驱动类

每个版本目录中还可以包含特定系统的驱动类，如`Ubuntu.php`、`CentOS.php`等：

```php
namespace VersionManager\Core\Version\Drivers\PHP71;

class Ubuntu extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->name = 'php71_ubuntu';
        $this->description = 'PHP 7.1 Ubuntu版本驱动';
    }

    protected function getDependencies($version)
    {
        // 获取基本依赖
        $dependencies = parent::getDependencies($version);

        // 添加Ubuntu特定的依赖
        $ubuntuDependencies = [
            'libmcrypt-dev',
            'libreadline-dev',
        ];

        return array_merge($dependencies, $ubuntuDependencies);
    }

    protected function installDependencies(array $dependencies)
    {
        // Ubuntu特定的依赖安装方式
        $command = 'apt-get update && apt-get install -y ' . implode(' ', $dependencies);
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }
}
```

这种结构允许系统为不同的PHP版本和操作系统组合提供高度定制的安装和配置逻辑。

## 7. 扩展性设计

系统设计考虑了良好的扩展性：

1. **新版本支持**：只需添加新的版本驱动类
2. **新功能添加**：通过扩展现有类或添加新组件
3. **新平台支持**：通过系统环境管理器适配不同平台

## 8. 错误处理

系统实现了全面的错误处理机制：

1. **异常处理**：使用异常捕获和处理安装过程中的错误
2. **日志记录**：记录安装和切换过程的详细日志
3. **用户反馈**：提供清晰的错误消息和建议解决方案

## 9. 安全考虑

系统实现了多项安全措施：

1. **签名验证**：验证下载的PHP包的签名
2. **权限控制**：确保安装目录具有适当的权限
3. **安全更新**：检查和提示安全更新

## 10. 总结

版本安装、切换和版本驱动是PHP版本管理系统的核心组件。通过驱动适配器模式，系统能够灵活地支持不同的PHP版本，并为用户提供简单而强大的版本管理功能。

系统的模块化设计使其易于维护和扩展，能够适应未来PHP版本的变化和新功能的需求。
