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
new_skinned_page('wiki.tpl', Env::has('response') ? AUTH_MDP : AUTH_PUBLIC);

if ($globals->wiki->wikidir) {
    ob_start();
    require_once(dirname(dirname(__FILE__)).'/'.$globals->wiki->wikidir.'/pmwiki.php');

    $wikiAll = ob_get_clean();
    $i = strpos($wikiAll, "<!--/HeaderText-->");
    $j = strpos($wikiAll, "<!--/PageLeftFmt-->", $i);

    $page->assign('xorg_extra_header', substr($wikiAll, 0, $i));
    $page->assign('menu-pmwiki', substr($wikiAll, $i, $j-$i));
    $page->assign('pmwiki', substr($wikiAll, $j));
}

$page->addCssLink('css/wiki.css');

$page->run();
?>
