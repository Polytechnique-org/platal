#!/bin/bash

MYSQL='mysql -u admin '


set -e

if [ "$UID" != 0 ]; then
    echo "has to be run as root"
    exit 1
fi

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

