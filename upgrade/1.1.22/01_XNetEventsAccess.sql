alter table group_events add access_control enum('group','registered','all') default 'registered' after accept_nonmembre;
update group_events set access_control='group' where accept_nonmembre=0;
update group_events set access_control='registered' where accept_nonmembre=1;
