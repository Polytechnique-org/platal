alter table aliases add index (type);
alter table auth_user_quick add column watch_flags set('contacts','mail') not null default 'contacts';
alter table auth_user_quick add column watch_last timestamp not null;

create table watch_cat (
    id smallint not null auto_increment,
    short varchar(40),
    mail  varchar(80),
    frequent smallint(1) default 0,
    primary key (id),
    index (frequent)
);

insert into watch_cat VALUES(1,'Mise à jour de fiche', 'Ces camarades ont mis leur fiche à jour', 1);
insert into watch_cat VALUES(2,'Nouveaux inscrits', 'Ces camarades se sont inscrit', 0);
insert into watch_cat VALUES(3,'Décès', 'Ces camarades nous ont quitté', 0);

create table watch_ops (
    uid smallint not null,
    cid smallint not null,
    known timestamp not null,
    date date not null,
    info varchar(255),
    primary key (uid,cid),
    index (known)
);

create table watch_sub (
    uid smallint not null,
    cid smallint not null,
    primary key (uid,cid)
);

create table watch_nonins (
    uid smallint not null,
    ni_id smallint not null,
    primary key (uid,ni_id)
);

create table watch_promo (
    uid smallint not null,
    promo smallint not null,
    primary key (uid,promo)
);

replace into  watch_sub
     select  u.user_id,id
       from  auth_user_md5 AS u
       join  watch_cat
      where  u.perms != 'non-inscrit';


