DELETE FROM  email_redirect_account
      WHERE  type IN ('googleapps', 'imap') AND flags = 'inactive';

-- vim:set syntax=mysql:
