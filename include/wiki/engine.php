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

require_once 'wiki.inc.php';

$n    = wiki_pagename();
if (!$n) {
    pl_redirect('');
}

new_skinned_page('core/wiki.tpl');
$perms = wiki_get_perms($n);
$feed  = false;

// Check user perms
switch (Env::v('action')) {
  case 'rss': case 'atom': case 'sdf': case 'dc':
    wiki_apply_feed_perms($perms['0']);  
    $feed = true;
  case '': case 'search': 
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

// Generate cache even if we don't have access rights
$wiki_cache   = wiki_work_dir().'/cache_'.wiki_filename($n).'.tpl';
$cache_exists = file_exists($wiki_cache);
if (Env::v('action') || !$cache_exists) {
    if ($cache_exists && !$feed) {
        unlink($wiki_cache);
        $files = glob($globals->spoolroot . '/spool/templates_c/*cache_' . wiki_filename($n) . '.tpl*');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    // we leave pmwiki do whatever it wants and store everything
    ob_start();
    require_once($globals->spoolroot.'/wiki/pmwiki.php');

    $wikiAll = ob_get_clean();
    // the pmwiki skin we are using (almost empty) has these keywords:
    $i = strpos($wikiAll, "<!--/HeaderText-->");
    $j = strpos($wikiAll, "<!--/PageLeftFmt-->", $i);
}

$wiki_exists = file_exists(wiki_work_dir() . '/' . wiki_filename($n));

if ($feed) {
    $wikiAll = str_replace('dc:contributor', 'author', $wikiAll);
    $wikiAll = preg_replace('!<author>.*?\..*?\.(\d{4})\|(.*?)</author>!u', '<author>$2 (X$1)</author>', $wikiAll);
} elseif (Env::v('action')) {
    $page->assign('xorg_extra_header', substr($wikiAll, 0, $i));
    $wikiAll = substr($wikiAll, $j);
} else {
    if (!$cache_exists && $wiki_exists) {
        $wikiAll = substr($wikiAll, $j);
        wiki_putfile($wiki_cache, $wikiAll);
    } elseif ($cache_exists) {
        $wikiAll = file_get_contents($wiki_cache);
    } elseif (S::has_perms()) {
        $wikiAll = "<p>La page de wiki $n n'existe pas. "
                 . "Il te suffit de <a href='" . str_replace('.', '/', $n) . "?action=edit'>l'éditer</a></p>";
    } else {
        $page->changeTpl('core/404.tpl');
    }
}

if ($feed) {
    echo $wikiAll;
    pl_clear_errors();
    exit;
}

// Check user perms
wiki_apply_perms($perms[0]);

$page->assign('perms', $perms);
$page->assign('perms_opts', wiki_perms_options());

$page->assign('canedit',    wiki_may_have_perms($perms[1]));
$page->assign('has_perms',  wiki_may_have_perms('admin'));

$page->assign('wikipage', str_replace('.', '/', $n));
if (!$feed && $perms[1] == 'admin' && !Env::v('action') && $wiki_exists) {
    $page->assign('pmwiki_cache', $wiki_cache);
} else {
    $page->assign('pmwiki',   $wikiAll);
    $page->assign('text', true);
}
$page->addCssLink('wiki.css');
$page->addJsLink('wiki.js');
if (!Env::v('action')) {
    $url = '/' . str_replace('.', '/', $n) . '?action=rss';
    if (S::logged()) {
        $url .= '&user=' . S::v('forlife') . '&hash=' . S::v('core_rss_hash');
    } 
    $page->setRssLink($n, $url);
}

$page->run();

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
