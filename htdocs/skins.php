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
 *  $Id: skins.php,v 1.12 2004/11/24 10:12:47 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
if (!$globals->skin->enable) {
    header("Location: index.php");
}
new_skinned_page('skins.tpl', AUTH_COOKIE);

if (isset($_REQUEST['newskin']))  {  // formulaire soumis, traitons les données envoyées
    $globals->db->query("UPDATE auth_user_quick
                SET skin={$_REQUEST['newskin']}
                WHERE user_id={$_SESSION['uid']}");
    set_skin();
}

$sql = "SELECT s.*,auteur,count(*) AS nb
          FROM skins AS s
     LEFT JOIN auth_user_quick AS a ON s.id=a.skin
         WHERE skin_tpl != '' AND ext != ''
      GROUP BY id ORDER BY s.date DESC";
$page->mysql_assign($sql, 'skins');

$page->run();
?>
