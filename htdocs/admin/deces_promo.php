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
new_admin_page('admin/deces_promo.tpl');

$promo = Env::getInt('promo');
if (Env::has('sub10')) $promo -= 10;
if (Env::has('sub01')) $promo -=  1;
if (Env::has('add01')) $promo +=  1;
if (Env::has('add10')) $promo += 10;

$page->assign('promo',$promo);

if (Env::get('valider') == "Valider") {
    $res = $globals->xdb->iterRow("SELECT user_id,matricule,deces FROM auth_user_md5 WHERE promo = {?}", $promo);
    while (list($uid,$mat,$deces) = $res->next()) {
        $val = Env::get($mat);
	if($val == $deces) continue;
	$globals->xdb->execute('UPDATE auth_user_md5 SET deces={?} WHERE matricule = {?}', $val, $mat);
	if($deces=='0000-00-00' or empty($deces)) {
	    require_once('notifs.inc.php');
	    register_watch_op($uid, WATCH_DEATH, $val);
	    require_once('user.func.inc.php');
	    user_clear_all_subs($uid, false);	// by default, dead ppl do not loose their email
	}
    }
}

$res = $globals->xdb->iterator('SELECT matricule, nom, prenom, deces FROM auth_user_md5 WHERE promo = {?} ORDER BY nom,prenom', $promo);
$page->assign('decedes', $res);

$page->run();
?>
