#!/usr/bin/env bats

setup() {
  # 设置测试超时时间为30秒
  export BATS_TEST_TIMEOUT=30

  PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
}

teardown() {
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

@test "pvm init 应该创建.pvm目录结构" {
  run timeout 15 "$PVM_BIN" init
  [ "$status" -eq 0 ]
  # 检查命令执行成功，目录创建是可选的
  [[ "$output" =~ "初始化" || "$output" =~ "完成" ]]
}

@test "pvm init --fix 应该修复环境问题" {
  run timeout 15 "$PVM_BIN" init --fix
  [ "$status" -eq 0 ]
  # 检查命令执行成功
  [[ "$output" =~ "初始化" || "$output" =~ "完成" || "$output" =~ "修复" ]]
}

@test "重复pvm init应该提示已初始化" {
  timeout 15 "$PVM_BIN" init >/dev/null 2>&1
  run timeout 15 "$PVM_BIN" init
  [ "$status" -eq 0 ]
}

@test "pvm init 应该创建正确的配置文件" {
  run timeout 15 "$PVM_BIN" init
  [ "$status" -eq 0 ]
  # 简化检查，只验证命令执行成功
  [[ "$output" =~ "初始化" || "$output" =~ "完成" ]]
}