#!/bin/sh

echo "drop table if exists $2; create table $2 like $1; alter table $2 engine = innodb; insert into $2 select * from $1;" | mysql x5dat
