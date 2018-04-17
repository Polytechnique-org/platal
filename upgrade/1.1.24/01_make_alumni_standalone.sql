INSERT INTO email_virtual_domains (name) VALUES ("alumni.polytechnique.org");
UPDATE email_virtual_domains SET aliasing=id WHERE id=LAST_INSERT_ID();
INSERT INTO email_virtual_domains (name) VALUES ("alumni.m4x.org");
UPDATE email_virtual_domains SET aliasing=id WHERE id=LAST_INSERT_ID();


-- vim:set syntax=mysql:
