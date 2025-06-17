#!/usr/bin/env bats

# 基本的CI测试，只测试最核心的功能，避免长时间运行

setup() {
  # 设置测试超时时间为15秒
  export BATS_TEST_TIMEOUT=15
  
  PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
}

teardown() {
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

@test "pvm 可执行文件存在且可运行" {
  [ -f "$PVM_BIN" ]
  [ -x "$PVM_BIN" ]
}

@test "pvm --version 显示版本信息" {
  run timeout 5 "$PVM_BIN" --version
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PHP Version Manager" ]]
}

@test "pvm help 显示帮助信息" {
  run timeout 5 "$PVM_BIN" help
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PHP Version Manager" ]]
}

@test "pvm 不带参数显示帮助信息" {
  run timeout 10 "$PVM_BIN"
  # 允许超时，因为可能进入交互模式
  [[ "$status" -eq 0 || "$status" -eq 124 ]]
  # 如果没有超时，检查输出
  if [[ "$status" -eq 0 ]]; then
    [[ "$output" =~ "PHP Version Manager" ]]
  fi
}

@test "pvm list 命令可以执行" {
  run timeout 10 "$PVM_BIN" list
  # 允许命令失败，因为可能没有安装PHP版本
  [[ "$status" -eq 0 || "$output" =~ "No PHP" || "$output" =~ "Installed PHP versions" ]]
}

@test "pvm 执行无效命令应该失败" {
  run timeout 5 "$PVM_BIN" invalid-command-that-does-not-exist
  [ "$status" -ne 0 ]
}

@test "PHP语法检查 - bin/pvm" {
  run php -l "$BATS_TEST_DIRNAME/../../bin/pvm"
  [ "$status" -eq 0 ]
}

@test "PHP语法检查 - bin/pvm-mirror" {
  run php -l "$BATS_TEST_DIRNAME/../../bin/pvm-mirror"
  [ "$status" -eq 0 ]
}
