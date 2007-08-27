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
  - insert scripts/cron/send_notifs.php in the crontab
    suggested : 0 4 * * 6  (it means every saturday at 4 AM)
"

###########################################################
echo "To make statistics work :
  - remove old cron (web cron) : /home/web/bin/nbx,
      genere.sh,genereParselog,genereParselog2
  - add new scripts (from scripts/cron/stats)in cron
      evolution-inscrits-mails, plot-graphs, mailParselog
  - add symlinks :
      in htdocs/stats : ln -s /home/web/stats/graph-* .
      in templates/stats : ln -s /home/web/stats/lastParselog* .

"
