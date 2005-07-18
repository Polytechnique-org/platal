alter table auth_user_quick  add column foo timestamp after user_id;

alter table auth_user_quick add column emails_alias_pub enum('public', 'private') not null default 'private';
alter table auth_user_quick add column profile_from_ax INT(1) not null default 0;
alter table auth_user_quick add column core_mail_fmt enum('html', 'texte') not null default 'html';

alter table auth_user_quick add column profile_nick varchar(64) not null;
alter table auth_user_quick add index(profile_nick);

alter table auth_user_quick add column core_rss_hash char(32) not null default '';
