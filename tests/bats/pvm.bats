#!/usr/bin/env bats

setup() {
  # 设置pvm可执行文件路径
  PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
}

teardown() {
  # 清理测试目录
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

@test "pvm --version should show version info" {
  run "$PVM_BIN" --version
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PHP Version Manager" ]]
}

@test "pvm list should show installed versions" {
  run "$PVM_BIN" list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Installed PHP versions:" ]]
}

@test "pvm install should install php version" {
  skip "This test requires network and may take long time"
  run "$PVM_BIN" install 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Successfully installed PHP 8.1" ]]
}

@test "pvm use should switch php version" {
  # 先确保安装了某个版本
  "$PVM_BIN" install 8.1 >/dev/null 2>&1 || true
  
  run "$PVM_BIN" use 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Now using PHP 8.1" ]]
  
  # 验证版本切换是否生效
  run php -v
  [[ "$output" =~ "PHP 8.1" ]]
}