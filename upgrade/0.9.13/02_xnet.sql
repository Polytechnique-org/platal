use groupex;
alter table membres change origine origine enum('X', 'ext', 'groupe') not null default 'X';
alter table asso add column unsub_url varchar(255) not null;
use x4dat;
# vim:set syntax=mysql:
