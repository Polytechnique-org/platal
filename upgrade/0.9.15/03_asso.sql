use groupex;
ALTER TABLE asso CHANGE COLUMN pub pub enum('public','private','membre') not null default 'public';
use x4dat;

# vim:set syntax=mysql:
