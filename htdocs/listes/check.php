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
        $Id: check.php,v 1.8 2004/11/22 20:04:44 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = strtolower($_REQUEST['liste']);

require_once("xorg.inc.php");
new_admin_page('listes/check.tpl');
require_once('xml-rpc-client.inc.php');

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949/polytechnique.org");

if(isset($_POST['correct'])) { $client->check_options($liste, true); }

if(list($details,$options) = $client->check_options($liste)) {
    $page->assign_by_ref('details', $details);
    $page->assign_by_ref('options', $options);
} else
    $page->assign('no_list', true);

$page->run();
?>
