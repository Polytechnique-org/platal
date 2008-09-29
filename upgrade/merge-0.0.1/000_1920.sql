set @n = 19200001;

INSERT INTO  auth_user_md5 (matricule, matricule_ax, promo, promo_sortie, nom_ini, prenom_ini, perms, nom, prenom)
     SELECT  @n := @n + 1, id_ancien, promotion_etude, promotion_etude + 3, CONCAT(partic_patro, Nom_patronymique), prenom,
             'pending', CONCAT(partic_patro, Nom_patronymique), prenom
       FROM  fusionax_anciens
      WHERE  promotion_etude = 1920;

-- vim:set syntax=mysql:
