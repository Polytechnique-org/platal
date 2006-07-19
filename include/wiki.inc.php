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

function wiki_pagename() {
    if (!Env::get('n')) {
        return null;
    }

    $words = explode('/', Env::get('n'));
    if (count($words) == 2) {
        return join('.', $words);
    }

    array_unshift($words, $words[0]);
    $b = array_pop($words);
    $a = array_pop($words);

    global $globals;
    redirect($globals->baseurl.'/'.$a.'/'.$b);
}

function wiki_work_dir() {
    global $globals;
    return $globals->spoolroot.'/spool/wiki.d';
}

function wiki_clear_all_cache()
{
    system("rm -f ".wiki_work_dir()."/cache_*");
}

// editing pages are not static but used templates too, so we used
// temp template files containing result from wiki
function wiki_create_tmp($content) {
    $tmpfile = tempnam(wiki_work_dir(), "temp_");
    $f = fopen($tmpfile, 'w');
    fputs($f, $content);
    fclose($f);
    return $tmpfile;
}

function wiki_clean_tmp() {
    // clean old tmp files (more than one hour)
    $wiki_work_dir = wiki_work_dir();
    $dh = opendir(wiki_work_dir());
    $time = time();
    while (($file = readdir($dh)) !== false) {
        if (strpos($file, 'temp_') === 0) {
            $created = filectime($wiki_work_dir.'/'.$file);
            if ($time-$created > 60 * 60)
                @unlink($wiki_work_dir.'/'.$file);
        }
    }
}

function wiki_assign_auth() {
    global $page, $wiki_auths;
    $page->assign('true',       true);
    $page->assign('public',     true);
    $page->assign('logged',     S::logged());
    $page->assign('identified', S::identified());
    $page->assign('has_perms',  S::has_perms());
}

// cannot be in a function because pmwiki use all vars as if it was globals
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
        $page->assign('xorg_extra_header', substr($wikiAll, 0, $i));

        // create new tmp files with editing page from wiki engine
        $wiki_template = wiki_create_tmp(substr($wikiAll, $j));
    } else {
        if (!$tmpfile_exists) {
            $f = fopen($wiki_template, 'w');
            fputs($f, substr($wikiAll, $j));
            fclose($f);
        }
    }
}
?>
