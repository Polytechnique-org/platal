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
        $Id: advanced_search.php,v 1.15 2004-10-14 17:55:18 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
require("search.classes.inc.php");
new_skinned_page('search.tpl', AUTH_COOKIE,true);
$page->assign('advanced',1);
$page->assign('public_directory',0);
require_once("applis.func.inc.php");
require_once("geoloc.inc.php");

function form_prepare() {
    global $page,$globals;
    $page->assign('formulaire',1);
    $sql = 'SELECT id,text FROM nationalites ORDER BY text';
    $page->mysql_assign($sql,'choix_nationalites');
    $sql = 'SELECT id,text FROM binets_def ORDER BY text';
    $page->mysql_assign($sql,'choix_binets');
    $sql = 'SELECT id,text FROM groupesx_def ORDER BY text';
    $page->mysql_assign($sql,'choix_groupesx');
    $sql = 'SELECT id,text FROM sections ORDER BY text';
    $page->mysql_assign($sql,'choix_sections');
    $sql = 'SELECT id,text FROM applis_def ORDER BY text';
    $page->mysql_assign($sql,'choix_schools');
    $sql = 'DESCRIBE applis_def type';
    $result = $globals->db->query($sql);
    $row = mysql_fetch_row($result);
    $types = explode('(',$row[1]);
    $types = str_replace("'","",substr($types[1],0,-1));
    $page->assign('choix_diplomas',explode(',',$types));
    $sql = 'SELECT id,label FROM emploi_secteur ORDER BY label';
    $page->mysql_assign($sql,'choix_secteurs');
    $sql = 'SELECT id,fonction_fr FROM fonctions_def ORDER BY fonction_fr';
    $page->mysql_assign($sql,'choix_postes');
}


if (!array_key_exists('rechercher', $_REQUEST)) {
    form_prepare();
} 
else {
    $page->assign('formulaire',0);

    $with_soundex = ((isset($_REQUEST['with_soundex']) && $_REQUEST['with_soundex']==1));

    if ($with_soundex) {
        $nameField = new RefWithSoundexSField('name',array('rn.nom1_soundex','rn.nom2_soundex','rn.nom3_soundex'),'recherche_soundex','rn','u.matricule = rn.matricule');
        $firstnameField = new RefWithSoundexSField('firstname',array('rp.prenom1_soundex','rp.prenom2_soundex'),'recherche_soundex','rp','u.matricule = rp.matricule');
    } else {
        $nameField = new NameSField('name',array('u.nom','u.epouse'),'');
        $firstnameField = new StringSField('firstname',array('u.prenom'),'');
    }
    $promo1Field = new PromoSField('promo1','egal1',array('u.promo'),'');
    $promo2Field = new PromoSField('promo2','egal2',array('u.promo'),'');
    $womanField = new RefSField('woman',array('FIND_IN_SET(i.flags,\'femme\')+1'),'','','');
   
    $townField = new RefSField('ville',array('av.ville'),'adresses','av','u.user_id=av.uid',false);
    $countryField = new RefSField('pays',array('ap.pays'),'adresses','ap','u.user_id=ap.uid');
    $regionField = new RefSField('region',array('ar.region'),'adresses','ar','u.user_id=ar.uid');
   
    $entrepriseField = new RefSField('entreprise',array('ee.entreprise'),'entreprises','ee','u.user_id=ee.uid',false);
    $posteField = new RefSField('poste',array('ep.fonction'),'entreprises','ep','u.user_id=ep.uid');
    $secteurField = new RefSField('secteur',array('es.secteur'),'entreprises','es','u.user_id=es.uid');
    $cvField = new RefSField('cv',array('u.cv'),'','','',false);
   
    $nationaliteField = new RefSField('nationalite',array('u.nationalite'),'','','');
    $binetField = new RefSField('binet',array('b.binet_id'),'binets_ins','b','u.user_id=b.user_id');
    $groupexField = new RefSField('groupex',array('g.gid'),'groupesx_ins','g','u.user_id=g.guid');
    $sectionField = new RefSField('section',array('u.section'),'','','');
    $schoolField = new RefSField('school',array('as.aid'),'applis_ins','`as`','u.user_id=as.uid');
    $diplomaField = new RefSField('diploma',array('ad.type'),'applis_ins','ad','u.user_id=ad.uid');
   
    $fields = new
    SFieldGroup(true,array($nameField,$firstnameField,$promo1Field,$promo2Field,$womanField,
    $townField,$countryField,$regionField,
    $entrepriseField,$posteField,$secteurField,$cvField,
    $nationaliteField,$binetField,$groupexField,$sectionField,$schoolField,$diplomaField));
    
    if ($fields->too_large())
    {
        form_prepare();
        new ThrowError('Recherche trop générale.');
    }
    $offset = new NumericSField('offset');
   
    $where = $fields->get_where_statement();
    $sql = 'SELECT SQL_CALC_FOUND_ROWS
                       DISTINCT u.matricule,i.matricule_ax,
                       1 AS inscrit,
                       u.nom,
                       u.epouse,
                       u.prenom,
                       u.promo,
                       i.deces!=0 AS decede,
                       a.alias AS forlife,
                       u.date,
                       '.$globals->search_result_fields.'
                       c.uid AS contact
                 FROM  auth_user_md5  AS u
	   '.$fields->get_select_statement().'
           INNER JOIN  aliases        AS a ON (u.user_id = a.id AND a.type="a_vie")
           INNER JOIN  identification AS i ON (i.matricule=u.matricule)
            LEFT JOIN  contacts       AS c ON (c.uid='.((array_key_exists('uid',$_SESSION))?$_SESSION['uid']:0).' AND c.contact=u.user_id)
            '.$globals->search_result_where_statement.'
                '.(($where!='')?('WHERE '.$where):'').'
             ORDER BY  '.(logged() && !empty($_REQUEST['mod_date_sort']) ? 'date DESC,' :'')
		        .implode(',',array_filter(array($fields->get_order_statement(),'promo DESC,nom,prenom'))).'
                LIMIT  '.$offset->value.','.$globals->search_results_per_page;

    $page->mysql_assign($sql, 'resultats', 'nb_resultats','nb_resultats_total');
    
    $nbpages = ($page->get_template_vars('nb_resultats_total')-1)/$globals->search_results_per_page;
    $page->assign('offsets',range(0,$nbpages));
    $page->assign('url_args',$fields->get_url());
    $page->assign('with_soundex',$with_soundex);
    $page->assign('mod_date_sort',$_REQUEST['mod_date_sort']);
    $page->assign('offset',$offset->value);
    $page->assign('perpage',$globals->search_results_per_page);
    $page->assign('is_admin',has_perms());
}

$page->run();
?>
