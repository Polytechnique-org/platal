#!/bin/bash

set -e

if [ "$UID" != 0 ]; then
    echo "has to be run as root"
    exit 1
fi



###########################################################
echo -n "stops mailman"
/etc/init.d/mailman stop &>/dev/null
echo .

echo -n "now updates mailman to use new Bogofilter policy"
./mailman_update.py | grep "ERROR" || echo .

echo -n "copies new mails templates"
cp -f ../../mailman/mails/*.txt /etc/mailman/fr/
echo .

echo -n "starts mailman"
/etc/init.d/mailman start &>/dev/null
echo .

###########################################################
echo -n "now drop x4dat.emploi_naf"
echo 'drop table x4dat.emploi_naf;' | mysql -u root x4dat &>/dev/null || echo -n ": FAILED"
echo .

###########################################################
echo -n "updating newsletter tables"
mysql -u root x4dat < newsleter.sql &>/dev/null	    || echo 'newsletter.sql FAILED'
mysql -u root x4dat < newsleter_art.sql &>/dev/null || echo 'newsletter_art.sql FAILED'
mysql -u root x4dat < newsleter_cat.sql &>/dev/null || echo 'newsletter_cat.sql FAILED'
mysql -u root x4dat < newsleter_ins.sql &>/dev/null || echo 'newsletter_ins.sql FAILED'
echo '.'

###########################################################
echo "ALL IS OK."
