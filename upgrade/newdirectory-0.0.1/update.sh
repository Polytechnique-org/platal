#!/bin/bash

. ../inc/pervasive.sh

mailman_stop
mailman_templates
mailman_start


###########################################################
for sql in *.sql
do
    echo -n $sql
    $MYSQL x4dat < $sql &>/dev/null || echo -n " ERROR"
    echo .
done

###########################################################

echo "Importing phone numbers"

./phones.php

###########################################################

echo "we will now upgrade the search table (this may be a long operation)

please hit ^D to continue
"

cat

pushd ../../bin
./search.rebuild_db.php
popd

###########################################################

