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

new_skinned_page('search.tpl', AUTH_PUBLIC);
if (logged()) {
    new_skinned_page('search.tpl', AUTH_COOKIE);
}

require_once("applis.func.inc.php");
require_once("geoloc.inc.php");

if (Env::has('quick')) {
    $page->assign('formulaire', 0);

    $qSearch = new QuickSearch('quick');
    $fields  = new SFieldGroup(true, array($qSearch));

    $offset  = new NumericSField('offset');
    
    if ($qSearch->isempty()) {
	new ThrowError('Recherche trop générale.');
    }
   
    $sql = 'SELECT SQL_CALC_FOUND_ROWS  DISTINCT 
                       UPPER(IF(u.nom!="",u.nom,u.nom_ini)) AS nom,
                       IF(u.prenom!="",u.prenom,u.prenom_ini) AS prenom,
                       '.$globals->search->result_fields.'
                       c.uid AS contact,
		       w.ni_id AS watch,
                       '.$qSearch->get_mark_statement().'
                 FROM  auth_user_md5  AS u
            LEFT JOIN  aliases        AS a   ON (u.user_id = a.id AND a.type="a_vie")
            LEFT JOIN  contacts       AS c   ON (c.uid='.Session::getInt('uid').' AND c.contact=u.user_id)
            LEFT JOIN  watch_nonins   AS w   ON (w.ni_id=u.user_id AND w.uid='.Session::getInt('uid').')
            '.$globals->search->result_where_statement.'
                WHERE  '.$fields->get_where_statement().(logged() && Env::has('nonins') ? ' AND u.perms="pending" AND u.deces=0' : '').'
               HAVING  mark>0
             ORDER BY  '.(logged() && Env::has('mod_date_sort') ? 'date DESC,' :'')
		        .implode(',',array_filter(array($fields->get_order_statement(), 'u.promo DESC, NomSortKey, prenom'))).'
                LIMIT  '.$offset->value.','.$globals->search->per_page;

    $page->assign('resultats', $globals->xdb->iterator($sql));
    $res     = $globals->xdb->query("SELECT  FOUND_ROWS()");
    $nb_tot  = $res->fetchOneCell();
    $nbpages  = ($nb_tot-1)/$globals->search->per_page;

    $url_ext = Array(
        'mod_date_sort' => Env::has('mod_date_sort')
    );
    $page->assign('offset',   $offset->value);
    $page->assign('offsets',  range(0, $nbpages));
    $page->assign('url_args', $fields->get_url($url_ext));
    $page->assign('perpage',  $globals->search->per_page);
    $page->assign('nb_tot',   $nb_tot);
    
    if (!logged() && $nb_tot > $globals->search->public_max) {
	new ThrowError('Votre recherche a généré trop de résultats pour un affichage public.');
    } elseif ($nb_tot > $globals->search->private_max) {
        new ThrowError('Recherche trop générale');
    } elseif (empty($nb_tot)) {
        new ThrowError('il n\'existe personne correspondant à ces critères dans la base !');
    }

} else {
    $page->assign('formulaire',1);
}

$page->register_modifier('display_lines', 'display_lines');
$page->run();
?>
