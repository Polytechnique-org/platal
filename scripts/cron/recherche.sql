USE x4dat;
DROP TABLE recherche;
CREATE TABLE recherche SELECT i.matricule AS matricule,LOWER(REPLACE(i.nom,'-',' ')) AS nom1,
LOWER(REPLACE(u.nom,'-',' ')) AS nom2, LOWER(REPLACE(u.epouse,'-',' ')) AS nom3,
LOWER(REPLACE(i.prenom,'-',' ')) AS prenom1, LOWER(REPLACE(u.prenom,'-',' ')) AS prenom2,i.promo AS
promo FROM identification AS i LEFT JOIN auth_user_md5 AS u ON (u.matricule=i.matricule);
