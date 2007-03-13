alter table auth_user_md5 change flags flags set('femme', 'watch') not null;
alter table auth_user_md5 add key nationalite(nationalite);
alter table adresses add key country(country);
alter table adresses add key region(region);

# vim:set syntax=mysql:
