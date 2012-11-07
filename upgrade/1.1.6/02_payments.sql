ALTER TABLE payments ADD COLUMN rib_id INT(11) NULL DEFAULT NULL ;
ALTER TABLE payments ADD CONSTRAINT fk_rib_id FOREIGN KEY (rib_id) REFERENCES payment_bankaccounts(id);

-- vim=set syntax=mysql:
