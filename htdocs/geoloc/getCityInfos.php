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


require_once('xorg.inc.php');

header("Content-type: text/xml");

new_nonhtml_page('geoloc/getCityInfos.tpl', AUTH_COOKIE);
header("Pragma:");
// to debug sql use the next line
//new_skinned_page('', AUTH_COOKIE);

require_once('geoloc.inc.php');
require_once('search.inc.php');

$usual_fields = advancedSearchFromInput();
$fields = new SFieldGroup(true, $usual_fields);
$where = $fields->get_where_statement();
if ($where) $where = "WHERE ".$where;

$users = $globals->xdb->iterator("
    SELECT u.user_id AS id, u.prenom, u.nom, u.promo
      FROM adresses AS a 
INNER JOIN auth_user_md5 AS u ON(u.user_id = a.uid)
INNER JOIN auth_user_quick AS q ON(q.user_id = a.uid)
        ".$fields->get_select_statement()."
        ".$where."
     GROUP BY u.user_id LIMIT 11",
        $id);

$page->assign('users', $users);

$page->run();
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
