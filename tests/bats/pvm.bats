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

@test "pvm remove should uninstall php version" {
  skip "This test requires network and may take long time"
  "$PVM_BIN" install 8.1 >/dev/null 2>&1 || true
  run "$PVM_BIN" remove 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Successfully removed PHP 8.1" ]]
}

@test "pvm use should switch php version" {
  skip "This test requires network and may take long time"
  "$PVM_BIN" install 8.1 >/dev/null 2>&1 || true
  run "$PVM_BIN" use 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Now using PHP 8.1" ]]
  run php -v
  [[ "$output" =~ "PHP 8.1" ]]
}

@test "pvm should fail with invalid command" {
  run "$PVM_BIN" invalid-command
  [ "$status" -ne 0 ]
  [[ "$output" =~ "Unknown command" ]]
}

@test "pvm install should fail with invalid version" {
  run "$PVM_BIN" install invalid-version
  [ "$status" -ne 0 ]
  [[ "$output" =~ "Invalid PHP version" ]]
}

@test "pvm should respect PVM_HOME environment variable" {
  export PVM_HOME="$TEST_DIR/custom_pvm_home"
  run "$PVM_BIN" list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PVM home: $TEST_DIR/custom_pvm_home" ]]
}

@test "pvm init should create config files" {
  run "$PVM_BIN" init
  [ "$status" -eq 0 ]
  [ -f "$TEST_DIR/.pvm/config.json" ]
}

@test "pvm update should update version list" {
  run "$PVM_BIN" update
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Version list updated" ]]
}