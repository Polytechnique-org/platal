-- Adds secondary education.

ALTER TABLE profile_education MODIFY COLUMN flags SET('primary', 'secondary', 'completed') NOT NULL DEFAULT '';

    UPDATE  profile_education      AS pe
INNER JOIN  profile_education_enum AS pen ON (pe.eduid = pen.id)
       SET  pe.flags = IF(pe.flags = '', 'secondary', 'secondary,completed')
     WHERE  pen.abbreviation = 'X' AND NOT FIND_IN_SET('primary', flags);

-- Updates secondary education details.

UPDATE  profile_education
   SET  degreeid = 33
 WHERE  FIND_IN_SET('secondary', flags) AND (degreeid = 4 OR degreeid = 6);

UPDATE  profile_education
   SET  degreeid = 5
 WHERE  FIND_IN_SET('secondary', flags) AND degreeid IS NULL;

UPDATE  profile_education
   SET  promo_year = grad_year
 WHERE  FIND_IN_SET('secondary', flags);

-- Removes duplicates.
    DELETE  e1
      FROM  profile_education AS e1
INNER JOIN  profile_education AS e2 ON (e1.pid = e2.pid AND e1.degreeid = e2.degreeid AND e1.id < e2.id
                                        AND FIND_IN_SET('secondary', e1.flags) AND FIND_IN_SET('secondary', e2.flags))
     WHERE  e1.fieldid IS NULL AND e1.program IS NULL;
    DELETE  e1
      FROM  profile_education AS e1
INNER JOIN  profile_education AS e2 ON (e1.pid = e2.pid AND e1.degreeid = e2.degreeid AND e1.id > e2.id
                                        AND FIND_IN_SET('secondary', e1.flags) AND FIND_IN_SET('secondary', e2.flags))
     WHERE  e1.fieldid IS NULL AND e1.program IS NULL;

-- Updates secondary education ids.
   UPDATE  profile_education AS e1
LEFT JOIN  profile_education AS e2 ON (e1.pid = e2.pid AND e1.degreeid = e2.degreeid AND e1.id != e2.id AND FIND_IN_SET('secondary', e2.flags))
      SET  e1.id = 101
    WHERE  FIND_IN_SET('secondary', e1.flags) AND e1.degreeid = 33 AND e2.pid IS NULL;

    UPDATE  profile_education AS e1
INNER JOIN  profile_education AS e2 ON (e1.pid = e2.pid AND e1.degreeid = e2.degreeid AND e1.id > e2.id)
       SET  e1.id = 101, e2.id = 102
     WHERE  FIND_IN_SET('secondary', e1.flags) AND FIND_IN_SET('secondary', e2.flags) AND e1.degreeid = 33;

   UPDATE  profile_education AS e1
LEFT JOIN  profile_education AS e2 ON (e1.pid = e2.pid AND e2.degreeid = 33 AND FIND_IN_SET('secondary', e2.flags))
      SET  e1.id = 101
    WHERE  FIND_IN_SET('secondary', e1.flags) AND e1.degreeid = 5 AND e2.pid IS NULL;

    UPDATE  profile_education AS e1
INNER JOIN  profile_education AS e2 ON (e1.pid = e2.pid AND e2.degreeid = 33)
       SET  e1.id = 102
     WHERE  FIND_IN_SET('secondary', e1.flags) AND FIND_IN_SET('secondary', e2.flags) AND e1.degreeid = 5;

-- vim:set syntax=mysql:
