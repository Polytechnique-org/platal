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

// on ramène les données du profil connecté (uid paramètre de session)
$sql = "SELECT  u.nom, u.prenom, u.promo, u.epouse, FIND_IN_SET('femme',u.flags), u.nationalite, u.mobile, u.web, u.libre,
                q.profile_nick,
                a1.aid, a1.type, a2.aid, a2.type
          FROM  auth_user_md5   AS u
    INNER JOIN  auth_user_quick AS q  USING(user_id)
    LEFT  JOIN  applis_ins      AS a1 ON(a1.uid = u.user_id and a1.ordre = 0)
    LEFT  JOIN  applis_ins      AS a2 ON(a2.uid = u.user_id and a2.ordre = 1)
	 WHERE  u.user_id = {?}";

$result = $globals->xdb->query($sql, Session::getInt('uid', -1));
list($nom, $prenom, $promo, $epouse, $femme,
        $nationalite, $mobile, $web, $libre, $surnom, 
        $appli_id1,$appli_type1, $appli_id2,$appli_type2) = $result->fetchOneRow();

replace_ifset($nationalite,'nationalite');
replace_ifset($mobile,'mobile');
replace_ifset($web,"web");
replace_ifset($libre,"libre");
replace_ifset($appli_id1,"appli_id1");
replace_ifset($appli_id2,"appli_id2");
replace_ifset($appli_type1,"appli_type1");
replace_ifset($appli_type2,"appli_type2");
replace_ifset($surnom,"surnom");

if(Env::has('modifier') || Env::has('suivant')) {
    $mobile_public = Env::has('mobile_public');
    $mobile_ax = Env::has('mobile_ax');
    $libre_public = Env::has('libre_public');
    $web_public = Env::has('web_public');
}

// Y a-t-il une photo en attente de confirmation ?
$sql = $globals->xdb->query("SELECT COUNT(*) FROM requests WHERE type='photo' AND user_id = {?}", Session::getInt('uid', -1));
$nouvellephoto=$sql->fetchOneCell();
?>
