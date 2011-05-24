DROP TABLE IF EXISTS profile_public_names;
CREATE TABLE IF NOT EXISTS profile_public_names (
  pid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  particles SET('initial', 'main', 'marital', 'ordinary') NOT NULL DEFAULT '',
  lastname_initial VARCHAR(255) NOT NULL DEFAULT '',
  lastname_main VARCHAR(255) NOT NULL DEFAULT '',
  lastname_marital VARCHAR(255) NOT NULL DEFAULT '',
  lastname_ordinary VARCHAR(255) NOT NULL DEFAULT '',
  firstname_initial VARCHAR(255) NOT NULL DEFAULT '',
  firstname_main VARCHAR(255) NOT NULL DEFAULT '',
  firstname_ordinary VARCHAR(255) NOT NULL DEFAULT '',
  pseudonym VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (pid),
  FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS profile_private_names;
CREATE TABLE IF NOT EXISTS profile_private_names (
  pid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  type ENUM('lastname', 'firstname', 'nickname') NOT NULL DEFAULT 'nickname',
  id TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id of the name among those sharing the same pid / general_type',
  name VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (pid, type, id),
  FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Initiates profile_public_names.
INSERT INTO  profile_public_names (pid)
     SELECT  pid
       FROM  profiles;

-- Insert lastnames.
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.lastname_initial = IF(pn.particle = '', pn.name, CONCAT(pn.particle, ' ', pn.name)),
            ppn.particles = IF(pn.particle = '', '', 'initial')
     WHERE  pne.type = 'name_ini';
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.lastname_main = IF(pn.particle = '', pn.name, CONCAT(pn.particle, ' ', pn.name)),
            ppn.particles = IF(pn.particle = '', ppn.particles, CONCAT_WS(',', ppn.particles, 'main'))
     WHERE  pne.type = 'lastname';
UPDATE  profile_public_names
   SET  particles = TRIM(BOTH ',' FROM particles);
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.lastname_marital = IF(pn.particle = '', pn.name, CONCAT(pn.particle, ' ', pn.name)),
            ppn.particles = IF(pn.particle = '', ppn.particles, CONCAT_WS(',', ppn.particles, 'marital'))
     WHERE  pne.type = 'lastname_marital';
UPDATE  profile_public_names
   SET  particles = TRIM(BOTH ',' FROM particles);
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.lastname_ordinary = IF(pn.particle = '', pn.name, CONCAT(pn.particle, ' ', pn.name)),
            ppn.particles = IF(pn.particle = '', ppn.particles, CONCAT_WS(',', ppn.particles, 'ordinary'))
     WHERE  pne.type = 'lastname_ordinary';
UPDATE  profile_public_names
   SET  particles = TRIM(BOTH ',' FROM particles);

-- Insert other names.
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.firstname_initial = pn.name
     WHERE  pne.type = 'firstname_ini';
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.firstname_main = pn.name
     WHERE  pne.type = 'firstname';
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.firstname_ordinary = pn.name
     WHERE  pne.type = 'firstname_other';
    UPDATE  profile_public_names AS ppn
INNER JOIN  profile_name         AS pn  ON (pn.pid = ppn.pid)
INNER JOIN  profile_name_enum    AS pne ON (pn.typeid = pne.id)
       SET  ppn.pseudonym = pn.name
     WHERE  pne.type = 'pseudonym';

-- Insert privates names.
INSERT INTO  profile_private_names (pid, type, id, name)
     SELECT  pn.pid, 'nickname', 0, pn.name
       FROM  profile_name      AS pn
 INNER JOIN  profile_name_enum AS pne ON (pn.typeid = pne.id)
      WHERE  pne.type = 'nickname';
INSERT INTO  profile_private_names (pid, type, id, name)
     SELECT  pn.pid, 'lastname', 0, pn.name
       FROM  profile_name      AS pn
 INNER JOIN  profile_name_enum AS pne ON (pn.typeid = pne.id)
      WHERE  pne.type = 'name_other';
INSERT INTO  profile_private_names (pid, type, id, name)
     SELECT  pn.pid, 'firstname', 0, pn.name
       FROM  profile_name      AS pn
 INNER JOIN  profile_name_enum AS pne ON (pn.typeid = pne.id)
      WHERE  pne.type = 'firstname_other';

-- DROP TABLE IF EXISTS profile_name;
-- DROP TABLE IF EXISTS profile_name_enum;

-- vim:set syntax=mysql:
