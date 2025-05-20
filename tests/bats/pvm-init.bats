#!/usr/bin/env bats

setup() {
  # 设置测试超时时间为60秒
  export BATS_TEST_TIMEOUT=60
  
  PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
}

teardown() {
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

@test "pvm init 应该创建.pvm目录结构" {
  run "$PVM_BIN" init
  [ "$status" -eq 0 ]
  [ -d "$TEST_DIR/.pvm" ]
  [ -d "$TEST_DIR/.pvm/versions" ]
  [ -d "$TEST_DIR/.pvm/config" ]
  [ -f "$TEST_DIR/.pvm/config.json" ]
}

@test "pvm init --fix 应该修复环境问题" {
  # 模拟环境问题
  rm -f "$TEST_DIR/.pvm/config.json" 2>/dev/null || true
  
  run "$PVM_BIN" init --fix
  [ "$status" -eq 0 ]
  [ -f "$TEST_DIR/.pvm/config.json" ]
  [[ "$output" =~ "环境问题已修复" ]]
}

@test "重复pvm init应该提示已初始化" {
  "$PVM_BIN" init >/dev/null 2>&1
  run "$PVM_BIN" init
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PVM已经初始化" ]]
}

@test "pvm init 应该创建正确的配置文件" {
  run "$PVM_BIN" init
  [ "$status" -eq 0 ]
  
  run jq -r '.version' "$TEST_DIR/.pvm/config.json"
  [ "$status" -eq 0 ]
  [ -n "$output" ]
  
  run jq -r '.mirror' "$TEST_DIR/.pvm/config.json"
  [ "$status" -eq 0 ]
  [ "$output" != "null" ]
}