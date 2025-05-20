#!/usr/bin/env bats

setup() {
  export TEST_MODE=1
  export PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
}

teardown() {
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

@test "环境检查失败时应提示修复" {
  export TEST_ENV_CHECK_FAIL=1
  run "$PVM_BIN" list
  [ "$status" -ne 0 ]
  [[ "$output" =~ "PVM运行环境不满足要求" ]]
  [[ "$output" =~ "是否立即修复环境问题" ]]
}

@test "选择修复环境问题应成功" {
  export TEST_ENV_CHECK_FAIL=1
  export TEST_AUTO_FIX=1
  run "$PVM_BIN" list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "环境问题已修复" ]]
}

@test "选择不修复环境问题应退出" {
  export TEST_ENV_CHECK_FAIL=1
  export TEST_AUTO_FIX=0
  run "$PVM_BIN" list
  [ "$status" -ne 0 ]
  [[ "$output" =~ "请运行 'pvm init --fix'" ]]
}

@test "缺失推荐扩展应显示警告" {
  export TEST_MISSING_EXTS="xdebug,redis"
  run "$PVM_BIN" list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "缺失推荐的PHP扩展: xdebug, redis" ]]
}