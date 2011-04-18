ALTER TABLE accounts ADD COLUMN firstname VARCHAR(255) DEFAULT NULL AFTER email;
ALTER TABLE accounts ADD COLUMN lastname VARCHAR(255) DEFAULT NULL AFTER firstname;

UPDATE TABLE  accounts          AS a
  INNER JOIN  profile_name_enum AS le ON (le.type = 'lastname')
  INNER JOIN  profile_name_enum AS ce ON (ce.type = 'lastname_ordinary')
  INNER JOIN  profile_name      AS l  ON (a.uid = l.uid AND le.id = l.typeid)
   LEFT JOIN  profile_name      AS c  ON (a.uid = c.uid AND ce.id = c.typeid)
         SET  a.lastname = IF(c.uid IS NULL, IF(l.particle != '', l.name, CONCAT(l.particle, ' ', l.name))
                                             IF(c.particle != '', c.name, CONCAT(c.particle, ' ', c.name)))
       WHERE  a.type IN ('x', 'master', 'phd');

UPDATE TABLE  accounts          AS a
  INNER JOIN  profile_name_enum AS fe ON (fe.type = 'firstname')
  INNER JOIN  profile_name_enum AS ce ON (ce.type = 'firstname_ordinary')
  INNER JOIN  profile_name      AS f  ON (a.uid = f.uid AND fe.id = f.typeid)
   LEFT JOIN  profile_name      AS c  ON (a.uid = c.uid AND ce.id = c.typeid)
         SET  a.firstname = IF(c.uid IS NULL, f.name, c.name)
       WHERE  a.type IN ('x', 'master', 'phd');

-- vim:set syntax=mysql:
