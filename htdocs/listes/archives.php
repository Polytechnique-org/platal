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
        $Id: archives.php,v 1.4 2004-11-30 18:39:19 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = strtolower($_REQUEST['liste']);

require_once("xorg.inc.php");
new_skinned_page('listes/archives.tpl', AUTH_COOKIE, 'listes/archives.head.tpl');
require_once('xml-rpc-client.inc.php');

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949/polytechnique.org");

if (list($det) = $client->get_members($liste)) {
    if ( substr($liste,0,5) != 'promo' && ( $det['ins'] || $det['priv'] ) && !$det['own'] && ($det['sub']<2) ) {
        $page->assign('no_list',true);
    } elseif (isset($_GET['file'])) {
        $file = $_GET['file'];
        $rep  = $_GET['rep'];
        if(strstr('/', $file)!==false || !preg_match(',^\d+/\d+$,', $_GET['rep'])) {
            $page->assign('no_list',true);
        } else { 
            $page->assign('url', $globals->lists->spool."/polytechnique.org-$liste/$rep/$file");
        }
    } else {
        $archs = Array();
        foreach (glob($globals->lists->spool."/polytechnique.org-$liste/*/*") as $rep) {
            if (preg_match(",/(\d*)/(\d*)$,", $rep, $matches)) {
                $archs[intval($matches[1])][intval($matches[2])] = true;
            }
        }
        $page->assign('archs', $archs);
        $page->assign('range', range(1,12));
    }
} else
    $page->assign('no_list',true);

$page->run();
?>
