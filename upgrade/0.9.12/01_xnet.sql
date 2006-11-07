use groupex;
alter table evenements add column accept_nonmembre tinyint(1) not null default 0;
alter table evenements add column archive tinyint(1) not null default 0;

create table announces (
    id smallint(4) unsigned not null auto_increment,
    user_id smallint(4) unsigned not null default 0,
    asso_id smallint(4) unsigned not null default 0,
    create_date timestamp(14) default null,
    titre varchar(255) not null,
    texte mediumtext not null,
    contacts mediumtext not null,
    peremption date not null default '0000-00-00',
    promo_min smallint(4) unsigned not null default 0,
    promo_max smallint(4) unsigned not null default 0,
    flags set('public') not null default '',

    primary key (id)
);

create table announces_read (
    announce_id smallint(4) unsigned not null default 0,
    user_id smallint(4) unsigned not null default 0,
    primary key (announce_id, user_id)
);

use x4dat;
