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
        $Id: postfix_perm.php,v 1.5 2004-08-31 10:03:29 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/postfix.common.tpl');

if(isset($_REQUEST['nomligne'])) {
    $nomligne = $_REQUEST['nomligne'];
    
    if (!empty($_REQUEST['del'])) {
        exec("/home/web/spam/effacerPermissions $nomligne {$_SESSION['username']}");
        $page->assign('erreur', "Action: DEL($nomligne)");
    } else if (!empty($_REQUEST['add'])) {
        exec("/home/web/spam/ajouterPermissions '".$nomligne."'");
        $page->assign('erreur', "Action: ADD($nomligne)");
    }
}

$permis = Array();
$fd = @fopen ("/etc/postfix/spampermis", "r");
while ($fd && !feof ($fd)) {
    $buffer = fgets($fd, 4096);
    if ($buffer[0]!='#' && (strlen($buffer)>1)) { # FIXME $string[i] is deprecated
        $permis[] = $buffer;
    }
}
@fclose($fd);

$page->assign_by_ref('list',$blacklist);
$page->assign('title','Permissions de polytechnique.org');
$page->assign('expl','On peut placer dans les cases les emails de personnes pouvant diffuser sans restriction.');
$page->run();
?>
