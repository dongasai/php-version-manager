# 输出级别实现示例

## 概述

本文档提供了如何在PVM代码中实现三级输出的具体示例。

## 基本实现模式

### 1. 替换现有的echo语句

**旧代码**
```php
echo "正在安装依赖...\n";
echo "依赖安装完成\n";
```

**新代码**
```php
\VersionManager\Core\Logger\Logger::info("正在安装依赖...");
\VersionManager\Core\Logger\Logger::success("依赖安装完成");
```

### 2. 添加详细模式输出

**示例：在命令执行时添加详细输出**
```php
public function executeCommand($command)
{
    \VersionManager\Core\Logger\Logger::verbose("执行命令: $command");
    
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    
    // 在详细模式下显示命令输出
    if (\VersionManager\Core\Logger\Logger::isVerbose()) {
        foreach ($output as $line) {
            \VersionManager\Core\Logger\Logger::verbose("  $line");
        }
    }
    
    if ($returnCode === 0) {
        \VersionManager\Core\Logger\Logger::success("命令执行成功");
    } else {
        \VersionManager\Core\Logger\Logger::error("命令执行失败");
    }
    
    return $returnCode === 0;
}
```

### 3. 条件性输出

**示例：根据级别显示不同详细程度的信息**
```php
public function installPackages(array $packages)
{
    if (empty($packages)) {
        \VersionManager\Core\Logger\Logger::info("所有依赖包已安装");
        return true;
    }
    
    // 默认模式：显示要安装的包
    \VersionManager\Core\Logger\Logger::info("安装依赖包: " . implode(' ', $packages));
    
    // 详细模式：显示包检查过程
    if (\VersionManager\Core\Logger\Logger::isVerbose()) {
        \VersionManager\Core\Logger\Logger::verbose("检查已安装的依赖包...");
        foreach ($packages as $package) {
            $installed = $this->isPackageInstalled($package);
            $status = $installed ? "已安装" : "未安装";
            \VersionManager\Core\Logger\Logger::verbose("  $package: $status");
        }
    }
    
    // 执行安装...
    $result = $this->doInstall($packages);
    
    if ($result) {
        \VersionManager\Core\Logger\Logger::success("依赖包安装成功");
    }
    
    return $result;
}
```

## 具体组件实现

### UbuntuDriver 实现示例

```php
public function updatePackageCache(array $options = [])
{
    \VersionManager\Core\Logger\Logger::info("更新软件包列表...");

    $command = 'apt-get update';
    list($output, $returnCode) = $this->executeWithPrivileges($command, $options);

    // 详细模式：显示完整输出
    if (\VersionManager\Core\Logger\Logger::isVerbose()) {
        \VersionManager\Core\Logger\Logger::verbose("执行命令: sudo $command");
        foreach ($output as $line) {
            \VersionManager\Core\Logger\Logger::verbose("  $line");
        }
    }

    if ($returnCode === 0) {
        \VersionManager\Core\Logger\Logger::success("软件包列表更新成功");
        return true;
    }

    // 错误处理...
    $outputStr = implode("\n", $output);
    
    // 网络问题处理（ESM源超时）
    if (strpos($outputStr, '连接超时') !== false) {
        \VersionManager\Core\Logger\Logger::warning("部分软件源连接失败，但主要软件源可用");
        return true;
    }

    throw new \Exception("更新软件包列表失败: " . $outputStr);
}
```

### VersionDriver 实现示例

```php
protected function installDependencies(array $dependencies)
{
    if (empty($dependencies)) {
        return true;
    }

    \VersionManager\Core\Logger\Logger::info("安装系统依赖...");

    // 详细模式：显示依赖分析过程
    if (\VersionManager\Core\Logger\Logger::isVerbose()) {
        $osDriver = $this->getOsDriver();
        \VersionManager\Core\Logger\Logger::verbose("检测到操作系统: " . $osDriver->getName() . " " . $osDriver->getVersion());
        \VersionManager\Core\Logger\Logger::verbose("检测到包管理器: " . $osDriver->getPackageManager());
        \VersionManager\Core\Logger\Logger::verbose("依赖列表: " . implode(' ', $dependencies));
    }

    try {
        $osDriver = $this->getOsDriver();
        
        // 更新包缓存（会根据级别显示相应信息）
        $osDriver->updatePackageCache();
        
        // 安装依赖包（会根据级别显示相应信息）
        $osDriver->installPackages($dependencies);

        \VersionManager\Core\Logger\Logger::success("系统依赖安装完成");
        return true;
    } catch (\Exception $e) {
        throw new \Exception("安装系统依赖失败: " . $e->getMessage());
    }
}
```

## 迁移指南

### 步骤1：引入Logger类

在需要输出的文件顶部添加：
```php
use VersionManager\Core\Logger\Logger;
```

### 步骤2：替换echo语句

按照以下规则替换：

| 原始代码 | 替换为 | 说明 |
|----------|--------|------|
| `echo "信息\n";` | `Logger::info("信息");` | 一般信息 |
| `echo "\033[32m成功\033[0m\n";` | `Logger::success("成功");` | 成功信息 |
| `echo "\033[33m警告\033[0m\n";` | `Logger::warning("警告");` | 警告信息 |
| `echo "\033[31m错误\033[0m\n";` | `Logger::error("错误");` | 错误信息 |

### 步骤3：添加详细输出

在关键操作处添加详细输出：
```php
// 在命令执行前
Logger::verbose("执行命令: $command");

// 在循环处理时
if (Logger::isVerbose()) {
    foreach ($items as $item) {
        Logger::verbose("处理: $item");
    }
}
```

### 步骤4：测试不同级别

```bash
# 测试静默模式
./bin/pvm install --silent -y 7.1

# 测试默认模式
./bin/pvm install -y 7.1

# 测试详细模式
./bin/pvm install --verbose -y 7.1
```

## 注意事项

1. **错误和警告总是显示**：使用 `Logger::error()` 和 `Logger::warning()` 的信息在所有级别都会显示
2. **成功信息总是显示**：使用 `Logger::success()` 的信息在所有级别都会显示
3. **避免重复输出**：不要在同一个操作中既使用 `echo` 又使用 `Logger`
4. **保持一致性**：同类型的操作应该使用相同的输出级别
5. **性能考虑**：在详细模式下可以输出更多信息，但要注意性能影响
