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
        $Id: carva_redirect.php,v 1.4 2004-08-31 10:03:28 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('carva_redirect.tpl', AUTH_MDP);

if (isset($_REQUEST['submit']) and ($_REQUEST['submit'] == "Valider" or $_REQUEST['submit'] == "Modifier") and isset($_REQUEST['url'])) {
    // on change la redirection (attention à http://)
    $globals->db->query("update auth_user_md5 set redirecturl = '{$_REQUEST['url']}'"
              ." where user_id = '{$_SESSION['uid']}'");
    if (mysql_errno($conn) == 0) {
        $_SESSION['log']->log("carva_add","http://".$_REQUEST['url']);
        $page->assign('message',"<p class='normal'>Redirection activée vers <a href='http://"
                .$_REQUEST['url']."'>{$_REQUEST['url']}</a></p>\n");
    } else {
        $page->assign('message',"<p class='erreur'>Erreur de mise à jour</p>\n");
    }
} elseif (isset($_REQUEST['submit']) and $_REQUEST['submit'] == "Supprimer") {
    // on supprime la redirection
    $globals->db->query("update auth_user_md5 set redirecturl = '' where user_id = {$_SESSION['uid']}");
    if (mysql_errno($conn) == 0) {
        $_SESSION['log']->log("carva_del",$_REQUEST['url']);
        $_POST['url'] = '';
        $page->assign('message',"<p class='normal'>Redirection supprimée</p>");
    } else {
        $page->assign('message',"<p class='erreur'>Erreur de suppression</p>\n");
    }
}

$result = $globals->db->query("select alias, redirecturl from auth_user_md5 where user_id={$_SESSION['uid']}");
list($alias, $carva) = mysql_fetch_row($result);
mysql_free_result($result);
$page->assign('carva', $carva);
$page->assign('alias', $alias);

$page->run();
?>
