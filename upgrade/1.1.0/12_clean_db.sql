-- Deletes lost addresses, medals and phones.
DELETE  a
  FROM  profile_addresses AS a
 WHERE  a.type = 'home' AND NOT EXISTS (SELECT  *
                                          FROM  profiles AS p
                                         WHERE  a.pid = p.pid);
DELETE  a
  FROM  profile_addresses AS a
 WHERE  a.type = 'job' AND NOT EXISTS (SELECT  *
                                         FROM  profile_job AS j
                                        WHERE  a.pid = j.pid AND a.id = j.id);
DELETE  a
  FROM  profile_addresses AS a
 WHERE  a.type = 'hq' AND NOT EXISTS (SELECT  *
                                        FROM  profile_job_enum AS je
                                       WHERE  a.jobid = je.id);
DELETE  a
  FROM  profile_addresses AS a
 WHERE  a.type = 'group' AND NOT EXISTS (SELECT  *
                                           FROM  groups AS g
                                          WHERE  a.groupid = g.id);
DELETE  m
  FROM  profile_medals AS m
 WHERE  m.gid != 0 AND NOT EXISTS (SELECT  *
                                     FROM  profile_medal_grade_enum AS mg
                                    WHERE  m.mid = mg.mid AND m.gid = mg.gid);
DELETE  p
  FROM  profile_phones AS p
 WHERE  p.link_type = 'address' AND NOT EXISTS (SELECT  *
                                                  FROM  profile_addresses AS a
                                                 WHERE  p.pid = a.pid AND p.link_id = a.id);
DELETE  p
  FROM  profile_phones AS p
 WHERE  p.link_type = 'pro' AND NOT EXISTS (SELECT  *
                                              FROM  profile_job AS j
                                             WHERE  p.pid = j.pid AND p.link_id = j.id);
DELETE  p
  FROM  profile_phones AS p
 WHERE  p.link_type = 'user' AND NOT EXISTS (SELECT  *
                                               FROM  profiles AS pf
                                              WHERE  p.pid = pf.pid);
DELETE  p
  FROM  profile_phones AS p
 WHERE  p.link_type = 'hq' AND NOT EXISTS (SELECT  *
                                             FROM  profile_job_enum AS je
                                            WHERE  p.link_id = je.id);
DELETE  p
  FROM  profile_phones AS p
 WHERE  p.link_type = 'group' AND NOT EXISTS (SELECT  *
                                                FROM  groups AS g
                                               WHERE  p.link_id = g.id);

-- vim:set syntax=mysql:
