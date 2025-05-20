#!/usr/bin/env bats

load "pvm-common"

setup() {
  # 调用父级setup
  setup_common
}

teardown() {
  # 调用父级teardown
  teardown_common
}

@test "CentOS下应使用yum安装依赖" {
  # 模拟安装命令
  export PVM_TEST_MODE=1
  run "$PVM_BIN" install 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "使用yum安装依赖" ]]
}

@test "CentOS下应支持EPEL仓库" {
  export PVM_TEST_MODE=1
  run "$PVM_BIN" install --with-epel 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "启用EPEL仓库" ]]
}