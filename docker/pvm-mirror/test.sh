#!/bin/bash

# pvm-mirror Docker 测试脚本

echo "=== PVM-Mirror Docker 测试 ==="
echo

# 检查Docker是否运行
echo "1. 检查Docker服务状态..."
if ! docker info >/dev/null 2>&1; then
    echo "❌ Docker服务未运行"
    exit 1
fi
echo "✅ Docker服务正常"

# 检查容器状态
echo
echo "2. 检查容器状态..."
CONTAINER_STATUS=$(docker compose -f dev-compose.yml ps --format "table {{.Name}}\t{{.Status}}" | grep pvm-mirror-dev | awk '{print $2}')
if [[ "$CONTAINER_STATUS" == "Up" ]]; then
    echo "✅ 容器运行正常"
else
    echo "❌ 容器状态异常: $CONTAINER_STATUS"
    exit 1
fi

# 检查端口是否开放
echo
echo "3. 检查端口34403是否开放..."
if curl -s -I http://localhost:34403/ >/dev/null; then
    echo "✅ 端口34403可访问"
else
    echo "❌ 端口34403无法访问"
    exit 1
fi

# 检查健康状态
echo
echo "4. 检查容器健康状态..."
HEALTH_STATUS=$(docker inspect pvm-mirror-dev --format='{{.State.Health.Status}}' 2>/dev/null)
if [[ "$HEALTH_STATUS" == "healthy" ]]; then
    echo "✅ 容器健康检查通过"
else
    echo "⚠️  容器健康状态: $HEALTH_STATUS"
fi

# 测试基本功能
echo
echo "5. 测试基本HTTP响应..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:34403/)
if [[ "$HTTP_CODE" == "200" ]]; then
    echo "✅ HTTP响应正常 (状态码: $HTTP_CODE)"
else
    echo "❌ HTTP响应异常 (状态码: $HTTP_CODE)"
fi

# 显示容器信息
echo
echo "6. 容器信息:"
docker compose -f dev-compose.yml ps

echo
echo "=== 测试完成 ==="
echo "访问地址: http://localhost:34403"
echo "查看日志: docker compose -f dev-compose.yml logs -f"
echo "停止服务: docker compose -f dev-compose.yml down"
