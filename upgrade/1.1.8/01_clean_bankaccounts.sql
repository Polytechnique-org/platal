UPDATE payments SET rib_id = NULL;
UPDATE payment_transfers SET account_id = NULL
DELETE FROM payment_bankaccounts;
ALTER TABLE payment_bankaccounts CHANGE account iban varchar(33) NOT NULL DEFAULT 'FRkk BBBB BGGG GGCC CCCC CCCC CKK';
ALTER TABLE payment_bankaccounts ADD COLUMN bic varchar(11) NOT NULL DEFAULT 'XXXXXXXXXXX' AFTER iban;
INSERT INTO payment_bankaccounts (id, asso_id, iban, bic, owner, status) VALUES (1, 53, DEFAULT, DEFAULT, 'RIB inconnu', 'new');
UPDATE payments SET rib_id = 1;
ALTER TABLE payments CHANGE rib_id rib_id int(11) NOT NULL DEFAULT 1;

-- vim=set syntax=mysql
