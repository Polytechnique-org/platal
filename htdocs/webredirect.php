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
new_skinned_page('webredirect.tpl', AUTH_MDP);
$page->assign('xorg_title','Polytechnique.org - Redirection de page WEB');

$log =& Session::getMixed('log');
$url = Env::get('url');

if ((Env::get('submit') == 'Valider' or Env::get('submit') == 'Modifier') and Env::has('url')) {
    if ($globals->xdb->execute('UPDATE auth_user_quick SET redirecturl = {?} WHERE user_id = {?}',
                $url, Session::getInt('uid')))
    {
        $log->log('carva_add', 'http://'.Env::get('url'));
        $page->trig("Redirection activée vers <a href='http://$url'>$url</a>");
    } else {
        $page->trig('Erreur de mise à jour');
    }
} elseif (Env::get('submit') == "Supprimer") {
    if ($globals->xdb->execute("UPDATE auth_user_quick SET redirecturl = '' WHERE user_id = {?}", Session::getInt('uid')))
    {
        $log->log("carva_del", $url);
        Post::kill('url');
        $page->trig('Redirection supprimée');
    } else {
        $page->trig('Erreur de suppression');
    }
}


$res = $globals->xdb->query('SELECT redirecturl FROM auth_user_quick WHERE user_id = {?}', Session::getInt('uid'));
$page->assign('carva', $res->fetchOneCell());

$page->run();
?>
