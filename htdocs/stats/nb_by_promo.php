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
new_skinned_page('stats/nb_by_promo.tpl', AUTH_COOKIE);

$result = $globals->db->query("SELECT  promo,COUNT(*)
                                 FROM  auth_user_md5
				WHERE  promo > 1900 AND perms IN ('admin','user')
			     GROUP BY  promo
			     ORDER BY  promo");
$max=0; $min=3000;
while(list($promo,$nb)=mysql_fetch_row($result)) {
    $promo=intval($promo);
    if(!isset($nbpromo[$promo/10]))
        $nbpromo[$promo/10] = Array('','','','','','','','','',''); // tableau de 10 cases vides
    $nbpromo[$promo/10][$promo%10]=Array('promo' => $promo, 'nb' => $nb);
}

$page->assign_by_ref('nbs', $nbpromo);
$page->assign('min', $min-$min % 10);
$page->assign('max', $max+10-$max%10);

$page->run();
?>
