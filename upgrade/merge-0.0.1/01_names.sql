CREATE OR REPLACE ALGORITHM=MERGE VIEW fusionax_xorg_anciens AS-
    SELECT
        u.user_id, u.matricule_ax, u.promo,
        n.display AS display_name, n.sort AS sort_name,
        u.nom, u.prenom
    FROM `auth_user_md5` AS u
    INNER JOIN `profile_names_display` AS n ON ( n.user_id = u.user_id );

-- vim:set syntax=mysql:
