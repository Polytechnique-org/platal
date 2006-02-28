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
require_once("search.inc.php");

new_skinned_page('search.tpl', AUTH_PUBLIC);
if (logged()) {
    new_skinned_page('search.tpl', AUTH_COOKIE);
}

$page->assign('xorg_title','Polytechnique.org - Annuaire');
require_once("applis.func.inc.php");
require_once("geoloc.inc.php");

$page->assign('baseurl', $globals->baseurl);

if (Env::has('quick')) {
    $page->assign('formulaire', 0);

    // {{{ get_list
    function get_list($offset, $limit, $order) {
        global $globals;
        $qSearch = new QuickSearch('quick');
        $fields  = new SFieldGroup(true, array($qSearch));

        if ($qSearch->isempty()) {
            new ThrowError('Recherche trop générale.');
        }
       
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                           UPPER(IF(u.nom!="",u.nom,u.nom_ini)) AS nom,
                           IF(u.prenom!="",u.prenom,u.prenom_ini) AS prenom,
                           '.$globals->search->result_fields.'
                           c.uid AS contact,
                           w.ni_id AS watch,
                           '.$qSearch->get_score_statement().'
                     FROM  auth_user_md5  AS u
                '.$fields->get_select_statement().'
                LEFT JOIN  auth_user_quick AS q  ON (u.user_id = q.user_id)
                LEFT JOIN  aliases        AS a   ON (u.user_id = a.id AND a.type="a_vie")
                LEFT JOIN  contacts       AS c   ON (c.uid='.Session::getInt('uid').' AND c.contact=u.user_id)
                LEFT JOIN  watch_nonins   AS w   ON (w.ni_id=u.user_id AND w.uid='.Session::getInt('uid').')
                '.$globals->search->result_where_statement.'
                    WHERE  '.$fields->get_where_statement().(logged() && Env::has('nonins') ? ' AND u.perms="pending" AND u.deces=0' : '').'
                 GROUP BY  u.user_id
                 ORDER BY '.($order?($order.', '):'')
                            .implode(',',array_filter(array($fields->get_order_statement(), 'u.promo DESC, NomSortKey, prenom'))).'
                    LIMIT  '.$offset * $globals->search->per_page.','.$globals->search->per_page;
        $list    = $globals->xdb->iterator($sql);
        $res     = $globals->xdb->query("SELECT  FOUND_ROWS()");
        $nb_tot  = $res->fetchOneCell();
        return array($list, $nb_tot);
    }

    // }}}

    $search = new XOrgSearch(get_list);
    $search->setNbLines($globals->search->per_page);
    $search->addOrder('score', 'score', false, 'pertinence', AUTH_PUBLIC, true);
    
    $nb_tot = $search->show();

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

// vim:set et sws=4 sw=4 sts=4:
?>
