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
        $Id: newsletter_edit.php,v 1.8 2004-11-22 20:04:36 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_admin_page('admin/newsletter_edit.tpl', 'newsletter/head.tpl');
require_once("newsletter.inc.php");

$nid = empty($_GET['nid']) ? 'last' : $_GET['nid'];
$nl = new NewsLetter($nid);
if(isset($_GET['del_aid'])) {
    $nl->delArticle($_GET['del_aid']);
    header("Location: ?nid=$nid");
}

if(isset($_POST['update'])) {
    $nl->_title = $_POST['title'];
    $nl->_date = $_POST['date'];
    $nl->_head = $_POST['head'];
    $nl->save();
}

if(isset($_POST['save'])) {
    $eaid = $_GET['edit_aid'];
    $art = new NLArticle($_POST['title'], $_POST['body'], $_POST['append'], $eaid, $_POST['cid'], $_POST['pos']);
    $nl->saveArticle($art);
    header("Location: ?nid=$nid");
}

if(isset($_GET['edit_aid'])) {
    $eaid = $_GET['edit_aid'];
    if(isset($_POST['aid'])) {
	$art = new NLArticle($_POST['title'], $_POST['body'], $_POST['append'],
		$eaid, $_POST['cid'], $_POST['pos']);
    } elseif($eaid<0) {
	$art = new NLArticle();
    } else {
	$art = $nl->getArt($_GET['edit_aid']);
    }
    $page->assign('art', $art);
}

$page->assign_by_ref('nl',$nl);

$page->run();
?>
