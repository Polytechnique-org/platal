UPDATE  profile_public_names
   SET  lastname_main = REPLACE(lastname_main, '  ', ' ')
 WHERE  lastname_main LIKE '%  %';

    UPDATE  accounts             AS a
INNER JOIN  account_profiles     AS ap  ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
INNER JOIN  profile_public_names AS ppn ON (ppn.pid = ap.pid)
INNER JOIN  profile_display      AS pd  ON (pd.pid = ap.pid)
       SET  a.lastname = IF(ppn.lastname_ordinary = '', ppn.lastname_main, ppn.lastname_ordinary)
     WHERE  a.type IN ('x', 'master', 'phd');

-- vim:set syntax=mysql:
