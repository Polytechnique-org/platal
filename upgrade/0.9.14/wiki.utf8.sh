#!/bin/sh

WIKISPOOLDIR='../../spool/wiki.d/'
IMAGESPOOLDIR='../../spool/uploads/'

find $WIKISPOOLDIR -name 'cache_*' -or -name 'tmp_*' -exec rm {} ";"
for i in `find $WIKISPOOLDIR -type f`; do
    CONV=`echo -n $i | iconv -t UTF-8`
    mv $i $i.latin1
    iconv -t UTF-8 $i.latin1 > $CONV
done

for i in `find $IMAGESPOOLDIR -type f`; do
    CONV=`echo -n $i | iconv -t UTF-8`
    if [ $i != $CONV ]; then
        mv $i $CONV
    fi
done

echo "Les pages de wiki ont ete converites en UTF-8"
echo "Verifie que tout c'est bien passe en presse ^D"
cat

find $WIKISPOOLDIR -name '*.latin1' -exec rm {} ";"
chown -R www-data:www-data $WIKISPOOLDIR

