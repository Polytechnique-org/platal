#! /bin/bash

# import des données
#scp -i ax_xorg_rsa xorg@polytechniciens.com:/home/axasso/ax-import/export_4D.txt.rar .
#unrar e -inul export_4D.txt.rar
cp /home/x2004jacob/export*utf8.TXT .

# séparation en fichiers de tables 
cat export_total* | grep ^AD > Adresses.txt
cat export_total* | grep ^AN > Anciens.txt
cat export_total* | grep ^FO > Formations.txt
cat export_total* | grep ^AC > Activites.txt
cat export_total* | grep ^EN > Entreprises.txt

exit 1

# intégration dans notre bdd
echo intégration dans notre bdd
$MYSQL x4dat < Activites.sql
$MYSQL x4dat < Adresses.sql
$MYSQL x4dat < Anciens.sql
$MYSQL x4dat < Formations.sql
$MYSQL x4dat < Entreprises.sql

# nettoyage
echo nettoyage
#rm Adresses.txt Anciens.txt Formations.txt Activites.txt Entreprises.txt export_4D.txt.rar export-total*
rm Adresses.txt Anciens.txt Formations.txt Activites.txt Entreprises.txt export-total*
