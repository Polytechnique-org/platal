alter table watch_cat add column type enum('basic', 'near', 'often') not null default 'basic';
update watch_cat set type='often' where frequent=1;
alter table watch_cat drop column frequent;
insert into watch_cat (id, short, mail, type) values(4, 'Anniversaires', 'Ces camarades ont fêté leur anniversaire cette semaine', 'near');

replace into watch_sub (uid, cid) select user_id, 4 from auth_user_md5 where perms in ('admin', 'user') and deces=0;
alter table watch_cat add mail_sg varchar(80) default NULL after mail;
update watch_cat set mail_sg = 'Ce camarade a mis sa fiche à jour' where id = 1;
update watch_cat set mail_sg = 'Ce camarade s\'est inscrit' where id = 2;
update watch_cat set mail_sg = 'Ce camarade nous a quitté' where id = 3;
update watch_cat set mail_sg = 'Ce camarade a fêté son anniversaire la semaine dernère' where id = 4;

