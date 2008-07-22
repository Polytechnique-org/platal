use logger;

alter table events change column data data text default null;

use x4dat;

# vim:set syntax=mysql:
