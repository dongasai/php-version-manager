#!/bin/bash

# 设置颜色
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 测试函数
test_container() {
    local container=$1
    echo -e "${BLUE}=========================================================${NC}"
    echo -e "${BLUE}测试容器: ${container}${NC}"
    echo -e "${BLUE}=========================================================${NC}"

    # 检查容器是否存在并运行
    if ! docker ps | grep -q $container; then
        echo -e "${RED}容器 $container 不存在或未运行${NC}"
        return 1
    fi

    # 在容器中执行测试命令
    echo -e "${YELLOW}1. 初始化 PVM${NC}"
    docker exec -it $container bash -c "cd /app && ./bin/pvm init" || {
        echo -e "${RED}初始化 PVM 失败${NC}"
        return 1
    }

    echo -e "${YELLOW}2. 安装 PHP 7.1${NC}"
    docker exec -it $container bash -c "cd /app && ./bin/pvm install 7.1" || {
        echo -e "${RED}安装 PHP 7.1 失败${NC}"
        return 1
    }

    echo -e "${YELLOW}3. 切换到 PHP 7.1${NC}"
    docker exec -it $container bash -c "cd /app && ./bin/pvm use 7.1" || {
        echo -e "${RED}切换到 PHP 7.1 失败${NC}"
        return 1
    }

    echo -e "${YELLOW}4. 验证 PHP 版本${NC}"
    docker exec -it $container bash -c "cd /app && ./bin/pvm list" || {
        echo -e "${RED}验证 PHP 版本失败${NC}"
        return 1
    }

    echo -e "${GREEN}容器 $container 测试成功!${NC}"
    return 0
}

# 获取所有容器名称
containers=$(docker ps --format "{{.Names}}" | grep "pvm-")

# 如果指定了容器名称，则只测试指定的容器
if [ $# -gt 0 ]; then
    containers=$@
fi

# 测试结果统计
total=0
success=0
failed=0
failed_containers=""

# 遍历所有容器进行测试
for container in $containers; do
    ((total++))
    if test_container $container; then
        ((success++))
    else
        ((failed++))
        failed_containers="$failed_containers $container"
    fi
    echo ""
done

# 输出测试结果摘要
echo -e "${BLUE}=========================================================${NC}"
echo -e "${BLUE}测试结果摘要${NC}"
echo -e "${BLUE}=========================================================${NC}"
echo -e "总计测试容器: ${total}"
echo -e "成功: ${GREEN}${success}${NC}"
echo -e "失败: ${RED}${failed}${NC}"

if [ $failed -gt 0 ]; then
    echo -e "${RED}失败的容器:${NC}${failed_containers}"
fi

# 返回测试结果
if [ $failed -eq 0 ]; then
    echo -e "${GREEN}所有容器测试通过!${NC}"
    exit 0
else
    echo -e "${RED}部分容器测试失败!${NC}"
    exit 1
fi
