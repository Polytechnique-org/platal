#! /bin/sh
mysql x4dat < newsleter.sql

list_members polytechnique.org-newsletter | \
    sed -e 's!^\(.*\)@polytechnique.org!INSERT INTO newsletter_ins SELECT id FROM aliases WHERE alias="\1";!' | \
    mysql x4dat

echo "DELETE FROM aliases WHERE alias LIKE 'newsletter%';" | mysql x4dat 
