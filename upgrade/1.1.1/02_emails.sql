    DELETE  e2
      FROM  email_virtual AS e1
INNER JOIN  email_virtual AS e2
     WHERE  e1.email = e2.email AND e1.domain = e2.domain AND e1.redirect != e2.redirect
            AND e1.redirect = REPLACE(e2.redirect, '@m4x.org', '@polytechnique.org');
UPDATE  email_virtual
   SET  redirect = REPLACE(redirect, '@m4x.org', '@polytechnique.org')
 WHERE  redirect LIKE '%@m4x.org';

-- vim:set syntax=mysql:
