alter table evenements change flags flags set('valide', 'archive', 'important') not null;
create table evenements_photo (
    eid smallint(4) unsigned not null,
    attachmime enum('jpeg', 'png', 'gif') not null default 'jpeg',
    attach blob not null,
    x smallint(5) unsigned not null default 0,
    y smallint(5) unsigned not null default 0,
    primary key eid (eid)
) charset=utf8;

# vim:set syntax=mysql:
