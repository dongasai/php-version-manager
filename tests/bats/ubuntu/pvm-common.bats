#!/usr/bin/env bats

setup() {
  # 检测操作系统是否为Ubuntu
  if [[ ! $(lsb_release -is) =~ "Ubuntu" ]]; then
    skip "此测试仅适用于Ubuntu系统"
  fi

  # 设置测试超时时间
  export BATS_TEST_TIMEOUT=60
  
  PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
}

teardown() {
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

# 公共测试函数
pvm_common_test() {
  local command=$1
  local expected=$2
  
  run "$PVM_BIN" "$command"
  [ "$status" -eq 0 ]
  [[ "$output" =~ "$expected" ]]
}