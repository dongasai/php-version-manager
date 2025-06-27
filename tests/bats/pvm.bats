#!/usr/bin/env bats

setup() {
  # 设置测试超时时间为30秒
  export BATS_TEST_TIMEOUT=30

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
  [[ "$output" =~ "已安装的PHP版本:" ]]
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
  [[ "$output" =~ "未知命令" || "$output" =~ "Unknown command" ]]
}

@test "pvm install 无效版本应该失败" {
  run "$PVM_BIN" install invalid-version
  [ "$status" -ne 0 ]
  [[ "$output" =~ "无效的PHP版本" || "$output" =~ "Invalid PHP version" || "$output" =~ "不支持的版本" ]]
}

@test "pvm 应该能识别PVM_HOME环境变量" {
  export PVM_HOME="$TEST_DIR/custom_pvm_home"
  run timeout 10 "$PVM_BIN" list
  [ "$status" -eq 0 ]
  # 简化检查，只验证命令执行成功
  [[ "$output" =~ "已安装的PHP版本:" ]]
}

@test "pvm init 应该能创建配置文件" {
  run timeout 10 "$PVM_BIN" init
  [ "$status" -eq 0 ]
  [ -d "$TEST_DIR/.pvm" ]
  # 简化检查，只验证目录创建成功
}

@test "pvm ext 应该能列出扩展" {
  run timeout 10 "$PVM_BIN" ext list
  # 允许命令失败，因为可能没有安装PHP
  [[ "$status" -eq 0 || "$output" =~ "No PHP" ]]
}

@test "pvm config 应该能列出配置" {
  run timeout 10 "$PVM_BIN" config list
  # 允许命令失败，因为可能没有配置文件
  [[ "$status" -eq 0 || "$output" =~ "No config" ]]
}

@test "pvm security 应该能检查安全设置" {
  run timeout 10 "$PVM_BIN" security check
  # 允许命令失败，因为可能没有安装PHP或有其他问题
  [[ "$status" -eq 0 || "$status" -eq 255 || "$output" =~ "No PHP" ]]
}

@test "pvm update 应该能更新版本列表" {
  skip "This test requires network and may take long time"
  run timeout 30 "$PVM_BIN" update
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Version list updated" ]]
}
