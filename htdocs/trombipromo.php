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
new_skinned_page('trombipromo.tpl', AUTH_COOKIE);
require_once("trombi.inc.php");

function getList($offset,$limit) {
    global $globals;

    $xpromo = intval($_REQUEST['xpromo']);
    $where = ( $xpromo>0 ? "WHERE promo='$xpromo'" : "" );

    $res = $globals->db->query("SELECT  COUNT(*)
				  FROM  auth_user_md5 AS u
			    RIGHT JOIN  photo         AS p ON u.user_id=p.uid
			    $where");
    list($pnb) = mysql_fetch_row($res);
    mysql_free_result($res);

    $sql = "SELECT  promo,user_id,a.alias AS forlife,nom,prenom
	      FROM  photo         AS p
	INNER JOIN  auth_user_md5 AS u ON u.user_id=p.uid
	INNER JOIN  aliases       AS a ON ( u.user_id=a.id AND a.type='a_vie' )
	    $where
	  ORDER BY  promo,nom,prenom LIMIT ".($offset*$limit).",$limit";

    $res = $globals->db->query($sql);
    $list = Array();
    while($tmp = mysql_fetch_assoc($res)) $list[] = $tmp;
    mysql_free_result($res);

    return Array($pnb, $list);
}

if(isset($_REQUEST['xpromo'])) {
    $xpromo = intval($_REQUEST['xpromo']);

    if ( $xpromo<1900 || $xpromo>date('Y') || ($xpromo == -1 && $_SESSION['perms']!="admin") ) {
	$page->trig("Promotion incorrecte (saisir au format YYYY). Recommence.");
        $page->assign('error', true);
    } else {
	$trombi = new Trombi('getList');
	$trombi->hidePromo();
	$trombi->setAdmin();
	$page->assign_by_ref('trombi',$trombi);
    }
}

$page->run();

?>
