ALTER TABLE contacts DROP FOREIGN KEY contacts_ibfk_2;
DELETE FROM  contacts
      WHERE  NOT EXISTS (SELECT  profiles.*
                           FROM  profiles
                          WHERE  profiles.pid = contacts.contact);
ALTER TABLE contacts ADD FOREIGN KEY (contact) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE;

-- vim:set syntax=mysql:
