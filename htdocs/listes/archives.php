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

if (!$page) {
    require_once("xorg.inc.php");
    if (!Env::has('liste')) header('Location: index.php');
    $liste = strtolower(Env::get('liste'));

    new_skinned_page('listes/archives.tpl', AUTH_COOKIE);
    require_once('lists.inc.php');

    $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));
}

$page->addCssLink('css/lists.archives.css');
if (list($det) = $client->get_members($liste)) {
    if ( substr($liste,0,5) != 'promo' && ( $det['ins'] || $det['priv'] ) && !$det['own'] && ($det['sub']<2) ) {
        $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter");
    } elseif (Get::has('file')) {
        $file = Get::get('file');
        $rep  = Get::get('rep');
        if(strstr('/', $file)!==false || !preg_match(',^\d+/\d+$,', $rep)) {
            $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter");
        } else { 
            $page->assign('archives', $globals->lists->spool."/{$globals->mail->domain}{$globals->lists->vhost_sep}$liste/$rep/$file");
        }
    } else {
        $archs = Array();
        foreach (glob($globals->lists->spool."/{$globals->mail->domain}{$globals->lists->vhost_sep}$liste/*/*") as $rep) {
            if (preg_match(",/(\d*)/(\d*)$,", $rep, $matches)) {
                $archs[intval($matches[1])][intval($matches[2])] = true;
            }
        }
        $page->assign('archs', $archs);
        $page->assign('range', range(1,12));
    }
} else {
    $page->kill("La liste n'existe pas ou tu n'as pas le droit de la consulter");
}

$page->run();
?>
