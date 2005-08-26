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
$sql = "SELECT  u.nom, u.prenom, u.nom_ini, u.prenom_ini, u.promo, u.promo_sortie, u.nom_usage, u.nationalite,
		q.profile_mobile, q.profile_mobile_pub, q.profile_web, q.profile_web_pub, q.profile_freetext, q.profile_freetext_pub, q.profile_nick,
                q.profile_from_ax,
                a1.aid, a1.type, a2.aid, a2.type
          FROM  auth_user_md5   AS u
    INNER JOIN  auth_user_quick AS q  USING(user_id)
    LEFT  JOIN  applis_ins      AS a1 ON(a1.uid = u.user_id and a1.ordre = 0)
    LEFT  JOIN  applis_ins      AS a2 ON(a2.uid = u.user_id and a2.ordre = 1)
	 WHERE  u.user_id = {?}";

$result = $globals->xdb->query($sql, Session::getInt('uid', -1));
list($nom, $prenom, $nom_ini, $prenom_ini, $promo, $promo_sortie, $nom_usage, $nationalite,
	$mobile, $mobile_pub, $web, $web_pub, $freetext, $freetext_pub, $nickname, 
        $synchro_ax,
        $appli_id1,$appli_type1, $appli_id2,$appli_type2) = $result->fetchOneRow();

$result = $globals->xdb->query("SELECT pub FROM photo WHERE uid = {?}", Session::getInt('uid', -1));
$photo_pub = $result->fetchOneCell();

$nom_anc = $nom;
$prenom_anc = $prenom;
$nationalite_anc = $nationalite;
$nickname_anc = $nickname;

replace_ifset($nom,'nom');
replace_ifset($prenom,'prenom');
replace_ifset($nationalite,'nationalite');
replace_ifset($mobile,'mobile');
replace_ifset($web,"web");
replace_ifset($freetext,"freetext");
replace_ifset($appli_id1,"appli_id1");
replace_ifset($appli_id2,"appli_id2");
replace_ifset($appli_type1,"appli_type1");
replace_ifset($appli_type2,"appli_type2");
replace_ifset($nickname,"nickname");

if(Env::has('modifier') || Env::has('suivant') && Env::get('old_tab') == 'general') {
    $mobile_pub = Env::get('mobile_pub');
    $web_pub = Env::has('web_pub')?'public':'private';
    $freetext_pub = Env::has('freetext_pub')?'public':'private';
    $photo_pub = Env::has('photo_pub')?'public':'private';
    $synchro_ax = Env::has("synchro_ax")?1:0;
}

require_once("xorg.misc.inc.php");
    $nom = strtoupper($nom);
    $nom_comp = replace_accent($nom);
    $nom_anc_comp = replace_accent($nom_anc);

    $prenom = make_firstname_case($prenom);
    $prenom_comp = replace_accent($prenom);
    $prenom_anc_comp = replace_accent($prenom_anc);

// Y a-t-il une photo en attente de confirmation ?
$sql = $globals->xdb->query("SELECT COUNT(*) FROM requests WHERE type='photo' AND user_id = {?}", Session::getInt('uid', -1));
$nouvellephoto=$sql->fetchOneCell();

// vim:set et sws=4 sw=4 sts=4:
?>
