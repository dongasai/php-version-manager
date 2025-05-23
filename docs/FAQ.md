# PHP Version Manager 常见问题解答 (FAQ)

## 安装问题

### Q: 安装 PVM 时出现 "Permission denied" 错误

**A:** 这通常是因为您没有足够的权限来执行安装脚本或写入目标目录。尝试以下解决方案：

1. 使用 `sudo` 运行安装脚本：

```bash
sudo bash -c "$(curl -fsSL https://raw.githubusercontent.com/dongasai/php-version-manager/main/install.sh)"
```

2. 或者，指定一个您有写入权限的目录：

```bash
curl -fsSL https://raw.githubusercontent.com/dongasai/php-version-manager/main/install.sh | bash -s -- --prefix=$HOME/pvm
```

### Q: 安装后找不到 `pvm` 命令

**A:** 这可能是因为 PVM 的 shell 初始化脚本没有被正确加载。确保您已将以下内容添加到您的 shell 配置文件（如 `~/.bashrc` 或 `~/.zshrc`）中：

```bash
export PVM_DIR="$HOME/.pvm"
[ -s "$PVM_DIR/shell/pvm.sh" ] && \. "$PVM_DIR/shell/pvm.sh"
```

然后，重新加载配置文件：

```bash
source ~/.bashrc  # 或 source ~/.zshrc
```

### Q: 安装时出现 "curl: command not found" 或 "wget: command not found" 错误

**A:** 您需要安装 `curl` 或 `wget` 工具。根据您的操作系统，使用以下命令安装：

- Ubuntu/Debian:
  ```bash
  sudo apt-get update
  sudo apt-get install curl wget
  ```

- CentOS/RHEL:
  ```bash
  sudo yum install curl wget
  ```

- Fedora:
  ```bash
  sudo dnf install curl wget
  ```

- Alpine:
  ```bash
  apk add curl wget
  ```

## PHP 版本管理问题

### Q: 安装 PHP 版本时出现编译错误

**A:** 这通常是因为缺少编译依赖。运行以下命令安装必要的依赖：

```bash
pvm init
```

如果仍然出现错误，您可能需要手动安装一些特定的依赖。根据您的操作系统，使用以下命令：

- Ubuntu/Debian:
  ```bash
  sudo apt-get update
  sudo apt-get install build-essential libssl-dev libcurl4-openssl-dev libxml2-dev libsqlite3-dev
  ```

- CentOS/RHEL:
  ```bash
  sudo yum install gcc make openssl-devel libcurl-devel libxml2-devel sqlite-devel
  ```

- Fedora:
  ```bash
  sudo dnf install gcc make openssl-devel libcurl-devel libxml2-devel sqlite-devel
  ```

- Alpine:
  ```bash
  apk add build-base openssl-dev curl-dev libxml2-dev sqlite-dev
  ```

### Q: 切换 PHP 版本后，`php -v` 仍然显示旧版本

**A:** 这可能是因为您的 PATH 环境变量中有其他 PHP 版本的路径优先级更高。尝试以下解决方案：

1. 确保 PVM 的 shell 初始化脚本已正确加载（见上文）。

2. 检查 PATH 环境变量：

```bash
echo $PATH
```

确保 `~/.pvm/bin` 在 PATH 中的位置靠前。

3. 重新加载 shell 配置：

```bash
source ~/.bashrc  # 或 source ~/.zshrc
```

4. 如果仍然不起作用，尝试使用完整路径运行 PHP：

```bash
~/.pvm/bin/php -v
```

### Q: 如何在项目中指定使用特定的 PHP 版本？

**A:** 您可以在项目根目录中创建一个 `.php-version` 文件，其中包含您想要使用的 PHP 版本号：

```bash
echo "8.2.0" > .php-version
```

当您进入该目录时，PVM 将自动切换到指定的 PHP 版本（如果已安装）。

### Q: 如何删除所有已安装的 PHP 版本？

**A:** 您可以使用以下命令列出并删除所有已安装的 PHP 版本：

```bash
for version in $(pvm list | grep -v "No PHP versions installed"); do
  pvm remove $version
done
```

或者，您可以直接删除 PVM 目录：

```bash
rm -rf ~/.pvm
```

然后重新安装 PVM。

## 扩展管理问题

### Q: 安装扩展时出现 "phpize: command not found" 错误

**A:** 这通常是因为您当前使用的 PHP 版本没有 `phpize` 工具。确保您已安装 PHP 开发包：

```bash
pvm init
```

### Q: 如何安装特定版本的扩展？

**A:** 您可以使用 `pvm ext install` 命令的 `--version` 选项：

```bash
pvm ext install redis --version=5.3.7
```

### Q: 安装扩展后，`php -m` 中没有显示该扩展

**A:** 这可能是因为扩展没有被正确启用。尝试以下解决方案：

1. 手动启用扩展：

```bash
pvm ext enable <扩展名>
```

2. 检查 PHP 配置目录中的扩展配置文件：

```bash
ls -la ~/.pvm/versions/$(pvm current)/etc/conf.d/
```

确保存在一个名为 `<扩展名>.ini` 的文件，并且其中包含 `extension=<扩展名>.so` 行。

3. 重新启动 PHP-FPM（如果使用）：

```bash
pvm service restart
```

### Q: 如何查看扩展的配置选项？

**A:** 您可以使用 `php --ri` 命令查看扩展的配置信息：

```bash
php --ri <扩展名>
```

例如：

```bash
php --ri redis
```

## Composer 管理问题

### Q: 安装 Composer 时出现 "PHP requires allow_url_fopen to be enabled" 错误

**A:** 您需要在 PHP 配置中启用 `allow_url_fopen` 选项。编辑 PHP 配置文件：

```bash
pvm config edit
```

然后添加或修改以下行：

```ini
allow_url_fopen = On
```

保存并退出，然后重试安装 Composer。

### Q: 如何为不同的 PHP 版本安装不同的 Composer 版本？

**A:** PVM 会为每个 PHP 版本单独安装 Composer。您可以使用以下命令为当前 PHP 版本安装特定版本的 Composer：

```bash
pvm composer install <版本>
```

例如：

```bash
pvm composer install 2.5.8
```

### Q: Composer 安装后找不到 `composer` 命令

**A:** 确保 PVM 的 bin 目录在您的 PATH 中：

```bash
echo $PATH
```

如果没有，将以下内容添加到您的 shell 配置文件中：

```bash
export PATH="$HOME/.pvm/bin:$PATH"
```

然后重新加载配置：

```bash
source ~/.bashrc  # 或 source ~/.zshrc
```

## Web 管理界面问题

### Q: 启动 Web 管理界面时出现 "Address already in use" 错误

**A:** 这表示指定的端口已被其他程序占用。尝试使用不同的端口：

```bash
pvm web --port=8080
```

### Q: Web 管理界面无法访问

**A:** 检查以下几点：

1. 确保 Web 服务器正在运行：

```bash
ps aux | grep php
```

2. 检查防火墙设置，确保端口已开放：

```bash
sudo ufw status  # Ubuntu
sudo firewall-cmd --list-all  # CentOS/RHEL/Fedora
```

3. 如果您想从其他机器访问，请使用 `--host=0.0.0.0` 选项：

```bash
pvm web --host=0.0.0.0 --port=8000
```

### Q: Web 管理界面显示 "Internal Server Error"

**A:** 检查 PHP 错误日志：

```bash
tail -f ~/.pvm/logs/web-error.log
```

这可能是由于 PHP 配置问题或权限问题导致的。

## 其他问题

### Q: 如何更新 PVM？

**A:** 使用以下命令更新 PVM：

```bash
pvm update
```

### Q: 如何完全卸载 PVM？

**A:** 删除 PVM 目录并从 shell 配置文件中移除相关行：

```bash
rm -rf ~/.pvm
```

然后编辑您的 shell 配置文件（如 `~/.bashrc` 或 `~/.zshrc`），删除以下行：

```bash
export PVM_DIR="$HOME/.pvm"
[ -s "$PVM_DIR/shell/pvm.sh" ] && \. "$PVM_DIR/shell/pvm.sh"
```

### Q: PVM 是否支持 Windows？

**A:** 目前，PVM 主要设计用于 Linux 和 macOS 系统。Windows 支持有限，建议在 Windows 上使用 WSL（Windows Subsystem for Linux）来运行 PVM。

### Q: 如何贡献代码或报告问题？

**A:** 您可以在 GitHub 仓库上提交问题或拉取请求：

https://github.com/dongasai/php-version-manager

### Q: 如何查看 PVM 的日志？

**A:** PVM 的日志文件位于 `~/.pvm/logs` 目录：

```bash
ls -la ~/.pvm/logs
```

您可以使用 `tail` 命令查看最新的日志：

```bash
tail -f ~/.pvm/logs/pvm.log
```

### Q: 如何备份 PVM 配置和已安装的 PHP 版本？

**A:** 您可以备份整个 PVM 目录：

```bash
tar -czf pvm-backup.tar.gz ~/.pvm
```

要恢复备份：

```bash
tar -xzf pvm-backup.tar.gz -C ~/
```

### Q: PVM 是否支持自定义编译选项？

**A:** 是的，您可以在安装 PHP 版本时指定自定义编译选项：

```bash
pvm install 8.2.0 --configure-options="--with-openssl --with-zlib --enable-bcmath"
```

或者，您可以在配置文件中设置默认的编译选项：

```bash
pvm config set default_configure_options "--with-openssl --with-zlib --enable-bcmath"
```
