ALTER TABLE accounts ADD COLUMN directory_name VARCHAR(255) DEFAULT NULL AFTER full_name;
UPDATE  accounts AS a
   SET  a.directory_name = (SELECT  pd.directory_name
                              FROM  profile_display  AS pd
                        INNER JOIN  account_profiles AS ap ON (ap.uid = pd.pid AND FIND_IN_SET('owner', perms))
                             WHERE  a.uid = ap.uid);

ALTER TABLE account_types
CHANGE COLUMN perms perms SET('groups', 'mail', 'directory_ax', 'directory_private',
                              'edit_directory', 'forums', 'lists', 'payment') NOT NULL;

REPLACE INTO account_types VALUES ('x', 'groups,mail,directory_private,forums,lists,payment'),
                                  ('master', 'groups,directory_private,forums,lists,payment'),
                                  ('phd', 'groups,directory_private,forums,lists,payment'),
                                  ('pi', 'groups,forums,lists'),
                                  ('ax', 'groups,directory_ax,edit_directory'),
                                  ('xnet', 'groups'),
                                  ('virtual', '');


-- vim:set syntax=mysql:
