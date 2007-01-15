<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

// $GLOBALS['IS_XNET_SITE'] = true;

require_once dirname(__FILE__).'/../include/xorg.inc.php';

if (!($path = Env::v('n')) || ($path{0} < 'A' || $path{0} > 'Z')) {

    $platal = new Platal('auth', 'banana', 'carnet', 'email', 'events',
                         'geoloc', 'lists', 'marketing', 'payment', 'platal',
                         'profile', 'register', 'search', 'stats', 'admin',
                         'newsletter');
    $platal->run();

    exit;
}

/*** WIKI CODE ***/

require_once 'wiki.inc.php';

$n = wiki_pagename();
if (!$n) {
    pl_redirect('');
}

new_skinned_page('core/wiki.tpl');
$perms = wiki_get_perms($n);

if (Env::v('display') == 'light') {
    $page->assign('simple', true);
}

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
$page->assign('perms_opts', wiki_perms_options());

$page->assign('canedit',    wiki_may_have_perms($perms[1]));
$page->assign('has_perms',  wiki_may_have_perms('admin'));

$page->assign('wikipage', str_replace('.', '/', $n));
if ($perms[1] == 'admin' && !Env::v('action')) {
    $page->assign('pmwiki_cache', $wiki_cache);
} else {
    $page->assign('pmwiki',   $wikiAll);
    $page->assign('text', true);
}
$page->addCssLink('wiki.css');
$page->addJsLink('wiki.js');

$page->run();

?>
