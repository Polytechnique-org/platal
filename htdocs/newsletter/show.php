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
new_skinned_page('newsletter/show.tpl', AUTH_COOKIE, 'newsletter/head.tpl');
require_once("newsletter.inc.php");

$nid = empty($_GET['nid']) ? 'last' : $_GET['nid'];
$nl = new NewsLetter($nid);
$page->assign_by_ref('nl',$nl);

if(isset($_POST['send'])) {
    $res = $globals->db->query("SELECT pref FROM newsletter_ins WHERE user_id='{$_SESSION['uid']}'");
    if(!(list($format) = mysql_fetch_row($res))) $format = 'html';
    $nl->sendTo($_SESSION['prenom'], $_SESSION['nom'], $_SESSION['bestalias'], $_SESSION['femme'], $format=='html');
}

$page->run();
?>
