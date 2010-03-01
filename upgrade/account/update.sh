#!/bin/bash

. ../inc/pervasive.sh

###########################################################
[ "$DATABASE" != "x4dat" ] || die "Cannot target x4dat"

echo "Setting up new database: target db is \"$DATABASE\", source prefix is \"$DBPREFIX\""
echo "* press ^D to start import (^C to cancel)"
cat

echo -n "* create database "
(echo "CREATE DATABASE IF NOT EXISTS $DATABASE;" | mysql_run) || die "ERROR"
echo "OK"

echo -n "* copying tables "
(./copy_tables.php | mysql_run) || die "ERROR"
echo "OK"

for sql in ../newdirectory-0.0.1/*.sql *.sql
do
    echo -n "* running $sql "
    (cat $sql | mysql_run) || die "ERROR"
    echo "OK"
done

###########################################################
echo -n "Importing phone numbers "

../newdirectory-0.0.1/phones.php || die "ERROR"
echo "OK"

###########################################################
echo "Updating birthday dates "

./birthday.php || die "ERROR"
echo "OK"
