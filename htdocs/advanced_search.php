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
 ***************************************************************************/

require_once("xorg.inc.php");
require_once("search.classes.inc.php");
new_skinned_page('search.tpl', AUTH_COOKIE);

$page->assign('advanced',1);
$page->assign('public_directory',0);
require_once("applis.func.inc.php");
require_once("geoloc.inc.php");

// {{{ function form_prepare()

function form_prepare()
{
    global $page,$globals;
    $page->assign('formulaire',1);
    $page->assign('choix_nats',     $globals->xdb->iterator('SELECT a2 AS id,IF(nat=\'\',pays,nat) AS text FROM geoloc_pays ORDER BY text'));
    $page->assign('choix_postes',   $globals->xdb->iterator('SELECT id,fonction_fr FROM fonctions_def ORDER BY fonction_fr'));
    $page->assign('choix_binets',   $globals->xdb->iterator('SELECT id,text FROM binets_def ORDER BY text'));
    $page->assign('choix_groupesx', $globals->xdb->iterator('SELECT id,text FROM groupesx_def ORDER BY text'));
    $page->assign('choix_sections', $globals->xdb->iterator('SELECT id,text FROM sections ORDER BY text'));
    $page->assign('choix_schools',  $globals->xdb->iterator('SELECT id,text FROM applis_def ORDER BY text'));
    $page->assign('choix_secteurs', $globals->xdb->iterator('SELECT id,label FROM emploi_secteur ORDER BY label'));

    if (Env::has('school')) {
        $sql = 'SELECT type FROM applis_def WHERE id='.Env::getInt('school');
    } else {
        $sql = 'DESCRIBE applis_def type';
    }
    $res = $globals->xdb->query($sql);
    $row = $res->fetchOneRow();
    if (Env::has('school')) {
        $types = $row[0];
    } else {
        $types = explode('(',$row[1]);
        $types = str_replace("'","",substr($types[1],0,-1));
    }
    $page->assign('choix_diplomas', explode(',',$types));
}

// }}}

if (!Env::has('rechercher')) {
    form_prepare();
} else {

    if ($with_soundex = Env::has('with_soundex')) {
        $nameField      = new RefWithSoundexSField('name',array('rn.nom1_soundex','rn.nom2_soundex','rn.nom3_soundex'),'recherche_soundex','rn','u.matricule = rn.matricule');
        $firstnameField = new RefWithSoundexSField('firstname',array('rp.prenom1_soundex','rp.prenom2_soundex'),'recherche_soundex','rp','u.matricule = rp.matricule');
    } else {
        $nameField      = new NameSField('name',array('u.nom','u.epouse'),'');
        $firstnameField = new StringSField('firstname',array('u.prenom'),'');
    }
    
    $promo1Field     = new PromoSField('promo1','egal1',array('u.promo'),'');
    $promo2Field     = new PromoSField('promo2','egal2',array('u.promo'),'');
    $womanField      = new RefSField('woman',array('FIND_IN_SET(u.flags,\'femme\')+1'),'','','');
    $subscriberField = new RefSField('subscriber',array('!(u.perms IN (\'admin\',\'user\'))+1'),'','','');
    $aliveField      = new RefSField('alive',array('(u.deces!=0)+1'),'','','');
   
    $townField       = new RefSField('ville',array('av.ville'),'adresses','av','u.user_id=av.uid',false);
    $countryField    = new RefSField('pays',array('ap.pays'),'adresses','ap','u.user_id=ap.uid');
    $regionField     = new RefSField('region',array('ar.region'),'adresses','ar','u.user_id=ar.uid');
   
    $entrepriseField = new RefSField('entreprise',array('ee.entreprise'),'entreprises','ee','u.user_id=ee.uid',false);
    $posteField      = new RefSField('poste',array('ep.fonction'),'entreprises','ep','u.user_id=ep.uid');
    $secteurField    = new RefSField('secteur',array('fm.secteur'),'entreprises','fm','u.user_id=fm.uid');
    $cvField         = new RefSField('cv',array('u.cv'),'','','',false);
   
    $natField        = new RefSField('nationalite',array('u.nationalite'),'','','');
    $binetField      = new RefSField('binet',array('b.binet_id'),'binets_ins','b','u.user_id=b.user_id');
    $groupexField    = new RefSField('groupex',array('g.gid'),'groupesx_ins','g','u.user_id=g.guid');
    $sectionField    = new RefSField('section',array('u.section'),'','','');
    $schoolField     = new RefSField('school',array('as.aid'),'applis_ins','`as`','u.user_id=as.uid');
    $diplomaField    = new RefSField('diploma',array('ad.type'),'applis_ins','ad','u.user_id=ad.uid');
  
    $freeField       = new RefSField('free',array('u.libre'),'','','',false);
    $offset          = new NumericSField('offset');
  
    $fields          = new SFieldGroup(true, array( $nameField, $firstnameField, $promo1Field,
                $promo2Field, $womanField, $subscriberField, $aliveField, $townField, $countryField,
                $regionField, $entrepriseField, $posteField, $secteurField, $cvField, $natField,
                $binetField, $groupexField, $sectionField, $schoolField, $diplomaField, $freeField)
            );

    
    if ($fields->too_large()) {
        form_prepare();
        new ThrowError('Recherche trop générale.');
    }
   
    $where = $fields->get_where_statement();
    $sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT
                       u.nom, u.prenom,
                       '.$globals->search->result_fields.'
                       c.uid AS contact,
                       w.ni_id AS watch
                 FROM  auth_user_md5  AS u 
            '.$fields->get_select_statement().'
            '.(Env::has('only_referent') ? ' INNER JOIN mentor AS m ON (m.uid = u.user_id)' : '').'
            LEFT JOIN  aliases        AS a ON (u.user_id = a.id AND a.type="a_vie")
            LEFT JOIN  contacts       AS c ON (c.uid='.Session::getInt('uid').' AND c.contact=u.user_id)
            LEFT JOIN  watch_nonins   AS w ON (w.ni_id=u.user_id AND w.uid='.Session::getInt('uid').')
            '.$globals->search->result_where_statement.'
                '.(empty($where) ? '' : "WHERE  $where").'
             ORDER BY  '.(logged() && Env::has('mod_date_sort') ? 'date DESC,' :'')
		        .implode(',',array_filter(array($fields->get_order_statement(), 'promo DESC, NomSortKey, prenom'))).'
                LIMIT  '.$offset->value.','.$globals->search->per_page;

    $page->assign('resultats', $globals->xdb->iterator($sql));
    $res     = $globals->xdb->query("SELECT  FOUND_ROWS()");
    $nb_tot  = $res->fetchOneCell();
    $nbpages = ($nb_tot - 1)/$globals->search->per_page;

    $url_ext = Array(
        'mod_date_sort' => Env::has('mod_date_sort'),
        'with_soundex'  => $with_soundex
    );

    $url_short = $fields->get_url($url_ext);
    $url_args  = $url_short . '&amp;rechercher=1';

    $page->assign('offset',    $offset->value);
    $page->assign('offsets',   range(0, $nbpages));
    $page->assign('url_short', $url_short);
    $page->assign('url_args',  $url_args);
    $page->assign('perpage',   $globals->search->per_page);
    $page->assign('nb_tot',    $nb_tot);
    $page->assign('with_soundex',   $with_soundex);
            
    if ($nb_tot > $globals->search->private_max) {
        form_prepare();
        new ThrowError('Recherche trop générale');
    }
    
}

$page->register_modifier('display_lines', 'display_lines');
$page->run();
?>
