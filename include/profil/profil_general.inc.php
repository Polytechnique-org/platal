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
        $Id: profil_general.inc.php,v 1.3 2004-08-31 13:59:43 x2000habouzit Exp $
 ***************************************************************************/

// on ramène les données du profil connecté (uid paramètre de session)
$sql = "SELECT u.nom, u.prenom".
        ", u.promo, epouse, FIND_IN_SET('femme',i.flags), nationalite".
	", mobile".
	", web".
        ", libre".
	", alias".
	", a1.aid, a1.type".
	", a2.aid, a2.type".
	" FROM auth_user_md5 AS u".
	" LEFT  JOIN applis_ins AS a1 ON(a1.uid = u.user_id and a1.ordre = 0)".
	" LEFT  JOIN applis_ins AS a2 ON(a2.uid = u.user_id and a2.ordre = 1)".
	" LEFT  JOIN identification AS i ON(u.matricule = i.matricule) ".
	" WHERE user_id=".$_SESSION['uid'];

$result = $globals->db->query($sql);
list($nom, $prenom,
     $promo, $epouse, $femme, $nationalite,
     $mobile, $web,
     $libre, $alias,
     $appli_id1,$appli_type1,
     $appli_id2,$appli_type2) = mysql_fetch_row($result);
if(mysql_errno($conn) !=0) echo mysql_errno($conn).": ".mysql_error($conn);

mysql_free_result($result);

$page->assign_by_ref('nom', $nom);
$page->assign_by_ref('prenom', $prenom);
$page->assign_by_ref('promo', $promo);
$page->assign_by_ref('epouse', $epouse);
$page->assign_by_ref('femme', $femme);
$page->assign_by_ref('alias', $alias);



replace_ifset($nationalite,'nationalite');
$page->assign_by_ref('nationalite',$nationalite);

replace_ifset($mobile,'mobile');
$page->assign_by_ref('mobile',$mobile);

replace_ifset($web,"web");
$page->assign_by_ref('web',$web);

replace_ifset($libre,"libre");
$page->assign_by_ref('libre',$libre);

replace_ifset($appli_id1,"appli_id1");
replace_ifset($appli_id2,"appli_id2");
$page->assign_by_ref('appli_id1',$appli_id1);
$page->assign_by_ref('appli_id2',$appli_id2);
replace_ifset($appli_type1,"appli_type1");
replace_ifset($appli_type2,"appli_type2");
$page->assign_by_ref('appli_type1',$appli_type1);
$page->assign_by_ref('appli_type2',$appli_type2);

?>
