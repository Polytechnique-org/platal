<?php
require("auto.prepend.inc.php");
require("search.classes.inc.php");
// choix entre partie publique (annuaire_public est vrai) et partie privée de l'annuaire.
$public_directory = ((isset($_REQUEST['public_directory']) && $_REQUEST['public_directory']==1));
if ($public_directory)
    new_skinned_page('search.tpl', AUTH_PUBLIC);
else
    new_skinned_page('search.tpl', AUTH_COOKIE);
if (array_key_exists('rechercher', $_POST)) {
    $page->assign('formulaire',0);
    $nameField = new StringSField('name',array('u.nom','u.epouse'));
    $firstnameField = new StringSField('firstname',array('u.prenom'));
    $promoField = new PromoSField('promo','egal','u.promo');
    $fields = new SFieldGroup(true,array($nameField,$firstnameField,$promoField));
    $nameField = new StringSField('name',array('i.nom'));
    $firstnameField = new StringSField('firstname',array('i.prenom'));
    $promoField = new PromoSField('promo','egal','i.promo');
    $fields2 = new SFieldGroup(true,array($nameField,$firstnameField,$promoField));
    $sql = '(SELECT u.nom,u.prenom,u.promo 
            FROM auth_user_md5 AS u  
            WHERE '.$fields->get_where_statement().')
            UNION
            (SELECT i.nom,i.prenom,i.promo
            FROM identification AS i
            WHERE '.$fields2->get_where_statement().')
            ORDER BY promo DESC,nom,prenom';
    $page->mysql_assign($sql, 'resultats', 'nb_resultats');
}
else
    $page->assign('formulaire',1);
$page->run();
?>
