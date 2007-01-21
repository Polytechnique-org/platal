alter table profile_medals add column flags set('validation') not null default 'validation';
update profile_medals set flags = '' where id = 20;
alter table requests_answers change category category enum('alias','liste','usage','photo','evts','marketing','orange','homonyme','nl','paiements','medal','broken') not null;
# vim:set syntax=mysql:
