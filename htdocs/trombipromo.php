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
        $Id: trombipromo.php,v 1.5 2004-09-02 22:27:05 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('trombipromo.tpl', AUTH_COOKIE, true);

$limit = 30;

$page->assign('limit', $limit);

if(!isset($_REQUEST['xpromo'])) $page->run();

$xpromo = intval($_REQUEST['xpromo']);

if ( $xpromo<1900 || $xpromo>date('Y') || ($xpormo = -1 && $_SESSION['perms']!="admin") ) {
    $page->assign('erreur', "Promotion incorrecte (saisir au format YYYY). Recommence.");
}

$offset = (empty($_REQUEST['offset']) ? 0 : $_REQUEST['offset']);

$where = ( $xpromo>0 ? "WHERE promo='$xpromo'" : "" );

$res = $globals->db->query("SELECT  COUNT(*)
                              FROM  auth_user_md5 AS u
                        RIGHT JOIN  photo         AS p ON u.user_id=p.uid
                        $where");
list($pnb) = mysql_fetch_row($res);
$page->assign('pnb', $pnb);

$sql = "SELECT  promo,user_id,a.alias AS forlife,nom,prenom
          FROM  photo         AS p
    INNER JOIN  auth_user_md5 AS u ON u.user_id=p.uid
    INNER JOIN  aliases       AS a ON ( u.user_id=a.id AND a.type='a_vie' )
        $where
      ORDER BY  promo,nom,prenom LIMIT $offset,$limit";

$links = Array();
if($offset>0) { $links[] = Array($offset-$limit, 'précédent'); }
for($i = 0; $i < $pnb / $limit ; $i++) $links[] = Array($i*$limit, $i+1);
if($offset+$limit < $pnb) { $links[] = Array ($offset+$limit, 'suivant'); }
$page->assign('links',$links);

$page->mysql_assign($sql,'photos');
$page->run();

?>
