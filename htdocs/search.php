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
    $nameField = new StringSField('name',array('nom','epouse'));
    $firstnameField = new StringSField('firstname',array('prenom'));
    $promoField = new PromoSField('promo','egal','promo');
    $fields = new SFieldGroup(true,array($nameField,$firstnameField,$promoField));
    $nameField = new StringSField('name',array('i.nom'));
    $firstnameField = new StringSField('firstname',array('i.prenom'));
    $promoField = new PromoSField('promo','egal','i.promo');
    $fields2 = new SFieldGroup(true,array($nameField,$firstnameField,$promoField));
    $offset = new NumericSField('offset');
    $sqli = '(SELECT matricule
            FROM auth_user_md5 
            WHERE '.$fields->get_where_statement().')';
    $result = mysql_query($sqli);
    while ($row = mysql_fetch_row($result))
        list($matricules[]) = $row;
    $sqln = '(SELECT matricule
            FROM auth_user_md5
            WHERE matricule IN ('.implode(',',$matricules).'))
            UNION
            (SELECT i.matricule
            FROM identification AS i
            WHERE i.matricule NOT IN ('.implode(',',$matricules).')
            AND '.$fields2->get_where_statement().')';
    $result = mysql_query($sqln);
    $page->assign('nb_resultats_total',mysql_num_rows($result));
    $sql = '(SELECT 1 AS inscrit,u.nom,u.epouse,u.prenom,u.promo,i.deces!=0 AS decede,u.username,
            c.uid AS contact
            FROM auth_user_md5 AS u
            INNER JOIN identification AS i ON (i.matricule=u.matricule)
            LEFT JOIN contacts AS c ON (c.uid = '.$_SESSION['uid'].' AND c.contact=u.user_id)
            WHERE u.matricule IN ('.implode(',',$matricules).'))
            UNION
            (SELECT 0 AS inscrit,i.nom,"",i.prenom,i.promo,i.deces!=0 AS decede,"","" AS contact
            FROM identification AS i
            WHERE i.matricule NOT IN ('.implode(',',$matricules).')
            AND '.$fields2->get_where_statement().')
            ORDER BY '.implode(',',array_filter(array($fields->get_order_statement(),
            'promo DESC,nom,prenom'))).'
            LIMIT '.$offset->value.','.$perpage;
    $page->mysql_assign($sql, 'resultats', 'nb_resultats');
    $page->assign('url_args',$fields->get_url());
    $page->assign('offset',$offset->value);
    $page->assign('perpage',$perpage);
}
else
    $page->assign('formulaire',1);
$page->run();
?>
