#!/usr/bin/env bats

setup() {
  # Set pvm executable path
  PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
  # Create mock PHP versions for testing
  mkdir -p "$TEST_DIR/.pvm/versions/8.1.0/bin"
  touch "$TEST_DIR/.pvm/versions/8.1.0/bin/php"
  chmod +x "$TEST_DIR/.pvm/versions/8.1.0/bin/php"
}

teardown() {
  # Clean up test directory
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

@test "pvm --version 应该显示版本信息" {
  run "$PVM_BIN" --version
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PHP Version Manager" ]]
}

@test "pvm list 应该显示已安装版本" {
  run "$PVM_BIN" list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Installed PHP versions:" ]]
}

@test "pvm install 应该安装PHP版本" {
  skip "This test requires network and may take long time"
  run "$PVM_BIN" install 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Successfully installed PHP 8.1" ]]
}

@test "pvm remove 应该卸载PHP版本" {
  skip "This test requires network and may take long time"
  "$PVM_BIN" install 8.1 >/dev/null 2>&1 || true
  run "$PVM_BIN" remove 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Successfully removed PHP 8.1" ]]
}

@test "pvm use 应该切换PHP版本" {
  skip "This test requires network and may take long time"
  "$PVM_BIN" install 8.1 >/dev/null 2>&1 || true
  run "$PVM_BIN" use 8.1
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Now using PHP 8.1" ]]
  run php -v
  [[ "$output" =~ "PHP 8.1" ]]
}

@test "pvm 执行无效命令应该失败" {
  run "$PVM_BIN" invalid-command
  [ "$status" -ne 0 ]
  [[ "$output" =~ "Unknown command" ]]
}

@test "pvm install 无效版本应该失败" {
  run "$PVM_BIN" install invalid-version
  [ "$status" -ne 0 ]
  [[ "$output" =~ "Invalid PHP version" ]]
}

@test "pvm 应该能识别PVM_HOME环境变量" {
  export PVM_HOME="$TEST_DIR/custom_pvm_home"
  run "$PVM_BIN" list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PVM home: $TEST_DIR/custom_pvm_home" ]]
}

@test "pvm init 应该能创建配置文件" {
  run "$PVM_BIN" init
  [ "$status" -eq 0 ]
  [ -f "$TEST_DIR/.pvm/config.json" ]
  run jq -r '.version' "$TEST_DIR/.pvm/config.json"
  [ "$status" -eq 0 ]
  [ -n "$output" ]
}

@test "pvm ext 应该能列出扩展" {
  run "$PVM_BIN" ext list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "已安装的PHP扩展" ]]
}

@test "pvm config 应该能列出配置" {
  run "$PVM_BIN" config list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "当前配置" ]]
}

@test "pvm security 应该能检查安全设置" {
  run "$PVM_BIN" security check
  [ "$status" -eq 0 ]
  [[ "$output" =~ "已安装的PHP版本" ]]
}
  run "$PVM_MIRROR_BIN" list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Available mirrors:" ]]
}

@test "pvm update 应该能更新版本列表" {
  run "$PVM_BIN" update
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Version list updated" ]]
}
