<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: search.php,v 1.35 2004-10-25 12:48:02 x2000habouzit Exp $
 ***************************************************************************/

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

    $with_soundex = !empty($_REQUEST['with_soundex']);

    if ($with_soundex) {
        $nameField = new
        StringWithSoundexSField('name',array('r.nom1_soundex','r.nom2_soundex','r.nom3_soundex'),'');
        $firstnameField = new
        StringWithSoundexSField('firstname',array('r.prenom1_soundex','r.prenom2_soundex'),'');
    }
    else {
        $nameField = new NameSField('name',array('r.nom1','r.nom2','r.nom3'),'r.nom1');
        $firstnameField = new StringSField('firstname',array('r.prenom1','r.prenom2'),'r.prenom1');
    }
    $promo1Field = new PromoSField('promo1','egal1',array('r.promo'),'');
    $promo2Field = new PromoSField('promo2','egal2',array('r.promo'),'');
    $womanField = new RefSField('woman',array('FIND_IN_SET(i.flags,\'femme\')+1'),'','','');
    $fields = new SFieldGroup(true,array($nameField,$firstnameField,$promo1Field,$promo2Field,$womanField));
    
    $offset = new NumericSField('offset');
   
    $sql = 'SELECT SQL_CALC_FOUND_ROWS
                       DISTINCT r.matricule,i.matricule_ax,
                       u.nom!="" AS inscrit,
                       UPPER(IF(u.nom!="",u.nom,i.nom)) AS nom,
                       IF(u.prenom!="",u.prenom,i.prenom) AS prenom,
                       IF(u.promo!="",u.promo,i.promo) AS promo,
                       a.alias AS forlife,
                       '.$globals->search_result_fields.'
                       c.uid AS contact
                 FROM  '.($with_soundex?'recherche_soundex':'recherche').'      AS r
           INNER JOIN  identification AS i   ON (i.matricule=r.matricule)
            LEFT JOIN  auth_user_md5  AS u   ON (u.matricule=r.matricule)
            LEFT JOIN  aliases        AS a   ON (u.user_id = a.id AND a.type="a_vie")
            LEFT JOIN  contacts       AS c   ON (c.uid='.((array_key_exists('uid',$_SESSION))?$_SESSION['uid']:0).' AND c.contact=u.user_id)
            '.$globals->search_result_where_statement.'
                WHERE  '.$fields->get_where_statement().'
             ORDER BY  '.(logged() && !empty($_REQUEST['mod_date_sort']) ? 'date DESC,' :'')
		        .implode(',',array_filter(array($fields->get_order_statement(),'promo DESC,nom,prenom'))).'
                LIMIT  '.$offset->value.','.$globals->search_results_per_page;

    $page->mysql_assign($sql, 'resultats', 'nb_resultats','nb_resultats_total');
    echo mysql_error();
    
    if (!logged() &&
	$page->get_template_vars('nb_resultats_total')>$globals->public_max_search_results)
    {
	new ThrowError('Votre recherche a généré trop de résultats pour un affichage public.');
    }
    $nbpages = ($page->get_template_vars('nb_resultats_total')-1)/$globals->search_results_per_page;
    $page->assign('offsets',range(0,$nbpages));
    $page->assign('url_args',$fields->get_url());
    $page->assign('with_soundex',$with_soundex);
    $page->assign('mod_date_sort',!empty($_REQUEST['mod_date_sort']));
    $page->assign('offset',$offset->value);
    $page->assign('perpage',$globals->search_results_per_page);
    $page->assign('is_admin',has_perms());
}
else
    $page->assign('formulaire',1);
$page->run();
?>
