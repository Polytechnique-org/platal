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
        $Id: newsletter.php,v 1.6 2004-09-02 22:27:05 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('newsletter.tpl', AUTH_COOKIE);

if (isset($_REQUEST['last']))
    $res=$globals->db->query("SELECT UNIX_TIMESTAMP(date),titre,text FROM newsletter ORDER BY id DESC LIMIT 1");
elseif (isset($_REQUEST['nl_id'])) 
    $res=$globals->db->query("SELECT UNIX_TIMESTAMP(date),titre,text FROM newsletter WHERE id='{$_REQUEST['nl_id']}'");
else
    $res="";

if (($res)&&(list($nl_date, $nl_titre, $nl_text) = mysql_fetch_row($res))) {
    $page->assign('nl_date', $nl_date);
    $page->assign('nl_titre', $nl_titre);
    $page->assign('nl_text', $nl_text);

    if (isset($_REQUEST['send_mail'])) {
        require('diogenes.mailer.inc.php');
        $mymail = new DiogenesMailer("info_newsletter@polytechnique.org",
                $_SESSION['forlife']."@polytechnique.org",
                "[polytechnique.org] ".$nl_titre);
        $mymail->addHeader("From: \"Equipe polytechnique.org\" <info_newsletter@polytechnique.org>");
        $mymail->setBody("Suite à ta demande sur le site web, nous te réexpédions cette lettre d'informations archivée.\r\n\r\n".strip_tags($nl_text));
        $mymail->send();
        $page->assign('erreur', '<p class="erreur">Mail envoyé.</p>');
    }
}

$sql = "SELECT id,date,titre FROM newsletter ORDER BY date DESC";
$page->mysql_assign($sql, 'nl_list');

$page->run();
?>
