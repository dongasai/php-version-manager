#!/usr/bin/env bats

@test "Install pvm in Ubuntu 18.04 container" {
    run docker exec pvm-ubuntu-18.04 bash -c "curl -sL https://raw.githubusercontent.com/phpvip/pvm/master/install.sh | bash"
    [ "$status" -eq 0 ]
}

@test "Test PHP 7.1 installation on Ubuntu 18.04" {
    run docker exec pvm-ubuntu-18.04 pvm install 7.1
    [ "$status" -eq 0 ]
    run docker exec pvm-ubuntu-18.04 php -v | grep "7.1"
    [ "$status" -eq 0 ]
}

@test "Install pvm in Ubuntu 20.04 container" {
    run docker exec pvm-ubuntu-20.04 bash -c "curl -sL https://raw.githubusercontent.com/phpvip/pvm/master/install.sh | bash"
    [ "$status" -eq 0 ]
}

@test "Test PHP 7.1 installation on Ubuntu 20.04" {
    run docker exec pvm-ubuntu-20.04 pvm install 7.1
    [ "$status" -eq 0 ]
    run docker exec pvm-ubuntu-20.04 php -v | grep "7.1"
    [ "$status" -eq 0 ]
}

@test "Install pvm in Ubuntu 22.04 container" {
    run docker exec pvm-ubuntu-22.04 bash -c "curl -sL https://raw.githubusercontent.com/phpvip/pvm/master/install.sh | bash"
    [ "$status" -eq 0 ]
}

@test "Test PHP 7.1 installation on Ubuntu 22.04" {
    run docker exec pvm-ubuntu-22.04 pvm install 7.1
    [ "$status" -eq 0 ]
    run docker exec pvm-ubuntu-22.04 php -v | grep "7.1"
    [ "$status" -eq 0 ]
}

@test "Install pvm in Debian 11 container" {
    run docker exec pvm-debian-11 bash -c "curl -sL https://raw.githubusercontent.com/phpvip/pvm/master/install.sh | bash"
    [ "$status" -eq 0 ]
}

@test "Test PHP 7.1 installation on Debian 11" {
    run docker exec pvm-debian-11 pvm install 7.1
    [ "$status" -eq 0 ]
    run docker exec pvm-debian-11 php -v | grep "7.1"
    [ "$status" -eq 0 ]
}

@test "Install pvm in Debian 12 container" {
    run docker exec pvm-debian-12 bash -c "curl -sL https://raw.githubusercontent.com/phpvip/pvm/master/install.sh | bash"
    [ "$status" -eq 0 ]
}

@test "Test PHP 7.1 installation on Debian 12" {
    run docker exec pvm-debian-12 pvm install 7.1
    [ "$status" -eq 0 ]
    run docker exec pvm-debian-12 php -v | grep "7.1"
    [ "$status" -eq 0 ]
}