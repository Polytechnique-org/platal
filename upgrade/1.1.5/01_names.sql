ALTER TABLE profile_public_names DROP COLUMN particles;
ALTER TABLE accounts ADD COLUMN sort_name VARCHAR(255) DEFAULT NULL AFTER directory_name;

-- vim:set syntax=mysql:
