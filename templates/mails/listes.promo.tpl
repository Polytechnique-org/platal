{config_load file="mails.conf" section="listes_promo"}
{from full=#from#}
{to addr=#to#}
{subject text="Création de la liste promo $promo"}

Création de la liste promo {$promo} à faire !
