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
require_once 'wiki.inc.php';

require_once dirname(__FILE__).'/../classes/Platal.php';
require_once dirname(__FILE__).'/../classes/PLModule.php';

$n = wiki_pagename();
if (!$n) {
    pl_redirect('');
}

new_skinned_page('wiki.tpl');
$perms = wiki_get_perms($n);

switch (Env::v('action')) {
  case '':
    wiki_apply_perms($perms[0]);
    break;

  case 'edit':
    wiki_apply_perms($perms[1]);
    break;

  default:
    wiki_apply_perms('admin');
    break;
}

if ($p = Post::v('setrperms')) {
    wiki_apply_perms('admin');
    if (wiki_set_perms($n, $p, $perms[1])) {
        $perms = wiki_get_perms($n);
        $page->trig('Permissions mises à jour');
    }
}

if ($p = Post::v('setwperms')) {
    wiki_apply_perms('admin');
    if (wiki_set_perms($n, $perms[0], $p)) {
        $perms = wiki_get_perms($n);
        $page->trig('Permissions mises à jour');
    }
}

$wiki_cache   = wiki_work_dir().'/cache_'.$n.'.tpl';
$cache_exists = file_exists($wiki_cache);

if (Env::v('action') || !$cache_exists) {
    @unlink($wiki_cache);

    // we leave pmwiki do whatever it wants and store everything
    ob_start();
    require_once($globals->spoolroot.'/wiki/pmwiki.php');

    $wikiAll = ob_get_clean();
    // the pmwiki skin we are using (almost empty) has these keywords:
    $i = strpos($wikiAll, "<!--/HeaderText-->");
    $j = strpos($wikiAll, "<!--/PageLeftFmt-->", $i);
}

if (Env::v('action')) {
    $page->assign('xorg_extra_header', substr($wikiAll, 0, $i));
    $wikiAll = substr($wikiAll, $j);
} else {
    if (!$cache_exists) {
        $wikiAll = substr($wikiAll, $j);
        wiki_putfile($wiki_cache, $wikiAll);
    } else {
        $wikiAll = file_get_contents($wiki_cache);
    }
}

$page->assign('perms', $perms);
$page->assign('perms_opts',
              array('public' => 'Public', 'logged' => 'Connecté',
                    'mdp' => 'Authentifié', 'admin' => 'Admin'));

$page->assign('canedit',    wiki_may_have_perms($perms[1]));
$page->assign('has_perms',  wiki_may_have_perms('admin'));

$page->assign('wikipage', str_replace('.', '/', $n));
$page->assign('pmwiki',   $wikiAll);

$page->addCssLink('css/wiki.css');
$page->addJsLink('javascript/wiki.js');

$page->run();
?>
