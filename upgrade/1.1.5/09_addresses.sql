ALTER TABLE profile_addresses MODIFY COLUMN flags SET('current','temporary','secondary','mail','cedex','deliveryIssue', 'ax_mail') DEFAULT NULL;

-- vim:set syntax=mysql:
