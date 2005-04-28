update groupex.asso set nom=replace(nom, '<small>', '');
update groupex.asso set nom=replace(nom, '</small>', '');

alter table groupex.asso drop column m_list;
alter table groupex.asso change column nom nom CHAR(255) not null;
alter table groupex.asso change column diminutif diminutif CHAR(64) not null;
alter table groupex.asso change column site site CHAR(255) not null;
alter table groupex.asso change column mail mail CHAR(255) not null;
alter table groupex.asso change column resp resp CHAR(255) not null;
alter table groupex.asso change column forum forum CHAR(255) not null;
alter table groupex.asso change column mail_domain mail_domain CHAR(255) not null;
alter table groupex.asso add unique index (diminutif);

alter table groupex.membres change column uid uid int not null;

