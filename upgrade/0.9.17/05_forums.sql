use forums;

alter table profils
 add column tree_unread varchar(8) not null default 'o',
 add column tree_read varchar(8) not null default 'dg';

use x4dat;

# vim:set syntax=mysql:
