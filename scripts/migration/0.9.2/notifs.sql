alter table auth_user_md5 add column watch set('contacts', 'deaths') default 'contacts' not null;

create table watch (
    user_id smallint not null,
    `type` enum('promo','non-inscrit') not null,
    arg smallint not null,
    primary key (user_id,type,arg)
);

create table watch_ops (
    user_id smallint not null,
    op enum('fiche','death','photo','ins') not null,
    primary key (user_id,op)
);
