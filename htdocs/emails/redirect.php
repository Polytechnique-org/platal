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

$uid     = Session::getInt('uid');
$forlife = Session::get('forlife');

$redirect = new Redirect(Session::getInt('uid'));

if (Env::has('emailop')) {
    $actifs = Env::getMixed('emails_actifs', Array());
    if (Env::get('emailop') == "retirer" && Env::has('email')) {
        $page->assign('retour', $redirect->delete_email(Env::get('email')));
    } elseif (Env::get('emailop') == "ajouter" && Env::has('email')) {
        $page->assign('retour', $redirect->add_email(trim(Env::get('email'))));
    } elseif (empty($actifs)) {
        $page->assign('retour', ERROR_INACTIVE_REDIRECTION);
    } elseif (is_array($actifs)) {
        $page->assign('retour', $redirect->modify_email($actifs, Env::getMixed('emails_rewrite',Array())));
    }
}
$sql = "SELECT  alias
          FROM  virtual
    INNER JOIN  virtual_redirect USING(vid)
          WHERE (  redirect='$forlife@{$globals->mail->domain}'
                OR redirect='$forlife@{$globals->mail->domain2}' )
                AND alias LIKE '%@{$globals->mail->alias_dom}'";
$res = $globals->db->query($sql);
if (mysql_num_rows($res)) {
    list($melix) = mysql_fetch_row($res);
    list($melix) = split('@', $melix);
    $page->assign('melix',$melix);
}

$page->mysql_assign("SELECT  alias,expire
                       FROM  aliases
		      WHERE  id=$uid AND (type='a_vie' OR type='alias')
		   ORDER BY  !FIND_IN_SET('epouse',flags), LENGTH(alias)", 'alias');
$page->assign('emails',$redirect->emails);

$page->run();
?>
