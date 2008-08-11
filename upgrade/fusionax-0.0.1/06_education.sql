CREATE TABLE IF NOT EXISTS profile_education_field_enum (
  id INT(2) NOT NULL AUTO_INCREMENT,
  field VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(field)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS profile_education_degree_enum (
  id INT(2) NOT NULL AUTO_INCREMENT,
  degree VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(degree)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS profile_education_degree (
  eduid INT(4) NOT NULL DEFAULT 0,
  degreeid INT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY(eduid, degreeid)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS profile_education_enum (
  id INT(4) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) DEFAULT NULL,
  url VARCHAR(255) DEFAULT NULL,
  country CHAR(2) NOT NULL DEFAULT 'FR',
  PRIMARY KEY(id),
  UNIQUE KEY(name)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS profile_education (
  id INT(2) NOT NULL DEFAULT 0,
  uid INT(11) NOT NULL DEFAULT 0,
  eduid INT(4) NOT NULL DEFAULT 0,
  degreeid INT(4) NOT NULL DEFAULT 0,
  fieldid INT(2) NOT NULL DEFAULT 0,
  grad_year INT(4) NOT NULL DEFAULT 0,
  PRIMARY KEY(id, uid)
) CHARSET=utf8;

INSERT INTO  profile_education_field_enum (field)
     VALUES  ('Aéronautique'), ('Agronomie'), ('Assurance'), ('Biologie'),
             ('Chimie'), ('Droit'), ('Économie'), ('Électronique/électricité'),
             ('Environnement/développement durable'), ('Finance'), ('Géographie'),
             ('Histoire'), ('Informatique'), ('Langues'), ('Mathématiques'),
             ('Mathématiques appliquées'), ('Mécanique'), ('Médecine'),
             ('Philosophie'), ('Physique'), ('Sciences politiques');

INSERT INTO  profile_education_degree_enum (degree)
     VALUES  ('Diplôme'), ('Ingénieur'), ('Corps'), ('MS'), ('PhD'),
             ('DEA'), ('ME'), ('MBA'), ('MiF'), ('MPA'), ('Licence');

INSERT INTO  profile_education_degree (eduid, degreeid)
     SELECT  a.id, d.id
       FROM  applis_def AS a
 INNER JOIN  profile_education_degree_enum AS d ON (FIND_IN_SET(d.degree, a.type));

INSERT INTO  profile_education_enum (id, name, url)
     SELECT  id, text, url
       FROM  applis_def;

INSERT INTO  profile_education (id, uid, eduid, degreeid)
     SELECT  a.ordre, a.uid, a.aid, d.id
       FROM  applis_ins AS a
 INNER JOIN  profile_education_degree_enum AS d ON (a.type = d.degree);


# vim:set syntax=mysql:

