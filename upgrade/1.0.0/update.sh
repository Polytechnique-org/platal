#!/bin/bash

. ../inc/pervasive.sh

###########################################################
[ "$DATABASE" != "x4dat" ] || die "Cannot target x4dat"

confirm "Setting up new database: target db is \"$DATABASE\", source prefix is \"$DBPREFIX\""

echo "* create database"
mysql_exec "CREATE DATABASE IF NOT EXISTS $DATABASE;"
echo "OK"

echo "* copying tables "
(../account/copy_tables.sh | while read line; do mysql_exec "$line"; done) || die "ERROR"
echo "OK"

mysql_run_directory ../newdirectory-0.0.1
mysql_run_directory ../account
mysql_run_directory .

###########################################################
confirm "* Running upgrade scripts"
script_run ../newdirectory-0.0.1/phones.php
script_run ../newdirectory-0.0.1/addresses.php
script_run ../newdirectory-0.0.1/alternate_subsubsectors.php
script_run ../account/birthday.php
