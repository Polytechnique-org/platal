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
new_skinned_page('emails/redirect.tpl',AUTH_MDP);
require_once("emails.inc.php");

$redirect = new Redirect($_SESSION['uid']);

if (isset($_REQUEST['emailop'])) {
    if ($_REQUEST['emailop']=="retirer" && isset($_REQUEST['email'])) {
        $page->assign('retour', $redirect->delete_email($_REQUEST['email']));
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
        $page->assign('retour', $redirect->modify_email($_REQUEST['emails_actifs'],$_REQUEST['emails_rewrite']));
    }
}
$sql = "SELECT  alias
          FROM  virtual
    INNER JOIN  virtual_redirect USING(vid)
          WHERE (  redirect='{$_SESSION['forlife']}@{$globals->mail->domain}'
                OR redirect='{$_SESSION['forlife']}@{$globals->mail->domain2}' )
                AND alias LIKE '%@{$globals->mail->alias_dom}'";
$res = $globals->db->query($sql);
if (mysql_num_rows($res)) {
    list($melix) = mysql_fetch_row($res);
    list($melix) = split('@', $melix);
    $page->assign('melix',$melix);
}

$page->mysql_assign("SELECT  alias,expire
                       FROM  aliases
		      WHERE  id='{$_SESSION['uid']}' AND (type='a_vie' OR type='alias')
		   ORDER BY  !FIND_IN_SET('epouse',flags), LENGTH(alias)", 'alias');
$page->assign('emails',$redirect->emails);

$page->run();
?>
