-- Rename ax_mail flag to dn_best_mail

ALTER TABLE profile_addresses MODIFY COLUMN flags SET('current', 'temporary', 'secondary', 'mail', 'cedex', 'deliveryIssue', 'ax_mail', 'dn_best_mail') DEFAULT NULL;

UPDATE profile_addresses SET flags=REPLACE(flags, 'ax_mail', 'dn_best_mail') WHERE FIND_IN_SET('ax_mail', flags);

ALTER TABLE profile_addresses MODIFY COLUMN flags SET('current', 'temporary', 'secondary', 'mail', 'cedex', 'deliveryIssue', 'dn_best_mail') DEFAULT NULL;

-- vim:set syntax=mysql:
