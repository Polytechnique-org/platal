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
    cp -f ../../scripts/mailman/mails/*.txt /etc/mailman/fr/
    echo .
}

function mailman_start() {
    echo -n "starts mailman"
    /etc/init.d/mailman start &>/dev/null
    echo .
}

