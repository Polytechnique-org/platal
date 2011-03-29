-- Updates serbian iso code.
UPDATE  profile_langskills
   SET  lid = 'srp'
 WHERE  lid = 'scc';
DELETE FROM  profile_langskill_enum
      WHERE  iso_639_2b = 'scc';

-- vim:set syntax=mysql:
