USE x4dat;
DROP TABLE IF EXISTS recherche;
CREATE TABLE recherche SELECT matricule AS matricule,LOWER(REPLACE(nom_ini,'-',' ')) AS nom1,
LOWER(REPLACE(nom,'-',' ')) AS nom2, LOWER(REPLACE(nom_usage,'-',' ')) AS nom3,
LOWER(REPLACE(prenom_ini,'-',' ')) AS prenom1, LOWER(REPLACE(prenom,'-',' ')) AS prenom2, promo AS
promo FROM auth_user_md5;
DROP TABLE IF EXISTS recherche_soundex;
CREATE TABLE recherche_soundex (matricule int(8) unsigned PRIMARY KEY,nom1_soundex
char(4),nom2_soundex char(4),nom3_soundex char(4),prenom1_soundex char(4),prenom2_soundex char(4),
promo smallint(4) unsigned);
