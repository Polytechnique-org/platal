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
new_admin_page('marketing/private.tpl');

$uid = Env::get('uid');
$res = $globals->xdb->query("SELECT nom, prenom, promo, matricule FROM auth_user_md5 WHERE user_id={?} AND perms='pending'", $uid);

if (list($nom, $prenom, $promo, $matricule) = $res->fetchOneRow()) {
    require_once('register.inc.php');
    $matricule_X = get_X_mat($matricule);
    $page->gassign('nom');
    $page->gassign('prenom');
    $page->gassign('promo');
    $page->gassign('matricule');
    $page->assign('matricule_X',$matricule_X);
} else {
    $page->kill('uid invalide');
}

if (Env::has('del')) {
    $globals->xdb->execute('DELETE FROM register_marketing WHERE uid={?} AND email={?}', $uid, Env::get('del'));
}

if (Env::has('rel') && !Env::has('valider')) {
    require_once('marketing.inc.php');
    list($to, $title, $text) = mark_text_mail($uid, Env::get('rel'));
    $from = mark_from_mail($uid, Env::get('rel'));
    $page->assign('rel_from_user', $from);
    $page->assign('rel_from_staff', "\"Equipe Polytechnique.org\" <register@polytechnique.org>");
    $page->assign('rel_to', $to);
    $page->assign('rel_title', $title);
    $page->assign('rel_text', $text);
}

if (Env::get('valider') == 'Envoyer') {
    require_once('marketing.inc.php');
    mark_send_mail($uid, Env::get('rel'), Env::get('from'), Env::get('to'), Env::get('title'), Env::get('message'));
    $page->trig("Mail envoyé");
}

if (Env::has('relance')) {
    require_once('marketing.inc.php');
    if (relance($uid)) {
        $page->trig('relance faite');
    }
}

if (Env::get('action') == 'ajouter') {
    $globals->xdb->execute(
        "INSERT INTO register_marketing
                 SET uid = {?}, sender = {?}, email = {?},
                     date = NOW(), type = {?}",
        $uid, Session::get('uid'), Env::get('email'), Env::get('type')); 
}

$res = $globals->xdb->iterator(
        "SELECT  r.*, a.alias
           FROM  register_marketing AS r
     INNER JOIN  aliases            AS a ON (r.sender=a.id AND a.type = 'a_vie')
          WHERE  uid={?}
       ORDER BY  date", $uid);
$page->assign('addr', $res);

$res = $globals->xdb->query("SELECT date, relance FROM register_pending WHERE uid = {?}", $uid);
if (list($pending, $relance) = $res->fetchOneCell()) {
    $page->gassign('pending');
    $page->gassign('relance');
}


$page->run();

// vim:set et sw=4 sws=4 sts=4:
?>
