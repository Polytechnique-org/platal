#!/bin/bash

. ../inc/pervasive.sh

echo "Upgrading bogofilter settings for ML"
sudo -u list ./upgrade_lists.py

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

echo "we will now upgrade the search table (this may be a long operation)

please hit ^D to continue
"

cat

pushd ../../bin
./search.rebuild_db.php
popd

###########################################################

