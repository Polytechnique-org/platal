INSERT IGNORE INTO  profile_corps (uid, original_corpsid, current_corpsid, rankid, corps_pub)
            SELECT  u.user_id, c.id, c.id, r.id, 'ax'
              FROM  auth_user_md5           AS u
        INNER JOIN  fusionax_anciens        AS f ON (u.matricule_ax = f.id_ancien)
        INNER JOIN  profile_corps_enum      AS c ON (f.corps_sortie = c.abbreviation)
        INNER JOIN  profile_corps_rank_enum AS r ON (f.grade = r.abbreviation);

    UPDATE  profile_corps      AS c
INNER JOIN  profile_corps_enum AS e ON (c.original_corpsid = e.id)
 LEFT JOIN  profile_corps_enum AS a ON (a.name = "Aucun (anc. démissionnaire)")
       SET  c.original_corpsid = a.id
     WHERE  e.name = "Ancien élève étranger";

UPDATE  profile_corps_enum
   SET  name = "Aucun"
 WHERE  name = "Aucun (anc. démissionnaire)";

DELETE FROM  profile_corps_enum
      WHERE  name = "Ancien élève étranger";

--  vim:set syntax=mysql:
