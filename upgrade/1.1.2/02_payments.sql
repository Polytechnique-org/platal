ALTER TABLE payment_transactions ADD method_id INTEGER DEFAULT NULL AFTER id; # NULL if not initiated from the site
ALTER TABLE payment_transactions CHANGE timestamp ts_confirmed DATETIME DEFAULT NULL; # NULL = not confirmed
ALTER TABLE payment_transactions ADD ts_initiated DATETIME DEFAULT NULL AFTER ts_confirmed; # NULL = not initiated
ALTER TABLE payment_transactions CHANGE amount amount_tmp VARCHAR(15);
ALTER TABLE payment_transactions ADD amount DECIMAL(9,2) NOT NULL AFTER amount_tmp; # only local currency allowed (EUR)
ALTER TABLE payment_transactions ADD commission DECIMAL(9,2) DEFAULT NULL AFTER amount;
ALTER TABLE payment_transactions ADD status ENUM('confirmed','pending','canceled') NOT NULL DEFAULT 'pending';
ALTER TABLE payment_transactions ADD recon_id INTEGER DEFAULT NULL; # NULL = not reconciliated
UPDATE payment_transactions SET method_id = 0 WHERE length(id)=7;
UPDATE payment_transactions SET method_id = 1 WHERE length(id)=15 OR length(id)=17;
UPDATE payment_transactions SET method_id = 2 WHERE length(id)=14;
UPDATE payment_transactions SET status = 'confirmed';
UPDATE payment_transactions SET amount=CONVERT(REPLACE(REPLACE(amount_tmp," EUR",""),",","."),DECIMAL(9,2));
ALTER TABLE payment_transactions ADD KEY method_id (method_id);
ALTER TABLE payment_transactions ADD KEY ref (ref);
# ALTER TABLE payment_transactions ADD UNIQUE KEY fullref (fullref);
#fullref dupliqués :
#select t1.* from payment_transactions as t1 join payment_transactions as t2 using(fullref) group by(t1.id) having count(*)!=1 order by fullref;
ALTER TABLE payment_transactions DROP amount_tmp;

ALTER TABLE payment_transactions ADD COLUMN display BOOL NOT NULL DEFAULT FALSE;
ALTER TABLE payments MODIFY COLUMN flags SET('unique', 'old', 'donation') NOT NULL DEFAULT '';

-- vim:set syntax=mysql:

