alter table geoloc_pays change column nat nat varchar(100);
update geoloc_pays
   set nat = NULL
 where a2 = 'MQ' or a2 = 'RE' or a2 = 'GP' or a2 = 'YT' or a2 = 'TF' or a2 = 'PF' or a2 = 'NC' or a2 = 'GF'
       or a2 = 'UM';
update auth_user_md5 set nationalite = 'FR' where nationalite = 'MQ' or nationalite = 'RE' or nationalite = 'GP' or nationalite = 'YT' or nationalite = 'TF' or nationalite = 'PF' or nationalite = 'NC' or nationalite = 'GF';

# vim:set syntax=mysql:
