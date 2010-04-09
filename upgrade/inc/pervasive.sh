#!/bin/bash

MYSQL='mysql -u admin '


set -e

if [ "$UID" != 0 ] && [ "$1" != "-u" ] ; then
    echo "has to be run as root"
    exit 1
fi

if [[ -n "${DBPREFIX}" ]]; then
    echo "Using non-default database ${DBPREFIX}x4dat."
fi
if [[ -z "${DATABASE}" ]]; then
  DATABASE="${DBPREFIX}x4dat"
fi

function die() {
    echo $1
    exit 1
}

function confirm() {
    echo "$1"
    echo "* press ^D to start import (^C to cancel)"
    cat
}

function mysql_pipe() {
    sed -e "s/#\([0-9a-z]*\)#/${DBPREFIX}\1/g" | $MYSQL $DATABASE
}

function mysql_run() {
    echo -n "* running $1"
    (cat $1 | mysql_pipe) || die "ERROR"
    echo "OK"
}

function mysql_run_directory() {
    for sql in $1/*.sql ; do
        mysql_run $sql
    done
}

function script_run() {
    echo -n "* running $1"
    $1 || die "ERROR"
    echo "OK"
}

function mailman_stop() {
    echo -n "stops mailman"
    /etc/init.d/mailman stop &>/dev/null
    echo .
}

function mailman_templates() {
    echo -n "copies new mails templates"
    mkdir -p /etc/mailman/xorg
    cp -f ../../modules/lists/mail_templates/*.txt /etc/mailman/xorg
    echo .
}

function mailman_start() {
    echo -n "starts mailman"
    /etc/init.d/mailman start &>/dev/null
    echo .
}
