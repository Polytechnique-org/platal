alter table search_name add column soundex char(4) not null;
alter table search_name add key soundex (soundex);

# vim:set syntax=mysql:
