#!/bin/sh

find /var/lib/php5 -maxdepth 1 -name 'sess_*' -type f -delete
