# PVM PHP Docker 容器示例

这个目录包含了使用 PHP Version Manager (PVM) 构建的 PHP 7.1 到 8.3 各版本的 Docker 容器示例。

## 目录结构

```
.
├── Dockerfile-php7.1-ubuntu  # PHP 7.1 Dockerfile
├── Dockerfile-php7.2-ubuntu  # PHP 7.2 Dockerfile
├── Dockerfile-php7.3-ubuntu  # PHP 7.3 Dockerfile
├── Dockerfile-php7.4-ubuntu  # PHP 7.4 Dockerfile
├── Dockerfile-php8.0-ubuntu  # PHP 8.0 Dockerfile
├── Dockerfile-php8.1-ubuntu  # PHP 8.1 Dockerfile
├── Dockerfile-php8.2-ubuntu  # PHP 8.2 Dockerfile
├── Dockerfile-php8.3-ubuntu  # PHP 8.3 Dockerfile
├── docker-compose.yml        # Docker Compose 配置文件
├── nginx                     # Nginx 配置目录
│   └── conf.d                # Nginx 站点配置目录
│       └── default.conf      # 默认站点配置
├── README.md                 # 本文件
└── www                       # Web 根目录
    ├── index.php             # 首页
    └── info.php              # PHP 信息页面
```

## 使用方法

### 构建和启动容器

```bash
# 构建并启动所有容器
docker-compose up -d

# 构建并启动特定版本的容器
docker-compose up -d php74 php80

# 重新构建容器
docker-compose up -d --build
```

### 访问 PHP 信息页面

启动容器后，可以通过以下 URL 访问不同版本的 PHP 信息页面：

- PHP 7.1: http://localhost:8080/php71/info.php
- PHP 7.2: http://localhost:8080/php72/info.php
- PHP 7.3: http://localhost:8080/php73/info.php
- PHP 7.4: http://localhost:8080/php74/info.php
- PHP 8.0: http://localhost:8080/php80/info.php
- PHP 8.1: http://localhost:8080/php81/info.php
- PHP 8.2: http://localhost:8080/php82/info.php
- PHP 8.3: http://localhost:8080/php83/info.php

或者访问首页查看所有版本的链接：http://localhost:8080

### 停止容器

```bash
# 停止所有容器
docker-compose down

# 停止并删除所有容器、网络和卷
docker-compose down -v
```

## 自定义

### 添加 PHP 扩展

可以通过修改 Dockerfile 中的以下部分来添加更多 PHP 扩展：

```dockerfile
# 安装常用扩展
RUN pvm ext install mysqli pdo_mysql gd curl mbstring xml zip
```

### 修改 PHP 配置

可以通过在 Dockerfile 中添加以下命令来修改 PHP 配置：

```dockerfile
# 修改 PHP 配置
RUN pvm config set memory_limit 256M
RUN pvm config set post_max_size 100M
RUN pvm config set upload_max_filesize 100M
```

### 添加自定义项目

可以将自己的 PHP 项目放在 `www` 目录中，然后通过 http://localhost:8080 访问。

## 注意事项

1. 这些容器使用 PVM 安装和管理 PHP，而不是使用系统包管理器。
2. 每个容器都包含一个完整的 PHP 环境，包括 PHP-FPM 和 Composer。
3. 所有容器共享同一个 `www` 目录，因此可以使用相同的代码测试不同版本的 PHP。
4. 默认情况下，所有容器都会启动，这可能会占用较多的系统资源。如果不需要所有版本，可以只启动需要的容器。

## 故障排除

### 端口冲突

如果遇到端口冲突，可以修改 `docker-compose.yml` 文件中的端口映射：

```yaml
ports:
  - "8080:80"  # 将 8080 改为其他可用端口
```

### 内存不足

如果系统内存不足，可以只启动需要的容器，而不是所有容器：

```bash
docker-compose up -d php74 php80 nginx
```
