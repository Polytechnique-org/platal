alter table auth_user_md5 change column perms perms enum('user','admin','non-inscrit','pending','disabled');
update auth_user_md5 set perms='pending' where perms='non-inscrit';
alter table auth_user_md5 change column perms perms enum('user','admin','pending','disabled');
alter table auth_user_md5 change column comment comment varchar(64) not null;

alter table auth_user_md5 add column foo int;
update auth_user_md5 set foo=naissance ;
alter table auth_user_md5 change column naissance naissance date not null;
update auth_user_md5 set naissance = concat(foo % 10000, '-', (foo / 10000) % 100, '-', foo / 1000000);
alter table auth_user_md5 drop column foo;

alter table en_cours add column foo int;
update en_cours set foo=naissance ;
alter table en_cours change column naissance naissance date not null;
update en_cours set naissance = concat(foo % 10000, '-', (foo / 10000) % 100, '-', foo / 1000000);
alter table en_cours drop column foo;
