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
        $Id: ins_confirmees.php,v 1.2 2004-08-31 10:03:30 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('marketing/ins_confirmees.tpl',true);

if (!isset($_GET["sort"]) || $_GET["sort"] != "promo") $_GET["sort"] = "date_ins";

$sql = "SELECT a.username,a.date_ins,a.promo,a.nom,a.prenom
        FROM       ins_confirmees AS i
        INNER JOIN auth_user_md5  AS a ON i.id=a.user_id
        ORDER BY a.{$_GET['sort']} DESC";
$page->mysql_assign($sql, 'ins', 'nb_ins');

$page->run();
?>
