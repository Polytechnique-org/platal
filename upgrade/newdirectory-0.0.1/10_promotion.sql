INSERT INTO  profile_education (id, uid, grad_year, entry_year, eduid, degreeid, flags)
     SELECT  100, u.user_id, u.promo_sortie, u.promo, e.id, d.id, 'primary'
       FROM  auth_user_md5                 AS u
  LEFT JOIN  profile_education_enum        AS e ON (e.abbreviation = "X")
  LEFT JOIN  profile_education_degree_enum AS d ON (d.degree = "Ingénieur");

REPLACE IGNORE INTO  profile_display (pid, promo)
             SELECT  uid, CONCAT("X", entry_year)
               FROM  profile_education
              WHERE  FIND_IN_SET('primary', flags);

-- vim:set syntax=mysql:
