#!/usr/bin/env bats

@test "Install pvm in container" {
    run docker exec pvm-ubuntu-18.04 bash -c "curl -sL https://raw.githubusercontent.com/phpv
    [ "$status" -eq 0 ]
}

@test "Test PHP 7.1 installation" {
    run docker exec pvm-ubuntu-18.04 pvm install 7.1
    [ "$status" -eq 0 ]
    run docker exec pvm-ubuntu-18.04 php -v | grep "7.1"
    [ "$status" -eq 0 ]
}