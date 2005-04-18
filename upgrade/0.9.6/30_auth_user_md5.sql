ALTER TABLE auth_user_md5 ADD promo_sortie SMALLINT(4) UNSIGNED NOT NULL AFTER promo;
UPDATE auth_user_md5 SET promo_sortie = promo + 3;

