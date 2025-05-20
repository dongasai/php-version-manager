# PVM 开发测试容器

这个目录包含了用于开发和测试 PHP Version Manager (PVM) 的各种 Linux 发行版的 Docker 容器配置。

## 目录结构

```
.
├── alinux3/                # Alibaba Linux 3 开发环境
├── alpine/                 # Alpine Linux 开发环境
├── alpine-3.16/            # Alpine Linux 3.16 开发环境
├── alpine-3.18/            # Alpine Linux 3.18 开发环境
├── alpine-3.19/            # Alpine Linux 3.19 开发环境
├── arm64/                  # ARM64 架构开发环境
├── centos/                 # CentOS 开发环境
├── centos-7/               # CentOS 7 开发环境
├── centos-stream-8/        # CentOS Stream 8 开发环境
├── centos-stream-9/        # CentOS Stream 9 开发环境
├── compose.yml             # Docker Compose 配置文件
├── debian/                 # Debian 开发环境
├── debian-11/              # Debian 11 开发环境
├── debian-12/              # Debian 12 开发环境
├── fedora/                 # Fedora 开发环境
├── fedora-36/              # Fedora 36 开发环境
├── fedora-38/              # Fedora 38 开发环境
├── fedora-40/              # Fedora 40 开发环境
├── README.md               # 本文件
├── ubuntu/                 # Ubuntu 开发环境
├── ubuntu-18.04/           # Ubuntu 18.04 开发环境
├── ubuntu-20.04/           # Ubuntu 20.04 开发环境
├── ubuntu-22.04/           # Ubuntu 22.04 开发环境
└── ubuntu-24.04/           # Ubuntu 24.04 开发环境
```

## 使用方法

### 构建和启动容器

```bash
# 进入开发容器目录
cd docker/dev

# 构建并启动所有容器
docker-compose up -d

# 构建并启动特定发行版的容器
docker-compose up -d ubuntu-22.04

# 重新构建容器
docker-compose up -d --build ubuntu-22.04
```

### 进入容器

```bash
# 进入 Ubuntu 22.04 容器
docker exec -it pvm-dev-ubuntu-22.04 bash

# 进入 Debian 11 容器
docker exec -it pvm-dev-debian-11 bash
```

### 停止容器

```bash
# 停止所有容器
docker-compose down

# 停止特定容器
docker-compose stop ubuntu-22.04
```

## 容器端口映射

每个容器都映射了不同的端口，以便可以同时运行多个容器：

| 容器 | 端口映射 |
|------|---------|
| ubuntu | 8081:8080 |
| ubuntu-18.04 | 8082:8080 |
| ubuntu-20.04 | 8083:8080 |
| ubuntu-22.04 | 8084:8080 |
| ubuntu-24.04 | 8085:8080 |
| debian | 8086:8080 |
| debian-11 | 8087:8080 |
| debian-12 | 8088:8080 |
| centos | 8089:8080 |
| centos-7 | 8090:8080 |
| centos-stream-8 | 8091:8080 |
| centos-stream-9 | 8092:8080 |
| fedora | 8093:8080 |
| fedora-36 | 8094:8080 |
| fedora-38 | 8095:8080 |
| fedora-40 | 8096:8080 |
| alpine | 8097:8080 |
| alpine-3.16 | 8098:8080 |
| alpine-3.18 | 8099:8080 |
| alpine-3.19 | 8100:8080 |
| alinux3 | 8101:8080 |
| arm64 | 8102:8080 |

## 数据卷

每个容器都有自己的数据卷，用于保存容器内的数据：

```yaml
volumes:
  pvm_dev_data:
  pvm_dev_ubuntu_18_04_data:
  pvm_dev_ubuntu_20_04_data:
  # ... 其他数据卷
```

## 自定义容器

如果需要自定义容器，可以修改相应目录下的 Dockerfile 文件。例如，要自定义 Ubuntu 22.04 容器，可以编辑 `ubuntu-22.04/Dockerfile` 文件。

## 注意事项

1. 这些容器主要用于开发和测试 PVM，不建议用于生产环境。
2. 所有容器都挂载了项目根目录，因此可以直接在容器内编辑和测试代码。
3. 默认情况下，所有容器都会启动，这可能会占用较多的系统资源。如果不需要所有容器，可以只启动需要的容器。
4. 容器内的用户是 `developer`，具有 sudo 权限。

## 故障排除

### 端口冲突

如果遇到端口冲突，可以修改 `compose.yml` 文件中的端口映射：

```yaml
ports:
  - "8084:8080"  # 将 8084 改为其他可用端口
```

### 内存不足

如果系统内存不足，可以只启动需要的容器，而不是所有容器：

```bash
docker-compose up -d ubuntu-22.04 debian-11
```

### 容器无法启动

如果容器无法启动，可以查看容器日志：

```bash
docker-compose logs ubuntu-22.04
```
