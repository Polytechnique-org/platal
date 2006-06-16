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
new_admin_page('marketing/this_week.tpl');

$sort = Get::get('sort') == 'promo' ? 'promo' : 'date_ins';

$sql = "SELECT  a.alias AS forlife, u.date_ins, u.promo, u.nom, u.prenom
          FROM  auth_user_md5  AS u
    INNER JOIN  aliases        AS a ON (u.user_id = a.id AND a.type='a_vie')
         WHERE  u.date_ins > ".date("Ymd000000", strtotime ('1 week ago'))."
      ORDER BY  u.$sort DESC";
$page->assign('ins', $globals->xdb->iterator($sql));

$page->run();
?>
