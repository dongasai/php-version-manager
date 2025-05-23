#!/bin/bash

# 测试函数
test_php71_install() {
    local container_name=$1
    echo "测试容器: $container_name"
    
    # 安装pvm
    docker exec $container_name bash -c "curl -sL https://raw.githubusercontent.com/phpvip/pvm/master/install.sh | bash"
    
    # 安装PHP 7.1
    docker exec $container_name bash -c "source ~/.bashrc && pvm install 7.1"
    
    # 验证安装
    docker exec $container_name bash -c "php -v | grep '7.1'"
    
    if [ $? -eq 0 ]; then
        echo "✅ $container_name PHP 7.1 安装成功"
    else
        echo "❌ $container_name PHP 7.1 安装失败"
    fi
}

# 测试所有容器
containers=(
    "pvm-ubuntu-18.04"
    "pvm-ubuntu-20.04" 
    "pvm-ubuntu-22.04"
    "pvm-debian-11"
    "pvm-debian-12"
)

for container in "${containers[@]}"; do
    test_php71_install $container
    echo "----------------------------------"
done