use groupex;
alter table membres change origine origine enum('X', 'ext', 'groupe') not null default 'X';
use x4dat;
# vim:set syntax=mysql:
