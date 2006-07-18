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

require_once 'xorg.inc.php';

// this page is to create a smarty template page from a wiki file
// the wiki engine used is pmwiki.
// the templates created are stored in wiki.d/cache_wikiword.tpl

// some page can be seen by everybody (public), but to validate a password
// if we arrive here before setting new access we need to try an auth
new_skinned_page('wiki.tpl');

if ($globals->wiki->wikidir) {
    $wikisite = 'xorg';
    require_once 'wiki.inc.php';
    $page->changeTpl($wiki_template);
}

if (!Env::get('action')) {
    $page->addJsLink('javascript/wiki.js');
}

wiki_assign_auth();
$page->addCssLink('css/wiki.css');

$page->run();
?>
