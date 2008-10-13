INSERT INTO  profile_education (id, uid, grad_year, entry_year, eduid, degreeid, flags)
     SELECT  100, user_id, promo_sortie, promo, 20, 2, 'primary'
       FROM  auth_user_md5;

CREATE TABLE  profile_display (
  uid INT(11) NOT NULL DEFAULT 0,
  promo_display VARCHAR(255) DEFAULT '' NOT NULL,
  PRIMARY KEY(uid)
) CHARSET=utf8;

INSERT IGNORE INTO  profile_display (uid, promo_display)
            SELECT  uid, CONCAT("X", entry_year)
              FROM  profile_education
             WHERE  FIND_IN_SET('primary', flags);

-- vim:set syntax=mysql:
