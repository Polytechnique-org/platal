alter table register_marketing add column message varchar(16) not null default 'default';
alter table register_marketing add column message_data varchar(64);
create table register_subs
(
    uid smallint(4) unsigned not null,
    type enum('list', 'group') not null default 'list',
    sub varchar(32) not null,
    domain varchar(64) not null,

    primary key id (uid, type, sub, domain)
) charset=utf8;

# vim:set syntax=mysql:
