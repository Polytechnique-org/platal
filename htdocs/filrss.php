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
new_skinned_page('filrss.tpl',AUTH_MDP);

if (Env::has('referer')) {
    $param = Env::get('referer');
    $page->assign('goback',$param);
}

if (Env::has('referer')) {
    $act = Env::get('act_rss');
    if ($act == 'Activer'){
        $page->trig("Ton Fil RSS est activé.");
        $_SESSION['core_rss_hash'] = rand_url_id(16);
        $globals->xdb->execute('UPDATE auth_user_quick SET core_rss_hash={?} WHERE user_id={?}',
                                Session::get('core_rss_hash'), Session::getInt('uid'));
    }                                
}

if (Session::get('core_rss_hash') > '')
{
    $page->assign('rsshash','oui');
}

$page->run();

// vim:set et sws=4 sw=4 sts=4:
?>
