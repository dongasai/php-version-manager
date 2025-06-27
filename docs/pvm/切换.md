# PHP 版本切换

PVM 提供了两种切换 PHP 版本的方式：永久切换和临时切换。

## 永久切换 vs 临时切换

| 命令 | 作用范围 | 持久性 | 影响 |
|------|---------|-------|------|
| `pvm use` | 全局 | 永久 | 所有终端会话和应用程序 |
| `pvm switch` | 当前终端 | 临时 | 仅当前终端会话 |

## 永久切换 PHP 版本

使用 `pvm use` 命令可以永久切换 PHP 版本。此命令会修改系统环境变量和符号链接，切换后的 PHP 版本将在所有终端会话中生效。

```bash
# 切换到 PHP 7.4.33
pvm use 7.4.33

# 切换到 PHP 8.1.27 并设置为全局版本
pvm use 8.1.27 --global

# 设置当前项目的 PHP 版本为 8.1.27
pvm use 8.1.27 --project
```

永久切换的特点：

- 修改系统环境变量和符号链接
- 影响所有终端会话和应用程序
- 重启终端后仍然生效
- 可以设置为全局版本或项目级别版本

## 临时切换 PHP 版本

使用 `pvm switch` 命令可以临时切换 PHP 版本。此命令只会在当前终端会话中修改 PATH 环境变量，不会影响其他终端会话或应用程序。

```bash
# 显示如何临时切换到 PHP 7.4.33 的说明
pvm switch 7.4.33

# 临时切换到 PHP 7.4.33（推荐用法）
eval $(pvm switch 7.4.33 --eval)
```

临时切换的特点：

- 只修改当前终端会话的 PATH 环境变量
- 不影响其他终端会话或应用程序
- 关闭终端后自动恢复到默认 PHP 版本
- 适合临时测试或运行特定 PHP 版本的脚本

## 使用场景

### 永久切换（`pvm use`）

- 当你需要长期使用某个 PHP 版本进行开发
- 当你需要为整个系统设置默认 PHP 版本
- 当你需要为特定项目设置 PHP 版本

### 临时切换（`pvm switch`）

- 当你需要临时测试代码在不同 PHP 版本下的兼容性
- 当你需要运行只兼容特定 PHP 版本的脚本
- 当你不想影响系统或其他终端会话的 PHP 版本

## 示例

### 永久切换

```bash
# 切换到 PHP 7.4.33
$ pvm use 7.4.33
已切换到PHP版本 7.4.33

# 验证 PHP 版本
$ php -v
PHP 7.4.33 (cli) (built: Feb 14 2023 18:31:23) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
```

### 临时切换

```bash
# 临时切换到 PHP 8.1.27
$ eval $(pvm switch 8.1.27 --eval)

# 验证 PHP 版本
$ php -v
PHP 8.1.27 (cli) (built: Feb 14 2023 18:45:12) ( NTS )
Copyright (c) The PHP Group
Zend Engine v4.1.27, Copyright (c) Zend Technologies

# 关闭终端后，再次打开终端
$ php -v
PHP 7.4.33 (cli) (built: Feb 14 2023 18:31:23) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
```

## 在 Shell 配置文件中添加别名

为了更方便地使用临时切换功能，你可以在 Shell 配置文件（如 `~/.bashrc` 或 `~/.zshrc`）中添加以下别名：

```bash
# 添加临时切换 PHP 版本的别名
alias php74="eval $(pvm switch 7.4.33 --eval)"
alias php80="eval $(pvm switch 8.0.30 --eval)"
alias php81="eval $(pvm switch 8.1.27 --eval)"
alias php82="eval $(pvm switch 8.2.17 --eval)"
```

这样，你就可以使用简单的命令临时切换 PHP 版本：

```bash
# 临时切换到 PHP 7.4
$ php74

# 临时切换到 PHP 8.1
$ php81
```

## 注意事项

1. 永久切换（`pvm use`）会影响所有终端会话和应用程序，包括 Web 服务器。
2. 临时切换（`pvm switch`）只影响当前终端会话，不会影响 Web 服务器或其他终端会话。
3. 如果你使用的是项目级别的 PHP 版本，临时切换可能会覆盖项目级别的设置，但只在当前终端会话中有效。
4. 临时切换后，如果你打开新的终端会话，新会话将使用永久设置的 PHP 版本。
