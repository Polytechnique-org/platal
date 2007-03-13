alter table auth_user_quick add column last_version varchar(16) not null default '0.9.13';

# vim:set syntax=mysql:
