ALTER TABLE accounts ADD COLUMN firstname VARCHAR(255) DEFAULT NULL AFTER email;
ALTER TABLE accounts ADD COLUMN lastname VARCHAR(255) DEFAULT NULL AFTER firstname;

    UPDATE  accounts          AS a
INNER JOIN  account_profiles  AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
INNER JOIN  profile_name_enum AS le ON (le.type = 'lastname')
INNER JOIN  profile_name_enum AS ce ON (ce.type = 'lastname_ordinary')
INNER JOIN  profile_name      AS l  ON (ap.pid = l.pid AND le.id = l.typeid)
 LEFT JOIN  profile_name      AS c  ON (ap.pid = c.pid AND ce.id = c.typeid)
       SET  a.lastname = IF(c.pid IS NULL, IF(l.particle != '', l.name, CONCAT(l.particle, ' ', l.name)),
                                           IF(c.particle != '', c.name, CONCAT(c.particle, ' ', c.name)))
     WHERE  a.type IN ('x', 'master', 'phd');

    UPDATE  accounts          AS a
INNER JOIN  account_profiles  AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
INNER JOIN  profile_name_enum AS fe ON (fe.type = 'firstname')
INNER JOIN  profile_name_enum AS ce ON (ce.type = 'firstname_ordinary')
INNER JOIN  profile_name      AS f  ON (ap.pid = f.pid AND fe.id = f.typeid)
 LEFT JOIN  profile_name      AS c  ON (ap.pid = c.pid AND ce.id = c.typeid)
       SET  a.firstname = IF(c.pid IS NULL, f.name, c.name)
     WHERE  a.type IN ('x', 'master', 'phd');

-- vim:set syntax=mysql:
