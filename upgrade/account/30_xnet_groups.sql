# Clean up group_members
UPDATE group_members SET prenom = NULL WHERE LENGTH(TRIM(prenom)) = 0;
UPDATE group_members SET nom = NULL WHERE LENGTH(TRIM(nom)) = 0;

# Create one account by email
INSERT INTO accounts (
  SELECT NULL AS uid,
         CONCAT(LOWER(REPLACE(email,'@','.')),'.ext') AS hruid,
         IF(origine = 'ext','xnet','virtual') AS type,
         0 as is_admin,
         'disabled' AS state,
         NULL as password,
         NULL as token,
         NULL as weak_password,
         NULL as registration_date,
         '' AS flags,
         comm AS comment,
         email,
         IF(prenom IS NULL,
            REPLACE(LEFT(email, POSITION('@' IN email)-1),'.',' '),
            CONCAT(prenom,' ',nom)) AS full_name,
         IF(prenom IS NULL,
            LEFT(email, POSITION('.' IN REPLACE(email,'@','.'))-1),
            prenom) AS display_name,
         IF(sexe,'female','male') AS sex,
         'html' AS email_format,
         1 AS skin,
         NULL as last_version
  FROM group_members WHERE origine != 'x' GROUP BY hruid);

# Make Upper case for first letters in fake full_names and display_names
UPDATE accounts AS a, group_members AS g
   SET a.display_name = CONCAT(UPPER(LEFT(a.display_name,1)),LOWER(MID(a.display_name, 2))),
       a.full_name = CONCAT(UPPER(LEFT(a.full_name,1)),LOWER(MID(a.full_name, 2)))
 WHERE a.hruid =  CONCAT(LOWER(REPLACE(g.email,'@','.')),'.ext') AND g.prenom IS NULL;
UPDATE accounts AS a, group_members AS g
   SET a.full_name = CONCAT(
        LEFT(a.full_name, POSITION(' ' IN a.full_name)),
        UPPER(MID(a.full_name, POSITION(' ' IN a.full_name) + 1, 1)),
        MID(a.full_name, POSITION(' '  IN a.full_name) + 2))
 WHERE a.hruid = CONCAT(LOWER(REPLACE(g.email,'@','.')),'.ext') AND g.prenom IS NULL AND POSITION(' ' IN a.full_name);

# Delete person that are several time in same asso
DELETE g2
        FROM group_members AS g1
  INNER JOIN group_members AS g2 ON (g1.asso_id = g2.asso_id AND g1.email = g2.email AND g1.uid < g2.uid)
  WHERE g1.origine != 'x' AND g2.origine != 'x';

# Update uids
UPDATE group_members AS g, accounts AS a
  SET g.uid = a.uid
  WHERE g.origine != 'x' AND
   a.hruid = CONCAT(LOWER(REPLACE(g.email,'@','.')),'.ext');

# Enable virtual accounts (for groups) with no rights
INSERT IGNORE INTO account_types VALUES('virtual', '');

# Drop now unused columns
ALTER TABLE group_members
  DROP COLUMN sexe,
  DROP COLUMN prenom,
  DROP COLUMN nom,
  DROP COLUMN origine,
  DROP COLUMN email;

# vim:set syntax=mysql:
