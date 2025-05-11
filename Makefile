# PHP Version Manager Makefile

.PHONY: build test-ubuntu test-debian test-centos test-fedora test-alpine test-arm64 test-all clean

# 构建所有容器
build:
	docker-compose build

# 测试Ubuntu
test-ubuntu:
	docker-compose run --rm ubuntu bash -c "cd /app && chmod +x docker/test.sh && ./docker/test.sh"

# 测试Debian
test-debian:
	docker-compose run --rm debian bash -c "cd /app && chmod +x docker/test.sh && ./docker/test.sh"

# 测试CentOS
test-centos:
	docker-compose run --rm centos bash -c "cd /app && chmod +x docker/test.sh && ./docker/test.sh"

# 测试Fedora
test-fedora:
	docker-compose run --rm fedora bash -c "cd /app && chmod +x docker/test.sh && ./docker/test.sh"

# 测试Alpine
test-alpine:
	docker-compose run --rm alpine bash -c "cd /app && chmod +x docker/test.sh && ./docker/test.sh"

# 测试ARM64
test-arm64:
	docker-compose run --rm arm64 bash -c "cd /app && chmod +x docker/test.sh && ./docker/test.sh"

# 测试所有环境
test-all: test-ubuntu test-debian test-centos test-fedora test-alpine test-arm64

# 清理容器
clean:
	docker-compose down
	docker-compose rm -f
