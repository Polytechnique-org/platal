# Creates a new column for the hruid field, and adds an index on it.
ALTER TABLE auth_user_md5
    ADD COLUMN hruid VARCHAR(255) DEFAULT NULL AFTER user_id,
    ADD UNIQUE INDEX hruid(hruid);


# Pre-fills the hruid field with the current forlife.
   UPDATE  auth_user_md5 AS u
LEFT JOIN  aliases AS a ON (a.id = u.user_id AND a.type = 'a_vie')
      SET  u.hruid = a.alias
    WHERE  a.alias IS NOT NULL AND u.hruid IS NULL;

# vim:set syntax=mysql:
