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
        $Id: promo.php,v 1.6 2004-11-22 17:28:14 x2000habouzit Exp $
 ***************************************************************************/


require("xorg.inc.php");
new_admin_page('marketing/promo.tpl');

$promo = (integer) (isset($_REQUEST["promo"]) ? $_REQUEST["promo"] : $_SESSION["promo"]);
$page->assign('promo', $promo);
$page->assign('promob10', $promo-10);
$page->assign('promob1', $promo-1);
$page->assign('promoa1', $promo+1);
$page->assign('promoa10', $promo+10);

$sql = "SELECT  u.nom, u.prenom, last_known_email, u.matricule, u.matricule_ax, MAX(e.date_envoi) AS dern_rel, c.email
          FROM  auth_user_md5  AS u
     LEFT JOIN  envoidirect    AS e ON (u.matricule = e.matricule)
     LEFT JOIN  en_cours       AS c ON (u.matricule = c.matricule)
         WHERE  u.promo = $promo AND u.deces = 0 AND u.perms='pending'
      GROUP BY  u.matricule
      ORDER BY  nom,prenom";

$page->mysql_assign($sql, 'nonins', 'nbnonins');

$page->run();

?>
