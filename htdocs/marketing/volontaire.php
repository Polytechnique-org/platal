<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
new_admin_page('marketing/volontaire.tpl');

$res = $globals->xdb->query(
        "SELECT
       DISTINCT  a.promo
           FROM  register_marketing AS m
     INNER JOIN  auth_user_md5      AS a  ON a.user_id = m.uid
       ORDER BY  a.promo");
$page->assign('promos', $res->fetchColumn());


if (Env::has('promo')) {
    $sql = "SELECT  a.nom, a.prenom, a.user_id,
                    m.email, sa.alias AS forlife
              FROM  register_marketing AS m
        INNER JOIN  auth_user_md5      AS a  ON a.user_id = m.uid AND a.promo = {?}
        INNER JOIN  aliases            AS sa ON (m.sender = sa.id AND sa.type='a_vie')
          ORDER BY  a.nom";
    $page->assign('addr', $globals->xdb->iterator($sql, Env::get('promo')));
}
$page->run();
?>
