CREATE TABLE IF NOT EXISTS alumni_data (
  uid INT(11) NOT NULL,
  matricule_ax INT(8) UNSIGNED NOT NULL,
  login_ax VARCHAR(15) NOT NULL,
  passwd_ax INT(11) UNSIGNED DEFAULT NULL,
  promo_type ENUM('', 'A', 'B', 'C', 'N', 'S') NOT NULL,
  membership_type ENUM('', '*', 'F', 'FB', 'P', 'PB', 'T', 'TB', 'TA') NOT NULL,
  salutation ENUM('', '.', 'M', 'MME', 'MLLE') NOT NULL,
  last_dues_paid_year INT(4) UNSIGNED NOT NULL,
  represents ENUM('', 'K', 'DE') NOT NULL,
  checked TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (uid)
) CHARSET=utf8;


INSERT INTO  alumni_data (uid, matricule_ax, login_ax, passwd_ax, promo_type, membership_type,
             salutation, last_dues_paid_year, represents, checked)
     SELECT  u.user_id, CONVERT(u.matricule_ax, UNSIGNED), f.Login, f.Password, f.Groupe_promo,
             f.Type_membre, f.Civilite, f.annee_dernCot, f.Representant, 0
       FROM  fusionax_anciens AS f
 INNER JOIN  auth_user_md5    AS u ON (f.id_ancien = u.matricule_ax);

-- vim:set syntax=mysql:
