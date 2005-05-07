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
alter table groupex.asso add column sub_url char(255) not null;

alter table groupex.membres change column uid uid int not null;

update groupex.asso set sub_url='http://www2.x-consult.org/xconsult/register.php' where diminutif = 'X-Mines-Consult';

