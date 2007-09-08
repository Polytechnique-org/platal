alter table entreprises add column cityid int(11) default null after city;
alter table entreprises add column regiontxt varchar(80) not null after region;
alter table entreprises add key cityid (cityid);
alter table entreprises add key country (country);
alter table entreprises add key region (region);

# vim:set syntax=mysql:
