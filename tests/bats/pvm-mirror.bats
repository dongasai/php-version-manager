#!/usr/bin/env bats

setup() {
  # 设置测试超时时间为120秒(镜像测试需要更长时间)
  export BATS_TEST_TIMEOUT=120
  
  PVM_BIN=$(realpath "$BATS_TEST_DIRNAME/../../bin/pvm")
  TEST_DIR=$(mktemp -d)
  cd "$TEST_DIR" || exit
  "$PVM_BIN" init >/dev/null 2>&1
}

teardown() {
  cd "$BATS_SUITE_TMPDIR" || exit
  rm -rf "$TEST_DIR"
}

@test "pvm mirror list 应该列出可用镜像" {
  run "$PVM_BIN" mirror list
  [ "$status" -eq 0 ]
  [[ "$output" =~ "可用镜像列表" ]]
  [[ "$output" =~ "官方源" ]]
}

@test "pvm mirror set 应该能设置镜像" {
  run "$PVM_BIN" mirror set https://mirror.example.com
  [ "$status" -eq 0 ]
  [[ "$output" =~ "镜像已设置为" ]]
  
  run jq -r '.mirror' "$TEST_DIR/.pvm/config.json"
  [ "$status" -eq 0 ]
  [ "$output" = "https://mirror.example.com" ]
}

@test "pvm mirror test 应该测试镜像速度" {
  skip "需要网络连接，可能耗时较长"
  run "$PVM_BIN" mirror test
  [ "$status" -eq 0 ]
  [[ "$output" =~ "镜像速度测试结果" ]]
}

@test "设置无效镜像应该失败" {
  run "$PVM_BIN" mirror set "invalid-url"
  [ "$status" -ne 0 ]
  [[ "$output" =~ "无效的镜像URL" ]]
}

@test "pvm mirror reset 应该重置为官方源" {
  run "$PVM_BIN" mirror set https://mirror.example.com
  [ "$status" -eq 0 ]
  
  run "$PVM_BIN" mirror reset
  [ "$status" -eq 0 ]
  [[ "$output" =~ "已重置为官方源" ]]
  
  run jq -r '.mirror' "$TEST_DIR/.pvm/config.json"
  [ "$status" -eq 0 ]
  [[ "$output" =~ "https://www.php.net" ]]
}