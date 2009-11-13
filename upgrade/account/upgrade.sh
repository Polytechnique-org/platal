#!/bin/bash

. ../inc/pervasive.sh

###########################################################
for sql in *.sql
do
    echo -n $sql
    (sed -e "s/#\([0-9a-z]*\)#/${DBPREFIX}\1/g" < $sql | $MYSQL $DATABASE &>/dev/null) || echo -n " ERROR"
    echo .
done

###########################################################
echo "Updating birthday dates"

./birthday.php
