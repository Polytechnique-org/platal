-- profiles.xorg_id was int(8) and converting to VARCHAR keeps the values.
ALTER TABLE profiles CHANGE xorg_id xorg_id VARCHAR(20);

-- vim:set syntax=mysql:
