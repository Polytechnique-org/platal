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

require_once("xnet.inc.php");

// this page is to create a smarty template page from a wiki file
// the wiki engine used is pmwiki.
// the templates created are stored in wiki.d/cache_wikiword.tpl

new_page('wiki.tpl'));

if ($globals->wiki->wikidir && $globals->xnet->wiki) {
    $wikisite = 'xnet';
    function more_wiki_config() {
        global $Conditions, $DefaultPasswords;
        $Conditions['has_perms'] = has_perms() || may_update();
        $Conditions['is_member'] = is_member();
        $DefaultPasswords['read'] = 'is_member:';
    }
    require_once("wiki.inc.php");
    $page->changeTpl($wiki_template);
    $page->setType($globals->asso('cat'));
    $page->addCssLink('css/wiki.css');
    $page->useMenu();
    wiki_assign_auth();
}

if (!Env::get('action')) {
    $page->addJsLink('javascript/wiki.js');
}

$page->assign('is_member', is_member());
$page->assign('has_perms', has_perms() || may_update());

$page->run();
?>
