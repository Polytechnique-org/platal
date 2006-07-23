<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
new_admin_page('admin/newsletter_edit.tpl');
$page->assign('xorg_title','Polytechnique.org - Administration - Newsletter : Edition'); 
require_once("newsletter.inc.php");

$nid = Get::v('nid', 'last');
$nl  = new NewsLetter($nid);

if(Get::has('del_aid')) {
    $nl->delArticle(Get::v('del_aid'));
    http_redirect("{$_SERVER['PHP_SELF']}?nid=$nid");
}

if(Post::v('update')) {
    $nl->_title = Post::v('title');
    $nl->_date  = Post::v('date');
    $nl->_head  = Post::v('head');
    $nl->save();
}

if(Post::v('save')) {
    $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
            Get::v('edit_aid'), Post::v('cid'), Post::v('pos'));
    $nl->saveArticle($art);
    http_redirect("{$_SERVER['PHP_SELF']}?nid=$nid");
}

if(Get::has('edit_aid')) {
    $eaid = Get::v('edit_aid');
    if(Post::has('aid')) {
        $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
                $eaid, Post::v('cid'), Post::v('pos'));
    } else {
	$art = $eaid<0 ? new NLArticle() : $nl->getArt($eaid);
    }
    $page->assign('art', $art);
}

$page->assign_by_ref('nl',$nl);

$page->run();
?>
