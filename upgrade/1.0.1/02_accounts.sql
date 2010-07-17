ALTER TABLE accounts ADD COLUMN directory_name VARCHAR(255) DEFAULT NULL;
UPDATE  accounts AS a
   SET  a.directory_name = (SELECT  pd.directory_name
                              FROM  profile_display  AS pd
                        INNER JOIN  account_profiles AS ap ON (ap.uid = pd.pid AND FIND_IN_SET('owner', perms))
                             WHERE  a.uid = ap.uid);

-- vim:set syntax=mysql:
