INSERT INTO email_virtual_domains (name) VALUES ("alumni.polytechnique.org");
UPDATE email_virtual_domains SET aliasing=id WHERE id=LAST_INSERT_ID();

SET @alumni_domain_id = 0;
SELECT @alumni_domain_id := id FROM email_virtual_domains WHERE name="alumni.polytechnique.org" and aliasing=id;

INSERT INTO email_virtual_domains (name, aliasing) VALUES ("alumni.m4x.org", @alumni_domain_id);

-- vim:set syntax=mysql:
