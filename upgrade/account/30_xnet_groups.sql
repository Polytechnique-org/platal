# Create one account by email
INSERT INTO accounts (
  SELECT NULL AS uid,
         CONCAT(LOWER(REPLACE(email,'@','.')),'.ext') AS hruid,
         'xnet' AS type,
         0 as is_admin,
         'disabled' AS state,
         NULL as password,
         NULL as token,
         NULL as weak_password,
         NULL as registration_date,
         '' AS flags,
         comm AS comment,
         email,
         CONCAT(prenom,' ',nom) AS full_name,
         prenom AS display_name,
         IF(sexe,'female','male') AS sex,
         'html' AS email_format,
         1 AS skin,
         NULL as last_version
  FROM group_members WHERE origine = 'ext' GROUP BY hruid);

# Delete person that are several time in same asso
DELETE g2
        FROM group_members AS g1
  INNER JOIN group_members AS g2 ON (g1.asso_id = g2.asso_id AND g1.email = g2.email AND g1.uid < g2.uid)
  WHERE g1.origine = 'ext' AND g2.origine = 'ext';

# Update uids
UPDATE group_members AS g, accounts AS a
  SET g.uid = a.uid
  WHERE g.origine = 'ext' AND
   a.hruid = CONCAT(LOWER(REPLACE(g.email,'@','.')),'.ext');

# vim:set syntax=mysql:
