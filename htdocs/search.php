<?php
require("auto.prepend.inc.php");
require("search.classes.inc.php");
$perpage = 10;
// choix entre partie publique (annuaire_public est vrai) et partie privée de l'annuaire.
$public_directory = ((isset($_REQUEST['public_directory']) && $_REQUEST['public_directory']==1));
if ($public_directory)
    new_skinned_page('search.tpl', AUTH_PUBLIC);
else
    new_skinned_page('search.tpl', AUTH_COOKIE);
$page->assign('public_directory',$public_directory);
if (array_key_exists('rechercher', $_REQUEST)) {
    $page->assign('formulaire',0);
    $nameField = new StringSField('name',array('u.nom','u.epouse','i.nom'),'i.nom');
    $firstnameField = new StringSField('firstname',array('u.prenom','i.prenom'),'i.prenom');
    $promoField = new PromoSField('promo','egal',array('u.promo','i.promo'),'i.promo');
    $fields = new SFieldGroup(true,array($nameField,$firstnameField,$promoField));
    $offset = new NumericSField('offset');
    $sql = 'SELECT SQL_CALC_FOUND_ROWS 
                    u.nom!="" AS inscrit,
                    IF(u.nom!="",u.nom,i.nom) AS nom,
                    u.epouse,
                    IF(u.prenom!="",u.prenom,i.prenom) AS prenom,
                    IF(u.promo!="",u.promo,i.promo) AS promo,
                    i.deces!=0 AS decede,
                    u.username,
                    c.uid AS contact
            FROM identification AS i
            LEFT JOIN auth_user_md5 AS u ON (i.matricule=u.matricule)
            LEFT JOIN contacts AS c ON (c.uid = '.$_SESSION['uid'].' AND c.contact=u.user_id)
            WHERE '.$fields->get_where_statement().'
            ORDER BY '.implode(',',array_filter(array($fields->get_order_statement(),
            'promo DESC,nom,prenom'))).'
            LIMIT '.$offset->value.','.$perpage;
    $page->mysql_assign($sql, 'resultats', 'nb_resultats','nb_resultats_total');
    $page->assign('url_args',$fields->get_url());
    $page->assign('offset',$offset->value);
    $page->assign('perpage',$perpage);
}
else
    $page->assign('formulaire',1);
$page->run();
?>
