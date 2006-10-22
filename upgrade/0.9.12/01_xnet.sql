use groupex;
alter table evenements add column accept_nonmembre tinyint(1) not null default 0;
alter table evenements add column archive tinyint(1) not null default 0;
use x4dat;
