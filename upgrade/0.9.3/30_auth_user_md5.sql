alter table auth_user_md5 change column perms perms enum('user','admin','non-inscrit','pending','disabled');
update auth_user_md5 set perms='pending' where perms='non-inscrit';
alter table auth_user_md5 change column perms perms enum('user','admin','pending','disabled');
alter table auth_user_md5 change column comment comment varchar(64) not null;
