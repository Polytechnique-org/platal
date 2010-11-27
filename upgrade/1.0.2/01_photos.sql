ALTER TABLE  profile_photos
 ADD COLUMN  last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- vim:set syntax=mysql:
