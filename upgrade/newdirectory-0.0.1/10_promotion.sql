INSERT INTO  profile_education (id, uid, grad_year, entry_year, eduid, degreeid, flags)
     SELECT  100, u.user_id, u.promo_sortie, u.promo, e.id, d.id, 'primary'
       FROM  #x4dat#.auth_user_md5                 AS u
  LEFT JOIN  profile_education_enum        AS e ON (e.abbreviation = "X")
  LEFT JOIN  profile_education_degree_enum AS d ON (d.degree = "Ingénieur");

    UPDATE  profile_display   AS d
INNER JOIN  profile_education AS e ON (d.pid = e.uid)
       SET  d.promo = CONCAT("X", entry_year)
     WHERE  FIND_IN_SET('primary', e.flags);

-- vim:set syntax=mysql:
