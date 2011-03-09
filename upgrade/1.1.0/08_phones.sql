ALTER TABLE profile_phones CHANGE COLUMN link_type link_type ENUM('address','pro','user','hq', 'group') NOT NULL DEFAULT 'user';

-- vim:set syntax=mysql:
