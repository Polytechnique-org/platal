INSERT INTO email_virtual_domains (name) VALUES ("grad.polytechnique.org");
UPDATE email_virtual_domains SET aliasing=id WHERE name="grad.polytechnique.org";

SET @grad_domain_id = 0;
SELECT @grad_domain_id := id FROM email_virtual_domains WHERE name="grad.polytechnique.org";

INSERT INTO email_virtual_domains (name, aliasing) VALUES
    ("grad.m4x.org", @grad_domain_id),
    ('alumni.polytechnique.org', @grad_domain_id),
    ('alumni.m4x.org', @grad_domain_id);

-- vim:set syntax=mysql:
