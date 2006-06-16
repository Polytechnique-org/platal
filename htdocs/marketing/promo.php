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
new_admin_page('marketing/promo.tpl');

$promo = (integer) (isset($_REQUEST["promo"]) ? $_REQUEST["promo"] : $_SESSION["promo"]);
$page->assign('promo', $promo);

$sql = "SELECT  u.user_id, u.nom, u.prenom, u.last_known_email, u.matricule_ax,
                IF(MAX(m.last)>p.relance, MAX(m.last), p.relance) AS dern_rel, p.email
          FROM  auth_user_md5      AS u
     LEFT JOIN  register_pending   AS p ON p.uid = u.user_id
     LEFT JOIN  register_marketing AS m ON m.uid = u.user_id
         WHERE  u.promo = {?} AND u.deces = 0 AND u.perms='pending'
      GROUP BY  u.user_id
      ORDER BY  nom, prenom";
$page->assign('nonins', $globals->xdb->iterator($sql, $promo));

$page->run();

?>
