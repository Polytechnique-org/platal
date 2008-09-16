ALTER TABLE geoloc_pays ADD INDEX (license_plate);

   UPDATE  auth_user_md5    AS u
LEFT JOIN  fusionax_anciens AS f ON (u.matricule_ax = f.id_ancien)
LEFT JOIN  geoloc_pays      AS g ON (g.license_plate = f.Code_nationalite)
      SET  u.nationalite = g.a2
    WHERE  u.nationalite IS NULL;

   UPDATE  auth_user_md5    AS u
LEFT JOIN  fusionax_anciens AS f ON (u.matricule_ax = f.id_ancien)
LEFT JOIN  geoloc_pays      AS g ON (g.license_plate = f.Code_nationalite)
      SET  u.nationalite2 = g.a2
    WHERE  u.nationalite != g.a2 AND u.nationalite2 IS NULL;

   UPDATE  auth_user_md5    AS u
LEFT JOIN  fusionax_anciens AS f ON (u.matricule_ax = f.id_ancien)
LEFT JOIN  geoloc_pays      AS g ON (g.license_plate = f.Code_nationalite)
      SET  u.nationalite3 = g.a2
    WHERE  u.nationalite != g.a2 AND u.nationalite2 != g.a2 AND u.nationalite3 IS NULL;

ALTER TABLE geoloc_pays DROP INDEX (license_plate);

-- vim:set syntax=mysql:
