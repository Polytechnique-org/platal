    DELETE  w.*
      FROM  watch_nonins  AS w
INNER JOIN  auth_user_md5 AS a ON (w.uid = a.user_id OR w.ni_id = a.user_id)
     WHERE  a.deces != '0000-00-00';

-- vim:set syntax=mysql:
