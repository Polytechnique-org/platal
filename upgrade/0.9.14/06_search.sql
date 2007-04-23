alter table search_name add column soundex char(4) not null;
alter table search_name add key soundex (soundex);
alter table search_name add flags set('public') not null default '';

# vim:set syntax=mysql:
