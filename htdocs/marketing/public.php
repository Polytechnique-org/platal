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
new_skinned_page('marketing/public.tpl', AUTH_COOKIE);

if (! Env::has('num')) { exit; }

$uid = Env::get('num');

$res = $globals->xdb->query("SELECT nom, prenom, promo FROM auth_user_md5 WHERE user_id={?} AND perms='pending'", $uid);

if (list($nom, $prenom, $promo) = $res->fetchOneRow()) {
    $page->assign('prenom', $prenom);
    $page->assign('nom', $nom);
    $page->assign('promo', $promo);

    if (Env::has('valide')) {
        require_once('xorg.misc.inc.php');

        $email = trim(Env::get('mail'));
        $res   = $globals->xdb->query('SELECT COUNT(*) FROM register_marketing WHERE uid={?} AND email={?}', $uid, $email);

        if (!isvalid_email_redirection($email)) {
            $page->trig("Email invalide !");
        } elseif ($res->fetchOneCell()) {
            $page->assign('already', true);
        } else {
            $page->assign('ok', true);
            $globals->xdb->execute(
                    "INSERT INTO  register_marketing (uid,sender,email,date,last,nb,type,hash)
                          VALUES  ({?}, {?}, {?}, NOW(), 0, 0, {?}, '')",
                    $uid, Session::getInt('uid'), $email, Env::get('origine'));
            require_once('validations.inc.php');
            $req = new MarkReq(Session::getInt('uid'), $uid, $email, Env::get('origine')=='user');
            $req->submit();
        }
    }
}

$page->run();
?>
