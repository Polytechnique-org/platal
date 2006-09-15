use groupex
alter table evenements drop column advertise;
alter table evenements drop column membres_only;
alter table membres add column sexe bool default null;

INSERT IGNORE INTO membres (uid, asso_id)
            SELECT u.user_id AS uid, g.id AS asso_id
              FROM x4dat.auth_user_md5 AS u
        INNER JOIN asso AS g ON (g.diminutif LIKE u.promo);
use x4dat

