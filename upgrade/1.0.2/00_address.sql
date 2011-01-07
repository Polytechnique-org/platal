ALTER TABLE profile_addresses MODIFY COLUMN flags SET('current','temporary','secondary','mail','cedex', 'deliveryIssue') DEFAULT NULL;

-- vim:set syntax=mysql:
