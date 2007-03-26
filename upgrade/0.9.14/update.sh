#!/bin/bash

. ../inc/pervasive.sh

mailman_stop
mailman_templates
mailman_start

###########################################################

echo "we will now convert the wiki spool to UTF8. This may cause page corruption."

./wiki.utf8.php

###########################################################

echo "fix geoloc table charset

please hit ^D to continue"

cat

./geoloc.utf8.php

###########################################################

echo "upgrading database"

for sql in *.sql
do
    echo -n $sql
    $MYSQL x4dat < $sql &>/dev/null || echo -n " ERROR"
    echo .
done

###########################################################

echo "we will now upgrade the search table (this may be a long operation)

please hit ^D to continue
"

cat

pushd ../../bin
./search.rebuild_db.php
popd

###########################################################

echo "we will now upgrade the banana spool

please hit ^D to continue
"

cat

pushd ../../bin
rm -rf /var/spool/banana/MLArchive
rm -f /var/spool/banana/templates_c/*
./banana.spoolgen.php
popd

############################################################
