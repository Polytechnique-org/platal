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

new_skinned_page('wiki.tpl');
if (!S::identified()) {
    XorgSession::doAuth();
}

require_once 'wiki.inc.php';

if ($n = wiki_pagename()) {
    $wiki_template  = wiki_work_dir().'/cache_'.$n.'.tpl';
    $tmpfile_exists = file_exists($wiki_template);

    if (Env::get('action') || !$tmpfile_exists) {
        if ($tmpfile_exists) {
            @unlink($wiki_template);
            $page->clear_compiled_tpl($wiki_template);
        }

        // we leave pmwiki do whatever it wants and store everything
        ob_start();
        require_once($globals->spoolroot.'/wiki/pmwiki.php');

        $wikiAll = ob_get_clean();
        // the pmwiki skin we are using (almost empty) has these keywords:
        $i = strpos($wikiAll, "<!--/HeaderText-->");
        $j = strpos($wikiAll, "<!--/PageLeftFmt-->", $i);
    }

    if (Env::get('action')) {
        // clean old tmp files
        wiki_clean_tmp();

        // create new tmp files with editing page from wiki engine
        $page->assign('xorg_extra_header', substr($wikiAll, 0, $i));
        $wikiAll = substr($wikiAll, $j);
        $wiki_template = wiki_create_tmp($wikiAll);
    } else {
        if (!$tmpfile_exists) {
            $f = fopen($wiki_template, 'w');
            $wikiAll = substr($wikiAll, $j);
            fputs($f, $wikiAll);
            fclose($f);
        } else {
            $wikiAll = file_get_contents($wiki_template);
        }
    }

    $page->assign('wikipage', str_replace('.', '/', $n));
}

$page->assign('pmwiki', $wikiAll);
wiki_assign_auth();
$page->addCssLink('css/wiki.css');

$page->run();
?>
