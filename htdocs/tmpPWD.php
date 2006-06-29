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

$globals->xdb->execute('DELETE FROM perte_pass WHERE DATE_SUB(NOW(), INTERVAL 380 MINUTE) > created');

$certif = Env::get('certificat');
$res    = $globals->xdb->query('SELECT uid FROM perte_pass WHERE certificat={?}', $certif);

if ($ligne = $res->fetchOneAssoc())  {
    $uid = $ligne["uid"];
    if (Post::has('response2')) {
        $password = Post::get('response2');
        $logger   = new DiogenesCoreLogger($uid);
        $globals->xdb->query('UPDATE auth_user_md5 SET password={?} WHERE user_id={?} AND perms IN("admin","user")', $password, $uid);
        $globals->xdb->query('DELETE FROM perte_pass WHERE certificat={?}', $certif);
        $logger->log("passwd","");
        new_skinned_page('tmpPWD.success.tpl', AUTH_PUBLIC);
        $page->run();
    } else {
        new_skinned_page('motdepasse.tpl', AUTH_PUBLIC);
        $page->addJsLink('javascript/motdepasse.js.php');
        $page->run();
    }
} else {
    new_skinned_page('index.tpl', AUTH_PUBLIC);
    $page->kill("Cette adresse n'existe pas ou n'existe plus sur le serveur.");
}

?>
