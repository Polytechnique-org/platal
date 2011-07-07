UPDATE  email_source_account
   SET  flags = REPLACE(flags, 'marital', 'usage')
 WHERE  FIND_IN_SET('marital', flags);

-- vim:set syntax=mysql:
