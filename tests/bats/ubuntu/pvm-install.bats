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

@test "Ubuntu下应使用apt安装依赖" {
  # 模拟安装命令
  export PVM_TEST_MODE=1
  run "$PVM_BIN" install 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "使用apt安装依赖" ]]
}

@test "Ubuntu下应支持PPA仓库" {
  export PVM_TEST_MODE=1
  run "$PVM_BIN" install --with-ppa 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "启用PPA仓库" ]]
}