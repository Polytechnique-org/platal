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
new_skinned_page('webredirect.tpl', AUTH_MDP);

if (isset($_REQUEST['submit']) and ($_REQUEST['submit'] == "Valider" or $_REQUEST['submit'] == "Modifier") and isset($_REQUEST['url'])) {
    // on change la redirection (attention à http://)
    $globals->db->query("update auth_user_quick set redirecturl = '{$_REQUEST['url']}' where user_id = '{$_SESSION['uid']}'");
    if (mysql_errno() == 0) {
        $_SESSION['log']->log("carva_add","http://".$_REQUEST['url']);
        $page->trigger("Redirection activée vers <a href='http://{$_REQUEST['url']}'>{$_REQUEST['url']}</a>");
    } else {
        $page->trigger('Erreur de mise à jour');
    }
} elseif (isset($_REQUEST['submit']) and $_REQUEST['submit'] == "Supprimer") {
    // on supprime la redirection
    $globals->db->query("update auth_user_quick set redirecturl = '' where user_id = {$_SESSION['uid']}");
    if (mysql_errno() == 0) {
        $_SESSION['log']->log("carva_del",$_REQUEST['url']);
        $_POST['url'] = '';
        $page->trigger('Redirection supprimée');
    } else {
        $page->trigger('Erreur de suppression');
    }
}


$result = $globals->db->query("select redirecturl from auth_user_quick where user_id={$_SESSION['uid']}");
list($carva) = mysql_fetch_row($result);
mysql_free_result($result);
$page->assign('carva', $carva);

$page->run();
?>
