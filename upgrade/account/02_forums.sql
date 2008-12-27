# Move forums tables to x4dat
#RENAME  forums.list
#    TO  x4dat.forums;
#RENAME  forums.abos
#    TO  x4dat.forum_subs;
#RENAME  forums.innd
#    TO  x4dat.forum_innd;
#RENAME  forums.porfils
#    TO  x4dat.forum_profiles;
#DROP DATABASE forums;

## Dev version of previous line
# (non destructive)
CREATE TABLE  x4dat.forums
        LIKE  forums.list;
INSERT INTO  x4dat.forums
     SELECT  *
       FROM  forums.list;

CREATE TABLE  x4dat.forum_subs
        LIKE  forums.abos;
INSERT INTO  x4dat.forum_subs
     SELECT  *
       FROM  forums.abos;

CREATE TABLE  x4dat.forum_innd
        LIKE  forums.innd;
INSERT INTO  x4dat.forum_innd
     SELECT  *
       FROM  forums.innd;

CREATE TABLE  x4dat.forum_profiles
        LIKE  forums.profils;
INSERT INTO  x4dat.forum_profiles
     SELECT  *
       FROM  forums.profils;


# Conform to naming convention.
  ALTER TABLE  forums
CHANGE COLUMN  nom name VARCHAR(64) NOT NULL;

  ALTER TABLE  forum_profiles
CHANGE COLUMN  nom name VARCHAR(64) NOT NULL,
   ADD COLUMN  last_seen TIMESTAMP NOT NULL DEFAULT '0000-00-00';

# vim:set syntax=mysql:
