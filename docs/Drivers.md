# 驱动匹配文档

## 总体概述

PVM（PHP Version Manager）使用驱动系统来处理不同PHP版本和扩展在不同操作系统和架构上的安装、配置和管理。驱动匹配系统负责根据目标环境选择最合适的驱动实现。

PVM包含三种主要的驱动类型：
1. **版本驱动（Version Driver）** - 负责PHP版本的安装和管理
2. **扩展驱动（Extension Driver）** - 负责PHP扩展的安装和管理
3. **操作系统驱动（OS Driver）** - 负责检测和处理不同操作系统环境的特性

# 版本驱动匹配规则

## 优化版本

> 标签匹配机制的驱动匹配算法

1. 找出可选择驱动
    * 在执行目录下查找所有驱动文件
2. 获取所有驱的标签
3. 通过必选标签比较,移除不可用的驱动
    * 版本驱动,PHP的版本就是必须的
    * 扩展驱动,扩展的名字就是必须的
    * 操作系统驱动，发行版名字必须的
4. 对非必须的标签进行命中,命中标签最多的就是最匹配的驱动

## 版本驱动类层次结构

驱动类的层次结构如下：

1. `VersionDriverInterface` - 定义所有驱动必须实现的接口
2. `AbstractVersionDriver` - 提供通用功能的抽象基类
3. `GenericVersionDriver` - 通用驱动实现，用于处理没有特定驱动的情况
4. 特定版本驱动 - 针对特定PHP版本的驱动实现（如PHP71、PHP80等）
5. 特定环境驱动 - 针对特定操作系统、版本和架构的驱动实现

## 版本驱动命名规则

特定环境驱动的命名遵循以下规则：

- 基础驱动: `Base.php`
- 特定发行版驱动: `{发行版}.php`（例如：`Ubuntu.php`）
- 特定发行版和版本驱动: `{发行版}_{版本}.php`（例如：`Ubuntu_2204.php`）
- 特定发行版和架构驱动: `{发行版}_{架构}.php`（例如：`Ubuntu_X8664.php`）
- 特定发行版、版本和架构驱动: `{发行版}_{版本}_{架构}.php`（例如：`Ubuntu_2204_X8664.php`）

注意：版本号中的点号(.)会被替换为下划线(_)，例如版本`22.04`会变成`22_04`。

## 版本驱动匹配算法

当需要为特定PHP版本和环境选择驱动时，系统按照以下步骤进行匹配：

1. 根据PHP版本（如7.4.0）提取主要和次要版本号，生成PHP版本键（如PHP74）
2. 获取当前环境信息（发行版、版本和架构）
3. 查找对应PHP版本目录下的所有驱动文件
4. 应用匹配规则，选择最合适的驱动

### 匹配规则优先级

1. 精确匹配：如果存在与当前环境完全匹配的驱动（发行版+版本+架构），则直接使用该驱动
2. 部分匹配：如果存在与当前环境部分匹配的驱动（如只匹配发行版和架构），则使用匹配度最高的驱动
3. 基础匹配：如果没有找到特定环境匹配的驱动，但存在基础驱动（Base.php），则使用基础驱动
4. 通用驱动：如果以上都没有找到，则使用通用驱动（GenericVersionDriver）

### 匹配分数计算

对于部分匹配的情况，系统会计算每个驱动的匹配分数：

1. 将驱动类名按下划线分割成标签（如`Ubuntu_2204_X8664`分割为`ubuntu`、`2204`、`x8664`）
2. 将当前环境信息也转换为标签
3. 计算两组标签的交集大小作为匹配分数
4. 选择匹配分数最高的驱动

## 版本驱动缓存机制

为了提高性能，系统会缓存已创建的驱动实例：

1. 根据PHP版本、发行版、版本和架构生成唯一的驱动键
2. 如果缓存中已存在该键对应的驱动实例，则直接返回
3. 否则创建新的驱动实例并缓存

## 版本驱动注册

系统支持通过配置文件或代码动态注册自定义驱动：

1. 配置文件：在`config/versions/driver_map.php`中定义驱动映射
2. 代码注册：使用`VersionDriverFactory::registerDriver()`方法注册

## 版本驱动目录结构

驱动文件按照PHP版本组织在以下目录结构中：

```
src/Core/Version/Drivers/
├── PHP54/
│   ├── Base.php
│   ├── Ubuntu.php
│   └── ...
├── PHP55/
│   ├── Base.php
│   └── ...
...
├── PHP74/
│   ├── Base.php
│   ├── Ubuntu.php
│   ├── Ubuntu_2204.php
│   ├── Ubuntu_X8664.php
│   └── ...
...
└── PHP82/
    ├── Base.php
    └── ...
```

## 版本驱动示例

### 示例1：匹配PHP 7.4在Ubuntu 22.04 x86_64上的驱动

1. PHP版本键：PHP74
2. 环境标签：`ubuntu`、`22_04`、`x86_64`
3. 匹配过程：
   - 检查是否存在`Ubuntu_2204_X8664.php`（精确匹配）
   - 如果不存在，检查部分匹配（如`Ubuntu_2204.php`或`Ubuntu_X8664.php`）
   - 计算匹配分数，选择最高分的驱动
   - 如果没有匹配，使用`Base.php`
   - 如果`Base.php`也不存在，使用通用驱动

### 示例2：自定义驱动注册

```php
// 注册自定义驱动
VersionDriverFactory::registerDriver('php74_myos', MyCustomDriver::class);
```

# 扩展驱动匹配规则

## 扩展驱动概述

扩展驱动负责PHP扩展的安装、配置和管理。与版本驱动类似，扩展驱动也根据目标环境（PHP版本、操作系统、架构等）选择最合适的实现。

## 扩展驱动类层次结构

扩展驱动的类层次结构如下：

1. `ExtensionDriverInterface` - 定义所有扩展驱动必须实现的接口
2. `AbstractExtensionDriver` - 提供通用功能的抽象基类
3. `GenericExtensionDriver` - 通用扩展驱动实现，用于处理没有特定驱动的扩展
4. 特定扩展驱动 - 针对特定扩展的驱动实现（如Xdebug、GD等）
5. 特定环境扩展驱动 - 针对特定环境的扩展驱动实现

## 扩展驱动命名规则

扩展驱动的命名遵循以下规则：

- 基础驱动: `Base.php`
- 特定PHP版本驱动: `Php{版本}.php`（例如：`Php74.php`）
- 特定发行版驱动: `{发行版}.php`（例如：`Ubuntu.php`）
- 特定发行版和PHP版本驱动: `{发行版}Php{版本}.php`（例如：`UbuntuPhp74.php`）
- 特定发行版和架构驱动: `{发行版}{架构}.php`（例如：`UbuntuX8664.php`）

## 扩展驱动目录结构

扩展驱动文件按照扩展名称组织在以下目录结构中：

```
src/Core/Extension/Drivers/
├── Gd/
│   ├── Base.php
│   ├── Ubuntu.php
│   ├── UbuntuX8664.php
│   └── ...
├── Xdebug/
│   ├── Base.php
│   ├── Php74.php
│   └── ...
...
└── Zip/
    ├── Base.php
    └── ...
```

## 扩展驱动匹配算法

当需要为特定扩展和环境选择驱动时，系统按照以下步骤进行匹配：

1. 生成驱动键，格式为 `[php版本]:[发行版]:[发行版版本]:[架构]:[扩展名]`
2. 检查驱动缓存，如果已有实例则直接返回
3. 尝试从驱动映射配置中查找匹配的驱动类
4. 如果没有找到，则按照以下优先级查找：
   - 完全匹配（PHP版本+发行版+版本+架构）
   - 发行版和架构匹配
   - 发行版匹配
   - 通用匹配
5. 如果仍未找到，则使用通用扩展驱动（GenericExtensionDriver）

### 匹配分数计算

对于扩展驱动的匹配，系统使用分数机制来确定最佳匹配：

1. 基础分数：1分
2. PHP版本完全匹配（如Php74）：25分
3. PHP主版本匹配（如Php7）：20分
4. 发行版和架构完全匹配：15分
5. 发行版和版本完全匹配：15分
6. 发行版匹配：10分
7. 架构匹配：5分

系统选择得分最高的驱动作为最佳匹配。

## 扩展驱动配置

扩展驱动可以通过配置文件进行映射，配置文件位于 `config/extensions/driver_map.php`：

```php
return [
    // 基本扩展映射
    'gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\Base',
    'xdebug' => 'VersionManager\\Core\\Extension\\Drivers\\Xdebug\\Base',

    // 特定发行版的扩展映射
    'ubuntu:gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\Ubuntu',

    // 特定发行版和架构的扩展映射
    'ubuntu:x86_64:gd' => 'VersionManager\\Core\\Extension\\Drivers\\Gd\\UbuntuX8664',
];
```

## 扩展驱动注册

可以通过代码动态注册自定义扩展驱动：

```php
// 注册自定义扩展驱动
ExtensionDriverFactory::registerDriver('redis', MyCustomRedisDriver::class);

// 注册特定环境的扩展驱动
ExtensionDriverFactory::registerDriver('ubuntu:redis', UbuntuRedisDriver::class);
```

## 扩展驱动示例

### 示例1：匹配Redis扩展在Ubuntu 22.04 x86_64上的驱动

1. 驱动键：`php7.4:ubuntu:22.04:x86_64:redis`
2. 匹配过程：
   - 检查驱动映射中是否有完全匹配的驱动
   - 检查是否有`ubuntu:x86_64:redis`的驱动
   - 检查是否有`ubuntu:redis`的驱动
   - 检查是否有通用的`redis`驱动
   - 如果都没有，使用GenericExtensionDriver

### 示例2：安装Xdebug扩展

```php
// 获取Xdebug扩展驱动
$driver = ExtensionDriverFactory::getDriver('xdebug', '7.4', 'ubuntu', '22.04', 'x86_64');

// 安装扩展
$driver->install('7.4', [
    'config' => [
        'remote_enable' => '1',
        'remote_host' => 'localhost',
        'remote_port' => '9000'
    ]
]);
```

# 操作系统驱动匹配规则

## 操作系统驱动概述

操作系统驱动负责检测和处理不同操作系统环境的特性，为版本驱动和扩展驱动提供操作系统相关的信息和功能。操作系统驱动是PVM系统的基础组件，它能够自动识别当前运行的操作系统类型、版本和架构，并提供相应的系统操作接口。

## 操作系统驱动类层次结构

操作系统驱动的类层次结构如下：

1. `OsDriverInterface` - 定义所有操作系统驱动必须实现的接口
2. `AbstractOsDriver` - 提供通用功能的抽象基类
3. 特定操作系统驱动 - 针对特定操作系统的驱动实现（如UbuntuDriver、CentosDriver等）
4. `GenericLinuxDriver` - 通用Linux驱动，用于处理没有特定驱动的Linux发行版

## 操作系统驱动命名规则

操作系统驱动的命名遵循以下规则：

- 特定操作系统驱动: `{操作系统}Driver.php`（例如：`UbuntuDriver.php`、`CentosDriver.php`）
- 通用Linux驱动: `GenericLinuxDriver.php`

## 操作系统驱动目录结构

操作系统驱动文件组织在以下目录结构中：

```
src/Core/System/
├── Drivers/
│   ├── AlpineDriver.php
│   ├── CentosDriver.php
│   ├── DebianDriver.php
│   ├── FedoraDriver.php
│   ├── GenericLinuxDriver.php
│   └── UbuntuDriver.php
├── AbstractOsDriver.php
├── OsDriverFactory.php
└── OsDriverInterface.php
```

## 操作系统驱动匹配算法

当需要获取操作系统驱动实例时，系统按照以下步骤进行匹配：

1. 检测操作系统类型（如Linux、Windows等）
2. 如果是Linux系统，则尝试从`/etc/os-release`获取发行版信息
3. 根据发行版信息，尝试加载对应的驱动类（如UbuntuDriver、CentosDriver等）
4. 如果没有找到特定的发行版驱动，则尝试通过其他方法检测发行版：
   - 检查`/etc/debian_version`判断是否为Debian或Ubuntu
   - 检查`/etc/centos-release`判断是否为CentOS
   - 检查`/etc/fedora-release`判断是否为Fedora
   - 检查`/etc/alpine-release`判断是否为Alpine
5. 如果仍未找到匹配的驱动，则使用通用Linux驱动（GenericLinuxDriver）
6. 如果不是Linux系统或没有找到适合的驱动，则创建一个匿名的通用驱动

## 操作系统驱动功能

操作系统驱动提供以下主要功能：

1. **系统信息检测**：自动检测操作系统类型、版本和架构
2. **环境信息提供**：为版本驱动和扩展驱动提供操作系统环境信息
3. **命令执行**：提供执行系统命令的统一接口
4. **依赖管理**：处理不同操作系统上的依赖安装

## 操作系统驱动示例

### 示例1：获取操作系统信息

```php
// 获取操作系统驱动实例
$osDriver = OsDriverFactory::getInstance();

// 获取操作系统信息
$osInfo = $osDriver->getInfo();

// 输出操作系统信息
echo "操作系统: {$osInfo['name']} {$osInfo['version']}\n";
echo "架构: {$osInfo['arch']}\n";
echo "内核版本: {$osInfo['kernel']}\n";
```

### 示例2：在版本驱动中使用操作系统信息

```php
// 在版本驱动中获取操作系统信息
protected function installDependencies()
{
    $osInfo = $this->getOsInfo();

    switch ($osInfo['type']) {
        case 'ubuntu':
        case 'debian':
            $this->installDebianDependencies();
            break;
        case 'centos':
        case 'fedora':
            $this->installRhelDependencies();
            break;
        case 'alpine':
            $this->installAlpineDependencies();
            break;
        default:
            throw new \Exception("不支持的操作系统类型: {$osInfo['type']}");
    }
}
```