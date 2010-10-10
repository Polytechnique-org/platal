#!/bin/bash

MYSQL='mysql -u admin --default-character-set=utf8 '


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
    if [[ -n "${NO_CONFIRM}" ]]; then
        echo "$1"
        echo "* press ^C to cancel, waiting 5 seconds..."
        sleep 5
    else
        echo "$1"
        echo "* press ^D to start import (^C to cancel)"
        cat
    fi
}

function mysql_pipe() {
    sed -e "s/#\([0-9a-z]*\)#/${DBPREFIX}\1/g" | $MYSQL $DATABASE
}

function mysql_exec() {
    echo -n " * executing $1 "
    if [[ -z "${DRY_RUN}" ]]; then
        (echo "$1" | mysql_pipe) || die "ERROR"
    fi
    echo "OK"
}

function mysql_pipe_nodb() {
    sed -e "s/#\([0-9a-z]*\)#/${DBPREFIX}\1/g" | $MYSQL
}

function mysql_exec_nodb() {
    echo -n " * executing $1 "
    if [[ -z "${DRY_RUN}" ]]; then
        (echo "$1" | mysql_pipe_nodb) || die "ERROR"
    fi
    echo "OK"
}

function mysql_run() {
    echo -n " * running $1 "
    if [[ -z "${DRY_RUN}" ]]; then
        (cat "$1" | mysql_pipe) || die "ERROR"
    fi
    echo "OK"
}

function create_db() {
    echo "* create database "
    mysql_exec_nodb "CREATE DATABASE IF NOT EXISTS $DATABASE;"
    mysql_exec_nodb "GRANT ALTER, CREATE, CREATE TEMPORARY TABLES, DELETE, DROP, EXECUTE, INDEX, INSERT, LOCK TABLES, SELECT, UPDATE ON $DATABASE.* TO 'web'@'localhost';"
    mysql_exec_nodb "FLUSH PRIVILEGES;"
    echo "OK"
}

function copy_db() {
    if [[ -n "$SOURCE_DATABASE" ]]; then
        confirm "* copying database from $SOURCE_DATABASE to $DATABASE"
        create_db
        echo -n "* build database from dump "
        ( mysqldump --add-drop-table -Q $SOURCE_DATABASE | $MYSQL $DATABASE ) \
            || die "ERROR"
        echo "OK"
    fi
}

function mysql_run_directory() {
    for sql in $1/*.sql ; do
        mysql_run $sql
    done
}

function script_run() {
    echo -n " * running $1 "
    if [[ -z "${DRY_RUN}" ]]; then
        $1 || die "ERROR"
    fi
    echo "OK"
}

function mailman_stop() {
    echo -n " * stops mailman"
    if [[ -z "${DRY_RUN}" ]]; then
        /etc/init.d/mailman stop &>/dev/null
    fi
    echo .
}

function mailman_templates() {
    echo -n " * copies new mails templates"
    if [[ -z "${DRY_RUN}" ]]; then
        mkdir -p /etc/mailman/xorg
        cp -f ../../modules/lists/mail_templates/*.txt /etc/mailman/xorg
    fi
    echo .
}

function mailman_start() {
    echo -n " * starts mailman"
    if [[ -z "${DRY_RUN}" ]]; then
        /etc/init.d/mailman start &>/dev/null
    fi
    echo .
}
