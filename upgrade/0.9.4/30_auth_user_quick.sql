alter table auth_user_quick add column emails_alias_pub enum('public', 'private') not null default 'private';
alter table auth_user_quick add column profile_from_ax INT(1) not null default 0;
alter table auth_user_quick add column core_mail_fmt enum('html', 'texte') not null default 'html';

