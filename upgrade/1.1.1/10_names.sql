ALTER TABLE profile_name_enum ADD COLUMN general_type ENUM('lastname', 'firstname', 'nickname') NOT NULL DEFAULT 'lastname';

UPDATE  profile_name_enum
   SET  general_type = 'lastname'
 WHERE  type LIKE 'lastname%' OR type LIKE 'name_%';
UPDATE  profile_name_enum
   SET  general_type = 'firstname'
 WHERE  type LIKE 'firstname%';
UPDATE  profile_name_enum
   SET  general_type = 'nickname'
 WHERE  type IN ('pseudonym', 'nickname');

ALTER TABLE search_name ADD COLUMN general_type ENUM('lastname', 'firstname', 'nickname') NOT NULL DEFAULT 'lastname';

-- vim:set syntax=mysql:
