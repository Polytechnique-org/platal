alter table auth_user_md5 change column perms perms enum('user','admin','non-inscrit','disabled');
alter table auth_user_md5 change column comment comment varchar(64) not null;
