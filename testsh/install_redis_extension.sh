#!/bin/bash
# Redis扩展安装脚本
# 用于为多个PHP版本安装Redis扩展

# 设置颜色
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 检查PVM是否已安装
if ! command -v pvm &> /dev/null; then
    echo -e "${RED}错误: PVM未安装，请先安装PVM${NC}"
    exit 1
fi

echo -e "${BLUE}Redis扩展安装脚本${NC}"
echo -e "${YELLOW}此脚本将为多个PHP版本安装Redis扩展${NC}"
echo ""

# 获取已安装的PHP版本
INSTALLED_VERSIONS=$(pvm list | grep -E '^[0-9]+\.[0-9]+\.[0-9]+' | awk '{print $1}')

if [ -z "$INSTALLED_VERSIONS" ]; then
    echo -e "${RED}错误: 未找到已安装的PHP版本${NC}"
    exit 1
fi

echo -e "${YELLOW}已安装的PHP版本:${NC}"
echo "$INSTALLED_VERSIONS"
echo ""

# 确认安装
read -p "是否为所有已安装的PHP版本安装Redis扩展? (y/n): " confirm
if [ "$confirm" != "y" ]; then
    echo -e "${YELLOW}已取消安装${NC}"
    exit 0
fi

# 为每个PHP版本安装Redis扩展
for version in $INSTALLED_VERSIONS; do
    echo -e "${YELLOW}正在为PHP $version 安装Redis扩展...${NC}"
    
    pvm ext install redis $version
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}PHP $version Redis扩展安装成功${NC}"
    else
        echo -e "${RED}PHP $version Redis扩展安装失败${NC}"
    fi
    
    echo ""
done

# 创建Redis测试文件
echo -e "${YELLOW}正在创建Redis测试文件...${NC}"

cat > redis_test.php << 'EOF'
<?php
// Redis扩展测试文件

echo "PHP版本: " . PHP_VERSION . "\n";
echo "Redis扩展版本: " . phpversion('redis') . "\n\n";

// 检查Redis扩展是否已加载
if (!extension_loaded('redis')) {
    echo "错误: Redis扩展未加载\n";
    exit(1);
}

// 连接到Redis服务器
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    
    echo "Redis服务器信息:\n";
    $info = $redis->info();
    echo "Redis版本: " . $info['redis_version'] . "\n";
    echo "运行模式: " . $info['redis_mode'] . "\n";
    echo "进程ID: " . $info['process_id'] . "\n";
    echo "已连接客户端: " . $info['connected_clients'] . "\n";
    echo "内存使用: " . $info['used_memory_human'] . "\n\n";
    
    // 基本操作测试
    echo "基本操作测试:\n";
    
    // 设置值
    $redis->set('test_key', 'Hello Redis!');
    echo "设置值: test_key = 'Hello Redis!'\n";
    
    // 获取值
    $value = $redis->get('test_key');
    echo "获取值: test_key = '$value'\n";
    
    // 删除值
    $redis->del('test_key');
    echo "删除值: test_key\n";
    
    // 检查值是否存在
    $exists = $redis->exists('test_key');
    echo "检查值是否存在: " . ($exists ? '是' : '否') . "\n\n";
    
    // 哈希表测试
    echo "哈希表测试:\n";
    $redis->hSet('user:1', 'name', 'John');
    $redis->hSet('user:1', 'email', 'john@example.com');
    $redis->hSet('user:1', 'age', 30);
    
    echo "哈希表值:\n";
    $user = $redis->hGetAll('user:1');
    foreach ($user as $key => $value) {
        echo "  $key: $value\n";
    }
    
    $redis->del('user:1');
    echo "\n";
    
    // 列表测试
    echo "列表测试:\n";
    $redis->lPush('mylist', 'item1');
    $redis->lPush('mylist', 'item2');
    $redis->lPush('mylist', 'item3');
    
    echo "列表值:\n";
    $list = $redis->lRange('mylist', 0, -1);
    foreach ($list as $item) {
        echo "  $item\n";
    }
    
    $redis->del('mylist');
    echo "\n";
    
    // 集合测试
    echo "集合测试:\n";
    $redis->sAdd('myset', 'member1');
    $redis->sAdd('myset', 'member2');
    $redis->sAdd('myset', 'member3');
    
    echo "集合成员:\n";
    $members = $redis->sMembers('myset');
    foreach ($members as $member) {
        echo "  $member\n";
    }
    
    $redis->del('myset');
    echo "\n";
    
    // 有序集合测试
    echo "有序集合测试:\n";
    $redis->zAdd('scores', 100, 'player1');
    $redis->zAdd('scores', 200, 'player2');
    $redis->zAdd('scores', 300, 'player3');
    
    echo "有序集合成员:\n";
    $members = $redis->zRange('scores', 0, -1, true);
    foreach ($members as $member => $score) {
        echo "  $member: $score\n";
    }
    
    $redis->del('scores');
    
    echo "\nRedis扩展测试成功!\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}
EOF

echo -e "${GREEN}Redis测试文件已创建: $(pwd)/redis_test.php${NC}"
echo -e "${YELLOW}您可以运行 'php redis_test.php' 来测试Redis扩展${NC}"
echo -e "${YELLOW}注意: 您需要先安装并启动Redis服务器${NC}"

echo -e "${GREEN}Redis扩展安装完成${NC}"
