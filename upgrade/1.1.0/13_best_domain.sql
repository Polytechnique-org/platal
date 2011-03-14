ALTER TABLE accounts ADD COLUMN best_domain SMALLINT(3) UNSIGNED DEFAULT NULL;
ALTER TABLE accounts ADD FOREIGN KEY (best_domain) REFERENCES email_virtual_domains (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- vim:set syntax=mysql:
