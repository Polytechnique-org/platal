# Rewrite protection

alter table emails add column hash varchar(32) default NULL;
alter table emails add column allow_rewrite boolean default false;
update emails set allow_rewrite = true where rewrite != '' and flags != 'filter';


# vim:set syntax=mysql:
