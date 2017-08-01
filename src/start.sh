#!/usr/bin/env bash

# Header logging
e_header() {
    printf "$(tput setaf 38)→ %s$(tput sgr0)\n" "$@"
}

# Success logging
e_success() {
    printf "$(tput setaf 76)✔ %s$(tput sgr0)\n" "$@"
}

# Error logging
e_error() {
    printf "$(tput setaf 1)✖ %s$(tput sgr0)\n" "$@"
}

# Warning logging
e_warning() {
    printf "$(tput setaf 3)! %s$(tput sgr0)\n" "$@"
}

#set -x

e_header "准备全局参数"
BINPATH=$(cd `dirname $0`; pwd)
SOURCE_PATH="${BINPATH}/web/"


e_header "开始运行"

php ${SOURCE_PATH}/cli.php task/start

e_success "OK"