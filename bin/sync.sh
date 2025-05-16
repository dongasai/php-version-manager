#!/bin/bash

# PVM 镜像同步脚本
# 用于定期同步镜像内容

# 定义根目录
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# 定义日志文件
LOG_FILE="$ROOT_DIR/logs/sync_$(date +%Y%m%d_%H%M%S).log"

# 确保日志目录存在
mkdir -p "$ROOT_DIR/logs"

# 记录日志函数
log() {
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] $1" | tee -a "$LOG_FILE"
}

# 开始同步
log "开始同步 PVM 镜像内容"

# 确保数据目录存在
mkdir -p "$ROOT_DIR/data/php"
mkdir -p "$ROOT_DIR/data/pecl"
mkdir -p "$ROOT_DIR/data/extensions"
mkdir -p "$ROOT_DIR/data/composer"

# 确保公共目录存在
mkdir -p "$ROOT_DIR/public/php"
mkdir -p "$ROOT_DIR/public/pecl"
mkdir -p "$ROOT_DIR/public/extensions"
mkdir -p "$ROOT_DIR/public/composer"

# 创建符号链接（如果不存在）
if [ ! -L "$ROOT_DIR/public/php" ]; then
    ln -sf "$ROOT_DIR/data/php" "$ROOT_DIR/public/php"
    log "创建符号链接: public/php -> data/php"
fi

if [ ! -L "$ROOT_DIR/public/pecl" ]; then
    ln -sf "$ROOT_DIR/data/pecl" "$ROOT_DIR/public/pecl"
    log "创建符号链接: public/pecl -> data/pecl"
fi

if [ ! -L "$ROOT_DIR/public/extensions" ]; then
    ln -sf "$ROOT_DIR/data/extensions" "$ROOT_DIR/public/extensions"
    log "创建符号链接: public/extensions -> data/extensions"
fi

if [ ! -L "$ROOT_DIR/public/composer" ]; then
    ln -sf "$ROOT_DIR/data/composer" "$ROOT_DIR/public/composer"
    log "创建符号链接: public/composer -> data/composer"
fi

# 运行 PVM 镜像同步命令
log "运行 PVM 镜像同步命令"
"$ROOT_DIR/bin/pvm-mirror" sync >> "$LOG_FILE" 2>&1

# 检查同步结果
if [ $? -eq 0 ]; then
    log "同步完成"
else
    log "同步失败，请查看日志文件: $LOG_FILE"
    exit 1
fi

# 显示镜像状态
log "镜像状态:"
"$ROOT_DIR/bin/pvm-mirror" status >> "$LOG_FILE" 2>&1

# 清理过期镜像
log "清理过期镜像"
"$ROOT_DIR/bin/pvm-mirror" clean >> "$LOG_FILE" 2>&1

# 设置文件权限
log "设置文件权限"
chmod -R 755 "$ROOT_DIR/data"
chmod -R 755 "$ROOT_DIR/public"

log "同步脚本执行完成"

# 添加到 crontab 的示例
# 0 2 * * * /path/to/pvm-mirror/bin/sync.sh
