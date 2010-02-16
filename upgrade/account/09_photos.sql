CREATE TABLE  profile_photos
        LIKE  photo;
 INSERT INTO  profile_photos
      SELECT  *
        FROM  photo;

  ALTER TABLE  profile_photos
CHANGE COLUMN  uid pid INT(6) not null;


# vim:set ft=mysql:
