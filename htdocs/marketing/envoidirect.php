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
        $Id: envoidirect.php,v 1.5 2004-11-22 20:04:50 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_admin_page('marketing/envoidirect.tpl');

// effacement des inscrits il y a plus de 8 jours
$globals->db->query("DELETE FROM envoidirect WHERE DATE_ADD(date_succes, INTERVAL 8 DAY) < CURRENT_DATE AND date_succes <> '0000-00-00'");
$sql = "SELECT  a.date_ins,e.date_envoi,e.promo,e.nom,e.prenom,e.email,b.nom as sender
          FROM  envoidirect   AS e
    INNER JOIN  auth_user_md5 AS a ON e.matricule = a.matricule
    LEFT  JOIN  auth_user_md5 AS b ON e.sender    = b.user_id
      ORDER BY  e.date_envoi DESC";

$page->mysql_assign($sql, 'recents', 'nbrecents');

$sql = "SELECT  DISTINCT e.date_envoi, e.promo, e.nom, e.prenom, e.email, b.nom as sender
          FROM  envoidirect   AS e
    LEFT  JOIN  auth_user_md5 AS a ON e.matricule = a.matricule
    INNER JOIN  auth_user_md5 AS b ON e.sender    = b.user_id
         WHERE  a.nom is null
      ORDER BY  e.date_envoi DESC";
$page->mysql_assign($sql, 'notsub', 'nbnotsub');

$page->run();

?>
