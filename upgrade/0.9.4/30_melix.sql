alter table auth_user_quick add column emails_alias_pub enum('public', 'private') not null default 'private';
