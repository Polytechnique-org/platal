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
        $Id: routage-mail.php,v 1.3 2004-08-31 10:03:28 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('routage-mail.tpl',AUTH_MDP);
require("email.classes.inc.php");

$redirect = new Redirect();

if (!$no_update_bd && isset($_REQUEST['emailop'])) {
    if ($_REQUEST['emailop']=="retirer" && isset($_REQUEST['num'])) {
        $page->assign('retour', $redirect->delete_email($_REQUEST['num']));
    }
    elseif ($_REQUEST['emailop']=="ajouter" && isset($_REQUEST['email'])) {
        $page->assign('retour', $redirect->add_email(trim($_REQUEST['email'])));
    }
    elseif (!isset($_REQUEST['emails_actifs']) || !is_array($_REQUEST['emails_actifs'])
        || count($_REQUEST['emails_actifs'])==0) {
        $page->assign('retour', ERROR_INACTIVE_REDIRECTION);
    }
    elseif (isset($_REQUEST['emails_actifs']) && is_array($_REQUEST['emails_actifs'])
        && isset($_REQUEST['emails_rewrite']) && is_array($_REQUEST['emails_rewrite'])) {
        $page->assign('retour',
        $redirect->modify_email($_REQUEST['emails_actifs'],$_REQUEST['emails_rewrite']));
    }
}
$sql = "SELECT domain FROM groupex.aliases WHERE id=12 AND email like'".$_SESSION['username']."'";
$res = $globals->db->query($sql);
list($grx) = mysql_fetch_row($res);
$page->assign('grx',$grx);
$page->assign('domaine',substr($grx,0,-3));
$sql = "SELECT alias FROM auth_user_md5 WHERE user_id=".$_SESSION["uid"];
$res = $globals->db->query($sql);
list($alias) = mysql_fetch_row($res);
$page->assign('alias',$alias);
foreach ($redirect->emails as $mail)
    $emails[] = $mail;
$page->assign('emails',$emails);
$page->assign('no_update_bd',$no_update_bd);

$page->run();
?>
