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

@test "pvm help 应该显示帮助信息" {
  run "$PVM_BIN" help
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PHP Version Manager" ]]
  [[ "$output" =~ "Available commands" ]]
}

@test "pvm -h 应该显示帮助信息" {
  run "$PVM_BIN" -h
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PHP Version Manager" ]]
  [[ "$output" =~ "Available commands" ]]
}

@test "pvm 不带参数应该显示帮助信息" {
  run "$PVM_BIN"
  [ "$status" -eq 0 ]
  [[ "$output" =~ "PHP Version Manager" ]]
  [[ "$output" =~ "Available commands" ]]
}

@test "pvm help list 应该显示list命令帮助" {
  run "$PVM_BIN" help list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "List installed PHP versions" ]]
}

@test "pvm help install 应该显示install命令帮助" {
  run "$PVM_BIN" help install
  [ "$status" -eq 0 ]
  [[ "$output" =~ "Install a PHP version" ]]
}