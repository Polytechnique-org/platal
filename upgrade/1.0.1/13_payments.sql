DROP TABLE IF EXISTS payment_bankaccounts;
CREATE TABLE payment_bankaccounts (
  id integer PRIMARY KEY auto_increment,
  asso_id integer NOT NULL,
  account varchar(23) NOT NULL,
  owner varchar(100) NOT NULL,
  status set('new','used','old') NOT NULL default 'new'
) ENGINE=InnoDB, CHARSET=utf8;

#INSERT INTO payment_bankaccounts VALUES (NULL,,"30002004690000008524R29","Amis de l'Espace Dirigea","used");
INSERT INTO payment_bankaccounts VALUES (NULL,239,"10207001172019602580784","ASCCX","used");
INSERT INTO payment_bankaccounts VALUES (NULL,42,"14707000010892101692291","AX (BPLC)","used");
INSERT INTO payment_bankaccounts VALUES (NULL,42,"30066109310001022770164","AX (CIC)","used");
INSERT INTO payment_bankaccounts VALUES (NULL,42,"30002004200000009372U74","AX (LCL)","used");
INSERT INTO payment_bankaccounts VALUES (NULL,31,"10107001820002105034095","Binet Point Gamma","used");
INSERT INTO payment_bankaccounts VALUES (NULL,73,"30003020600003729601589","GTX","used");
INSERT INTO payment_bankaccounts VALUES (NULL,246,"20041000012241609M02035","Humanix - Jacques Bellev","used");
#INSERT INTO payment_bankaccounts VALUES (NULL,,"10107001820092105033751","Kes des élèves","used");
INSERT INTO payment_bankaccounts VALUES (NULL,214,"30003022160005198020072","Khomiss (Aurélien Lajoie","used");
#INSERT INTO payment_bankaccounts VALUES (NULL,,"30003021900002011521283","Maison des X","used");
INSERT INTO payment_bankaccounts VALUES (NULL,181,"10107001820012105055968","Raid Polytechnique 2004","used");
INSERT INTO payment_bankaccounts VALUES (NULL,165,"20041010123576371A03369","Sabix","used");
INSERT INTO payment_bankaccounts VALUES (NULL,11,"30002089410000023857R03","X-eConfiance 'Mathieu Be","used");
INSERT INTO payment_bankaccounts VALUES (NULL,251,"30003022200005041343575","X-Achats 'Francois Rena","used");
INSERT INTO payment_bankaccounts VALUES (NULL,252,"30002008190000045217G86","X-Automobile 'F. Tronel","used");
INSERT INTO payment_bankaccounts VALUES (NULL,6,"30002005940000434521B52","X-Aviation 'Francis Fouq","used");
INSERT INTO payment_bankaccounts VALUES (NULL,96,"30003041110003726598647","X-Biotech (M.O.Bevierre)","used");
INSERT INTO payment_bankaccounts VALUES (NULL,57,"15589335720697076254012","X-Bordelais (T Leblond)","used");
INSERT INTO payment_bankaccounts VALUES (NULL,4,"30003005080003728293253","X-Consult","used");
INSERT INTO payment_bankaccounts VALUES (NULL,18,"30066100210001067980188","X-Environnement (P Worbe","used");
INSERT INTO payment_bankaccounts VALUES (NULL,3,"30003031900005066357935","X-Finance - Ariane Chaze","used");
INSERT INTO payment_bankaccounts VALUES (NULL,7,"30002004200000009372U74","X-Gaziers - Compte AX LC","used");
INSERT INTO payment_bankaccounts VALUES (NULL,21,"30588610978071800010189","X-Golf (Guy Marchand)","used");
INSERT INTO payment_bankaccounts VALUES (NULL,202,"30003034210005003887246","X-HEC CapInvest (A Santo","used");
INSERT INTO payment_bankaccounts VALUES (NULL,174,"30002006840000005831S15","X-Mer","used");
INSERT INTO payment_bankaccounts VALUES (NULL,166,"30066108700001028630170","X-Mines au Feminin","used");
INSERT INTO payment_bankaccounts VALUES (NULL,219,"30002004200000009372U74","X-Nucleaire - Compte AX","used");
INSERT INTO payment_bankaccounts VALUES (NULL,82,"30003038320005055982303","X-Pierre (Quoc-Giao Tran","used");
INSERT INTO payment_bankaccounts VALUES (NULL,233,"30002004200000009372U74","X-PI - Compte AX LCL","used");
INSERT INTO payment_bankaccounts VALUES (NULL,248,"12548029983443030151039","X-Renouvelables 'Jerome","used");
INSERT INTO payment_bankaccounts VALUES (NULL,179,"30066106410001050600128","X-Sursaut H Levy-Lambert","used");
INSERT INTO payment_bankaccounts VALUES (NULL,223,"30066100410001126780124","X-Theatre","used");

DROP TABLE IF EXISTS payment_reconcilations;
CREATE TABLE payment_reconcilations (
  id INTEGER PRIMARY KEY auto_increment,
  method_id INTEGER NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  recongroup_id INTEGER DEFAULT NULL,
  status ENUM('pending','transfering','closed') NOT NULL DEFAULT 'pending',
  payment_count INTEGER NOT NULL,
  sum_amounts DECIMAL(9,2) NOT NULL, # transaction amount, before taking the commission
  sum_commissions DECIMAL(9,2) NOT NULL,
  comments text NOT NULL
) ENGINE=InnoDB, CHARSET=utf8;

DROP TABLE IF EXISTS payment_transfers;
CREATE TABLE payment_transfers (
  id INTEGER PRIMARY KEY auto_increment,
  recongroup_id INTEGER NOT NULL,
  payment_id INTEGER NOT NULL,
  amount DECIMAL(9,2) NOT NULL,
  account_id INTEGER DEFAULT NULL,
  message VARCHAR(255) NOT NULL,
  date DATE DEFAULT NULL # NULL = not done
) ENGINE=InnoDB, CHARSET=utf8;

ALTER TABLE payment_methods ADD short_name VARCHAR(10) NOT NULL;
ALTER TABLE payment_methods ADD flags SET('deferred_com') DEFAULT '';
UPDATE payment_methods SET short_name='paypal', flags='' WHERE id=1;
UPDATE payment_methods SET short_name='bplc2', flags='deferred_com' WHERE id=2;

-- vim:set syntax=mysql:

