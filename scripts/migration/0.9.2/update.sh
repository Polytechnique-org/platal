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
echo "STILL TODO :
  - update the mailman-rpc daemon
  - insert scripts/cron/watch.notifs.php in the crontab
    suggested : 0 4 * * 6  (it means every saturday at 4 AM)
"
