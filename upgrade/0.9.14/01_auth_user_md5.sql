# Security stuff
alter table auth_user_md5 change flags flags set('femme', 'watch') not null;
alter table auth_user_md5 add column naissance_ini date not null default '0000-00-00' after smtppass;

# Deprecated stuff
alter table auth_user_md5 drop column nom_ini_soundex;
alter table auth_user_md5 drop column prenom_ini_soundex;
alter table auth_user_md5 drop column appli;
alter table auth_user_md5 drop column bits;

# Advance search
alter table auth_user_md5 add key nationalite(nationalite);
alter table adresses add key country(country);
alter table adresses add key region(region);

# vim:set syntax=mysql:
