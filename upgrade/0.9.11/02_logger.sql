use logger;
alter table sessions change host host varchar(128);
alter table events change data data varchar(255);
use x4dat;
