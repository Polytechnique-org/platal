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
new_skinned_page('acces_smtp.tpl', AUTH_MDP);

if (isset($_REQUEST['op']) && $_REQUEST['op'] == "Valider"
        && isset($_REQUEST['smtppass1']) && isset($_REQUEST['smtppass2'])
        && $_REQUEST['smtppass1'] == $_REQUEST['smtppass2']
        && strlen($_REQUEST['smtppass1'])>=6) {

    // on change le mot de passe
    $result = $globals->db->query("select smtppass from auth_user_md5 where user_id = ".$_SESSION['uid']);
    list($smtppass_old) = mysql_fetch_row($result);
    mysql_free_result($result);
    $globals->db->query("update auth_user_md5 set smtppass = '{$_REQUEST['smtppass1']}' where user_id = ".$_SESSION['uid']);
    $_SESSION['log']->log("passwd_ssl");

    $page->trig('Mot de passe enregistré');

} elseif (isset($_REQUEST['op']) && $_REQUEST['op'] == "Supprimer") {

    $globals->db->query("update auth_user_md5 set smtppass = '' where user_id = ".$_SESSION['uid']);
    $_SESSION['log']->log("passwd_del");
    $page->trig('Compte SMTP et NNTP supprimé');

}

$result = $globals->db->query("select smtppass from auth_user_md5 where user_id = ".$_SESSION['uid']);
list($smtppass_old) = mysql_fetch_row($result);
mysql_free_result($result);

$page->assign('actif', ($smtppass_old != ""));

$page->run(($smtppass_old != "") ? "actif" : "");

// vim:et:sw=4:
?>
