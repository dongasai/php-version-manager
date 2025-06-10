# pvm-mirror 的Docker

这个目录包含了pvm-mirror的Docker容器化配置文件。

## 文件说明

### Dockerfile
pvm-mirror的容器镜像定义文件，基于Ubuntu 22.04构建，包含：
- PHP 8.1运行环境
- 必要的系统依赖和PHP扩展
- pvm-mirror专用用户
- 健康检查配置
- 默认启动命令

### dev-compose.yml
开发环境的Docker Compose配置文件，特性：
- 映射项目的data目录到容器的数据目录
- 映射项目的logs目录到容器的日志目录
- 映射源代码目录用于开发调试
- 容器自动重启
- 启动后即运行pvm-mirror服务
- 开放默认端口34403
- 设置开发环境变量

## 使用方法

### 构建镜像
```bash
# 在项目根目录执行
docker build -f docker/pvm-mirror/Dockerfile -t pvm-mirror .
```

### 启动开发环境
```bash
# 进入pvm-mirror目录
cd docker/pvm-mirror

# 启动开发环境
docker-compose -f dev-compose.yml up -d

# 查看日志
docker-compose -f dev-compose.yml logs -f

# 停止服务
docker-compose -f dev-compose.yml down
```

### 访问服务
- Web界面：http://localhost:34403
- 容器内部：`docker exec -it pvm-mirror-dev bash`

## 环境变量

- `PVM_MIRROR_ENV`: 运行环境 (development/production)
- `PVM_MIRROR_LOG_LEVEL`: 日志级别 (debug/info/warning/error)
- `PVM_MIRROR_DATA_DIR`: 数据目录路径
- `PVM_MIRROR_LOG_DIR`: 日志目录路径

## 数据持久化

开发环境通过卷映射实现数据持久化：
- `../../data:/app/data` - 镜像数据
- `../../logs:/app/logs` - 日志文件
- `../../configMirror:/app/configMirror` - 配置文件
- `../../cache:/app/cache` - 缓存文件

## 健康检查

容器包含健康检查配置，每30秒检查一次服务状态：
```bash
# 手动检查健康状态
docker inspect pvm-mirror-dev | grep Health -A 10
```

## 故障排除

### 查看容器日志
```bash
docker logs pvm-mirror-dev
```

### 进入容器调试
```bash
docker exec -it pvm-mirror-dev bash
```

### 重启服务
```bash
docker-compose -f dev-compose.yml restart
```