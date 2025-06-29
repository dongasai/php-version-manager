# 修复PVM交互模式colorize方法类型错误

## 问题描述

执行 `./bin/pvm` 命令启动后，输入2选择"PHP版本管理"时出现以下错误：

```
PHP Fatal error:  Uncaught TypeError: Unsupported operand types: int + string in /data/wwwroot/php/pvm/src/Console/UI/ConsoleUI.php:144
Stack trace:
#0 /data/wwwroot/php/pvm/src/Console/Commands/VersionMenuCommand.php(106): VersionManager\Console\UI\ConsoleUI->colorize()
#1 /data/wwwroot/php/pvm/src/Console/Commands/AbstractMenuCommand.php(92): VersionManager\Console\Commands\VersionMenuCommand->showWelcome()
#2 /data/wwwroot/php/pvm/src/Console/Commands/InteractiveCommand.php(222): VersionManager\Console\Commands\AbstractMenuCommand->execute()
#3 /data/wwwroot/php/pvm/src/Console/Commands/InteractiveCommand.php(171): VersionManager\Console\Commands\InteractiveCommand->manageVersions()
#4 /data/wwwroot/php/pvm/src/Console/Commands/AbstractMenuCommand.php(136): VersionManager\Console\Commands\InteractiveCommand->handleMenuOption()
#5 /data/wwwroot/php/pvm/src/Console/Commands/AbstractMenuCommand.php(95): VersionManager\Console\Commands\AbstractMenuCommand->runInteractiveLoop()
#6 /data/wwwroot/php/pvm/src/Console/Application.php(116): VersionManager\Console\Commands\AbstractMenuCommand->execute()
#7 /data/wwwroot/php/pvm/bin/pvm(169): VersionManager\Console\Application->run()
#8 {main}
  thrown in /data/wwwroot/php/pvm/src/Console/UI/ConsoleUI.php on line 144
```

## 问题分析

错误发生在 `ConsoleUI.php` 的 `colorize` 方法中，第144行尝试执行 `30 + $foreground` 操作时，`$foreground` 是字符串而不是整数，导致类型错误。

通过代码分析发现，多个地方调用 `colorize` 方法时传入了字符串颜色名称（如 `'green'`）而不是颜色常量：

- `src/Console/Commands/ComposerMenuCommand.php:106`
- `src/Console/Commands/VersionMenuCommand.php:106`
- `src/Console/Commands/ServiceMenuCommand.php:106`
- `src/Console/Commands/ExtensionMenuCommand.php:106`

## 解决方案

修改 `ConsoleUI.php` 中的 `colorize` 方法，使其能够同时处理颜色常量（整数）和颜色名称字符串：

### 修改内容

1. **更新方法签名和文档**：
   - 将参数类型从 `int` 改为 `int|string`
   - 更新注释说明支持颜色名称字符串

2. **添加颜色代码转换方法**：
   - 新增 `getColorCode()` 私有方法
   - 支持字符串颜色名称到颜色常量的转换
   - 支持的颜色名称：black, red, green, yellow, blue, magenta, cyan, white

3. **修改颜色处理逻辑**：
   - 在设置前景色和背景色时，先通过 `getColorCode()` 方法转换颜色值
   - 保持向后兼容性，仍然支持直接传入颜色常量

## 测试结果

修复后测试：
1. 执行 `./bin/pvm` 正常启动交互界面
2. 输入 `2` 选择"PHP版本管理"，成功进入版本管理菜单
3. 输入 `1` 查看已安装版本，正常显示版本信息
4. 所有颜色显示正常，无错误信息

## 影响范围

此修复解决了所有使用字符串颜色名称调用 `colorize` 方法的地方，包括：
- ComposerMenuCommand
- VersionMenuCommand  
- ServiceMenuCommand
- ExtensionMenuCommand

## 文件修改

- `src/Console/UI/ConsoleUI.php`：修改 `colorize` 方法，添加 `getColorCode` 方法

## 后续发现的问题

在修复colorize方法后，发现了另一个显示不一致的问题：

### 问题描述
主界面显示"已安装版本: 6 个"，但进入版本管理查看时却显示"没有通过PVM安装的PHP版本"。

### 问题原因
- `InteractiveCommand::showCurrentStatus()` 使用 `VersionManager::getInstalledVersions()`，它调用 `VersionSwitcher::getInstalledVersions()`，返回包含系统版本和所有PVM目录的详细信息数组
- `ListCommand::execute()` 使用 `VersionDetector::getInstalledVersions()`，只返回有效PHP二进制文件的版本

检查发现PVM版本目录存在但没有有效的PHP二进制文件：
```
~/.pvm/versions/7.4.33/     - 无 bin/php
~/.pvm/versions/8.1/        - 无 bin/php
~/.pvm/versions/8.1.2-1ubuntu2.21/ - 无 bin/php
~/.pvm/versions/8.1.27/     - 无 bin/php
~/.pvm/versions/8.3.21/     - 无 bin/php
```

### 解决方案
修改 `InteractiveCommand::showCurrentStatus()` 方法，使用 `VersionDetector::getInstalledVersions()` 来获取真正有效的已安装版本，确保显示信息的一致性。

### 修复结果
- ✅ 主界面显示"已安装版本: 0 个"
- ✅ 版本管理界面显示"没有通过PVM安装的PHP版本"
- ✅ 信息显示完全一致

## 文件修改

- `src/Console/UI/ConsoleUI.php`：修改 `colorize` 方法，添加 `getColorCode` 方法
- `src/Console/Commands/InteractiveCommand.php`：修改 `showCurrentStatus` 方法，使用VersionDetector获取有效版本

## 总结

通过两个修复：
1. 增强 `colorize` 方法支持字符串颜色名称，解决了类型错误问题
2. 统一版本信息显示逻辑，解决了信息不一致问题

彻底解决了PVM交互模式中的显示错误和信息不一致问题，提升了用户体验。
