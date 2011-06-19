INSERT INTO  profile_job_term_enum (name, full_name)
     VALUES  ('Hedge Fund', 'Hedge Fund (secteur)');

SET @jtid = 0;
SELECT  @jtid := jtid
  FROM  profile_job_term_enum
 WHERE  name = 'Hedge Fund';

INSERT INTO  profile_job_term_search (search, jtid)
     VALUES  ('HEDGE', @jtid), ('FUND', @jtid);
INSERT INTO  profile_job_term_relation (jtid_1, jtid_2, rel, computed)
     VALUES  (697, @jtid, 'narrower', 'computed'), (735, @jtid, 'narrower', 'original'), (@jtid, @jtid, 'narrower', 'computed');

-- vim:set syntax=mysql:
