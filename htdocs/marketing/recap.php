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
new_admin_page('marketing/recap.tpl');

$sql = "SELECT  s.success, u.promo, u.nom, u.prenom, a.alias as forlife, b.nom as sender
          FROM  register_mstats AS s
    INNER JOIN  auth_user_md5   AS u ON s.uid    = u.user_id
    INNER JOIN  aliases         AS a ON (s.uid   = a.id AND a.type='a_vie')
    INNER JOIN  auth_user_md5   AS b ON s.sender = b.user_id
         WHERE  s.success > ".date('Ymd000000', strtotime('1 month ago'))."
      ORDER BY  s.success DESC";
$page->assign('recents', $globals->xdb->iterator($sql));

$sql = "SELECT  m.uid, MAX(m.last) AS last, COUNT(m.email) AS nb, u.promo, u.nom, u.prenom,  b.nom as sender
          FROM  register_marketing AS m
    INNER JOIN  auth_user_md5      AS u ON m.uid    = u.user_id
    INNER JOIN  auth_user_md5      AS b ON m.sender = b.user_id
         WHERE  m.nb > 0
      GROUP BY  m.uid
      ORDER BY  u.promo, u.nom";
$page->assign('notsub', $globals->xdb->iterator($sql));

$page->run();

?>
