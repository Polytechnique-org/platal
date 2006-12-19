alter table profile_medals add column flags set('validation') not null default 'validation';
update profile_medals set flags = '' where id = 20;
# vim:set syntax=mysql:
