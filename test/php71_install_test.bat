#!/usr/bin/env bats

@install_pvm() {
    curl -sL https://raw.githubusercontent.com/phpvip/pvm/master/install.sh | bash
}

@test "Install pvm in container" {
    run @install_pvm
    [ "$status" -eq 0 ]
}

@test "Test PHP 7.1 installation" {
    run pvm install 7.1
    [ "$status" -eq 0 ]
    run php -v | grep "7.1"
    [ "$status" -eq 0 ]
}