create table tips (
    id smallint(4) unsigned not null auto_increment,
    titre varchar(64) not null,
    text mediumtext not null,
    priorite tinyint(2) unsigned not null default 127,
    peremption date not null default '0000-00-00',
    promo_min smallint(4) unsigned not null default 0,
    promo_max smallint(4) unsigned not null default 0,

    primary key (id)
);

# vim:set syntax=mysql:
