#! /bin/ash

TEMPLATES='/etc/mailman/xorg'
URL='http://listes.polytechnique.org'
TARGET="/var/lib/mailman/lists/$1/fr"

MBOX=${1#*_}
FQDN=${1%%_*}

ALIST="${MBOX}-owner@${FQDN}"
LIST="${MBOX}@${FQDN}"
ADMIN="$URL/admin/$LIST"
MEMBERS="$URL/members/$LIST"
MODERATE="$URL/moderate/$LIST"

mkdir -p "$TARGET"

for tpl in $TEMPLATES/*txt
do
    cat $tpl \
    | sed -e "s,{{{ALIST}}},$ALIST,g ; s,{{{LIST}}},$LIST,g ; s,{{{ADMIN}}},$ADMIN,g ; s,{{{MEMBERS}}},$MEMBERS,g ; s,{{{MODERATE}}},$MODERATE,g" \
    > "$TARGET/${tpl#$TEMPLATES/}
done

