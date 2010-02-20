#!/bin/bash

. ../inc/pervasive.sh

###########################################################
echo "Setting up new database: target db is $DATABASE, source prefix is $DBPREFIX"
echo "* press ^D to start import"
cat

echo -n "* copying tables "
./copy_tables.php || die "ERROR"
echo "OK"

for sql in ../newdirectory-0.0.1/*.sql
do
    echo -n "* running $sql "
    (sed -e "s/#\([0-9a-z]*\)#/${DBPREFIX}\1/g" < $sql | $MYSQL $DATABASE &>/dev/null) || die "ERROR"
    echo "OK"
done

for sql in *.sql
do
    echo -n "* running $sql "
    (sed -e "s/#\([0-9a-z]*\)#/${DBPREFIX}\1/g" < $sql | $MYSQL $DATABASE &>/dev/null) || die "ERROR"
    echo "OK"
done

###########################################################
echo -n "Importing phone numbers "

./newdirectory-0.0.1/phones.php || die "ERROR"
echo "OK"

###########################################################
echo "Updating birthday dates "

./birthday.php || die "ERROR"
echo "OK"
