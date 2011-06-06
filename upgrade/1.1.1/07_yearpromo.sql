ALTER TABLE profile_education ADD COLUMN promo_year INT(4) DEFAULT NULL AFTER grad_year;

    UPDATE  profile_education AS pe
INNER JOIN  profile_display   AS pd ON (pd.pid = pe.pid)
       SET  pe.promo_year = REPLACE(pd.promo, 'X', '')
     WHERE  pe.degreeid = 2 AND FIND_IN_SET('primary', pe.flags);

    UPDATE  profile_education AS pe
INNER JOIN  profile_display   AS pd ON (pd.pid = pe.pid)
       SET  pe.promo_year = REPLACE(pd.promo, 'M', '')
     WHERE  pe.degreeid = 33 AND FIND_IN_SET('primary', pe.flags);

    UPDATE  profile_education AS pe
INNER JOIN  profiles          AS p ON (p.pid = pe.pid)
       SET  pe.promo_year = REPLACE(SUBSTRING_INDEX(p.hrpid, '.', -1), 'd', '')
     WHERE  pe.degreeid = 5 AND FIND_IN_SET('primary', pe.flags);

-- vim:set syntax=mysql:
