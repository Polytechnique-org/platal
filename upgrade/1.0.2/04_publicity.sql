    UPDATE  profile_phones    AS pp
INNER JOIN  profile_addresses AS pa ON (pp.pid = pa.pid AND pp.link_id = pa.id AND pa.type = 'home' AND pp.tel_type = 'address')
       SET  pp.pub = 'ax'
     WHERE  pp.pub != pa.pub AND pa.pub = 'ax' AND pp.pub = 'public';
    UPDATE  profile_phones    AS pp
INNER JOIN  profile_addresses AS pa ON (pp.pid = pa.pid AND pp.link_id = pa.id AND pa.type = 'home' AND pp.tel_type = 'address')
       SET pp.pub = 'private' WHERE pp.pub != pa.pub AND pa.pub = 'private';

    UPDATE  profile_phones AS pp
INNER JOIN  profile_job    AS pj ON (pp.pid = pj.pid AND pp.link_id = pj.id AND pp.link_type = 'pro')
       SET  pp.pub = 'ax'
     WHERE  pp.pub != pj.pub AND  pj.pub = 'ax' AND pp.pub = 'public';
    UPDATE  profile_phones AS pp
INNER JOIN  profile_job    AS pj ON (pp.pid = pj.pid AND pp.link_id = pj.id AND pp.link_type = 'pro')
       SET  pp.pub = 'private'
     WHERE  pp.pub != pj.pub AND pj.pub = 'private';

    UPDATE  profile_addresses AS pa
INNER JOIN  profile_job       AS pj ON (pa.pid = pj.pid AND pa.id = pj.id AND pa.type = 'job')
       SET  pa.pub = 'ax'
     WHERE  pa.pub != pj.pub AND pj.pub = 'ax' AND pa.pub = 'public';
    UPDATE  profile_addresses AS pa
INNER JOIN  profile_job       AS pj ON (pa.pid = pj.pid AND pa.id = pj.id AND pa.type = 'job')
       SET  pa.pub = 'private'
     WHERE  pa.pub != pj.pub AND pj.pub = 'private';

UPDATE  profile_job
   SET  email_pub = 'ax'
 WHERE  pub != email_pub AND pub = 'ax' AND email_pub = 'public';
UPDATE  profile_job
   SET  email_pub = 'private'
 WHERE pub = 'private';

-- vim:set syntax=mysql:
