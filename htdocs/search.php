<?php
require("auto.prepend.inc.php");
require("search.classes.inc.php");

new_skinned_page('search.tpl', AUTH_PUBLIC);
if(logged()) {
    new_skinned_page('search.tpl', AUTH_COOKIE,true);
}

$page->assign('advanced',0);
require_once("applis.func.inc.php");
require_once("geoloc.inc.php");

if (array_key_exists('rechercher', $_REQUEST)) {
    $page->assign('formulaire',0);

    $with_soundex = ((isset($_REQUEST['with_soundex']) && $_REQUEST['with_soundex']==1));

    if ($with_soundex) {
        $nameField = new
        StringWithSoundexSField('name',array('r.nom1_soundex','r.nom2_soundex','r.nom3_soundex'),'');
        $firstnameField = new
        StringWithSoundexSField('firstname',array('r.prenom1_soundex','r.prenom2_soundex'),'');
    }
    else {
        $nameField = new StringSField('name',array('r.nom1','r.nom2','r.nom3'),'r.nom1');
        $firstnameField = new StringSField('firstname',array('r.prenom1','r.prenom2'),'r.prenom1');
        $with_soundex = ($nameField->length()==0 && $firstnameField->length()==0)?(-1):0;
    }
    $promo1Field = new PromoSField('promo1','egal1',array('r.promo'),'');
    $promo2Field = new PromoSField('promo2','egal2',array('r.promo'),'');
    $fields = new SFieldGroup(true,array($nameField,$firstnameField,$promo1Field,$promo2Field));
    
    if ($nameField->length()<2 && $firstnameField->length()<2 && 
        ($public_directory || !$promo1Field->is_a_single_promo()))
    {
	new ThrowError('Recherche trop générale.');
    }
    $offset = new NumericSField('offset');
    
    $sql = 'SELECT SQL_CALC_FOUND_ROWS
                       r.matricule,i.matricule_ax,
                       u.nom!="" AS inscrit,
                       IF(u.nom!="",u.nom,i.nom) AS nom,
                       u.epouse,
                       IF(u.prenom!="",u.prenom,i.prenom) AS prenom,
                       IF(u.promo!="",u.promo,i.promo) AS promo,
                       i.deces!=0 AS decede,
                       u.username,
                       u.date,
                       ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
                       ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
                       c.uid AS contact
                 FROM  '.(($with_soundex)?'recherche_soundex':'recherche').'      AS r
           INNER JOIN  identification AS i ON (i.matricule=r.matricule)
            LEFT JOIN  auth_user_md5  AS u ON (u.matricule=r.matricule)
            LEFT JOIN  contacts       AS c ON (c.uid='.((array_key_exists('uid',$_SESSION))?$_SESSION['uid']:0).' AND c.contact=u.user_id)
            LEFT  JOIN applis_ins     AS ai0 ON (u.user_id = ai0.uid AND ai0.ordre = 0)
            LEFT  JOIN applis_def     AS ad0 ON (ad0.id = ai0.aid)
            LEFT  JOIN applis_ins     AS ai1 ON (u.user_id = ai1.uid AND ai1.ordre = 1)
            LEFT  JOIN applis_def     AS ad1 ON (ad1.id = ai1.aid)
                WHERE  '.$fields->get_where_statement().'
             ORDER BY  '.implode(',',array_filter(array($fields->get_order_statement(),'promo DESC,nom,prenom'))).'
                LIMIT  '.$offset->value.','.$globals->search_results_per_page;

    $page->mysql_assign($sql, 'resultats', 'nb_resultats','nb_resultats_total');
    
    if ($public_directory &&
	$page->get_template_vars('nb_resultats_total')>$globals->public_max_search_results)
    {
	new ThrowError('Votre recherche a généré trop de résultats pour un affichage public.');
    }
    $nbpages = ($page->get_template_vars('nb_resultats_total')-1)/$globals->search_results_per_page;
    $page->assign('offsets',range(0,$nbpages));
    $page->assign('url_args',$fields->get_url());
    $page->assign('with_soundex',$with_soundex);
    $page->assign('offset',$offset->value);
    $page->assign('perpage',$globals->search_results_per_page);
    $page->assign('is_admin',has_perms());
}
else
    $page->assign('formulaire',1);
$page->run();
?>
