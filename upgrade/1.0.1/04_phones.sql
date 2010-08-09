ALTER TABLE profile_phones MODIFY COLUMN pid INT(6) NOT NULL DEFAULT 0;
ALTER TABLE profile_phones MODIFY COLUMN link_id INT(6) NOT NULL DEFAULT 0;

-- vim:set syntax=mysql:
