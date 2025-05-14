# PHP Version Manager Makefile

.PHONY: build dev shell run test test-ubuntu test-debian test-centos test-fedora test-alpine test-arm64 test-all clean run-tests-ubuntu compat-test-ubuntu compat-test-debian compat-test-centos compat-test-fedora compat-test-alpine compat-test-arm64 compat-test-all test-all-versions test-in-containers

# 构建所有容器
build:
	docker-compose build

# 启动开发容器
dev:
	docker-compose build dev
	docker-compose run --rm dev

# 进入开发容器的shell
shell:
	docker-compose run --rm dev

# 在开发容器中运行命令
run:
	docker-compose run --rm dev bash -c "cd /app && chmod +x docker/dev.sh && ./docker/dev.sh $(CMD)"

# 在开发容器中运行测试
test:
	docker-compose run --rm dev bash -c "cd /app && chmod +x docker/dev.sh && ./docker/dev.sh test-all"

# 测试Ubuntu
test-ubuntu:
	docker-compose run --rm ubuntu bash -c "cd /app && chmod +x docker/test.sh && ./docker/test.sh"

# 运行所有测试在Ubuntu上
run-tests-ubuntu:
	docker-compose run --rm ubuntu bash -c "cd /app && chmod +x docker/run_tests.sh && ./docker/run_tests.sh"

# 运行兼容性测试在Ubuntu上
compat-test-ubuntu:
	docker-compose run --rm ubuntu bash -c "cd /app && chmod +x docker/compatibility_test.sh && ./docker/compatibility_test.sh"

# 运行兼容性测试在Debian上
compat-test-debian:
	docker-compose run --rm debian bash -c "cd /app && chmod +x docker/compatibility_test.sh && ./docker/compatibility_test.sh"

# 运行兼容性测试在CentOS上
compat-test-centos:
	docker-compose run --rm centos bash -c "cd /app && chmod +x docker/compatibility_test.sh && ./docker/compatibility_test.sh"

# 运行兼容性测试在Fedora上
compat-test-fedora:
	docker-compose run --rm fedora bash -c "cd /app && chmod +x docker/compatibility_test.sh && ./docker/compatibility_test.sh"

# 运行兼容性测试在Alpine上
compat-test-alpine:
	docker-compose run --rm alpine bash -c "cd /app && chmod +x docker/compatibility_test.sh && ./docker/compatibility_test.sh"

# 运行兼容性测试在ARM64上
compat-test-arm64:
	docker-compose run --rm arm64 bash -c "cd /app && chmod +x docker/compatibility_test.sh && ./docker/compatibility_test.sh"

# 运行所有兼容性测试
compat-test-all: compat-test-ubuntu compat-test-debian compat-test-centos compat-test-fedora compat-test-alpine

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

# 在开发容器中测试所有PHP版本
test-all-versions:
	docker-compose run --rm dev bash -c "cd /app && chmod +x docker/test_all_versions.sh && ./docker/test_all_versions.sh"

# 在所有容器中测试安装
test-in-containers:
	chmod +x docker/test_in_containers.sh
	./docker/test_in_containers.sh --all

# 在指定容器中测试指定版本
test-version-in-container:
	chmod +x docker/test_in_containers.sh
	./docker/test_in_containers.sh -c $(CONTAINER) -v $(VERSION)

# 清理容器
clean:
	docker-compose down
	docker-compose rm -f
