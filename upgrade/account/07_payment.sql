CREATE TABLE  payment_codeC
        LIKE  paiement.codeC;
 INSERT INTO  payment_codeC
      SELECT  *
        FROM  paiement.codeC;

CREATE TABLE  payment_codeRCB
        LIKE  paiement.codeRCB;
 INSERT INTO  payment_codeRCB
      SELECT  *
        FROM  paiement.codeRCB;

CREATE TABLE  payment_methods
        LIKE  paiement.methodes;
 INSERT INTO  payment_methods
      SELECT  *
        FROM  paiement.methodes;

CREATE TABLE  payments
        LIKE  paiement.paiements;
 INSERT INTO  payments
      SELECT  *
        FROM  paiement.paiements;

CREATE TABLE  payment_transactions
        LIKE  paiement.transactions;
 INSERT INTO  payment_transactions
      SELECT  *
        FROM  paiement.transactions;

# Conform to naming convention
  ALTER TABLE  payments
CHANGE COLUMN  montant_def amount_def DECIMAL(10,2) NOT NULL DEFAULT 0.00,
CHANGE COLUMN  montant_min amount_min DECIMAL(10,2) NOT NULL DEFAULT 0.00,
CHANGE COLUMN  montant_max amount_max DECIMAL(10,2) NOT NULL DEFAULT 0.00;

  ALTER TABLE  payment_transactions
CHANGE COLUMN  montant amount VARCHAR(15) NOT NULL DEFAULT '0.00',
CHANGE COLUMN  cle pkey VARCHAR(5) NOT NULL;

# vim:set ft=mysql:
