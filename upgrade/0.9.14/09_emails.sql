alter table emails change column flags flags enum('active','filter','panne','disable') not null default 'active';

# vim:set syntax=mysql:
