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

echo "
You now have to :

(*) install FPDF (pdflatex and tetex are not required anymore)
    apt-get install php-fpdf

(*) apt-get install php4-gd2
    add extension=gd.so in /etc/php/apache(2?)/php.ini
    
(*) apt-get install php-banana (may not be on public servers)
    and add a rewrite rule on www.polytechnique.org (http and https) :
    ^/banana/([^/]*/.*)$    /usr/share/banana/\$1

(*) install the new cron system :
    delete ALL web crons from the crontab EXCEPT for the /home/web/bin/espace_disque that is not a plat/al feature
    and then, link /etc/cron.d/platal -> platal/configs/platal.cron
"
