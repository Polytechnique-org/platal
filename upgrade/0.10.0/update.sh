#!/bin/bash

. ../inc/pervasive.sh

mailman_stop
mailman_templates
mailman_start


###########################################################
for sql in *.sql
do
    mysql_run $sql
done

###########################################################
echo "Creating forlife ids for unregistered user (takes a while)."

./hruid.update.php

###########################################################

