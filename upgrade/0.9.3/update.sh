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

echo "BE CAREFUL :

* scripts/ is now essentially in bin/
  (with for this release, the inner path unchanged)
  --> have to modify crons
  --> have to modify xml-rpc path in the daemontools

* carva redirects are still in scripts/ but their path and name have changed.
  --> the error page is scripts/webredirect_error.php
  --> the redirect page is scripts/webredirect.php
  "
