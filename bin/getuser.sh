#!/bin/sh

field=$1
nom=$2
promo=$3

query="SELECT $field FROM auth_user_md5 AS u "
where=""
pos=0
for i in $nom ; do
  query="$query INNER JOIN search_name AS sn${pos} ON (u.user_id = sn${pos}.uid) "
  [ "$where" != "" ] && where="$where AND"
  where="${where} sn${pos}.token LIKE \"${i}%\""
  pos=$((pos + 1))
done
if [ "${promo}" != "" ] ; then
  [ "$where" != "" ] && where="$where AND "
  where="${where} u.promo = $promo"
fi
query="${query} WHERE ${where} GROUP BY u.user_id"

echo $query | mysql --default-character-set=utf8 -N x4dat
