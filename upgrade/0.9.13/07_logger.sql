use logger;
alter table sessions add column forward_ip varchar(64) default NULL;
alter table sessions add column forward_host varchar(128) default NULL;
alter table sessions add column flags set('proxy') NOT NULL default '';
alter table sessions add key(forward_ip);
alter table sessions add key(ip);
insert into actions (text, description) values ('view_page', 'Consultation d\'une page');
use x4dat;

# vim:set syntax=mysql:
