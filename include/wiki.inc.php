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
$wikisites = array('xorg','xnet');

function wiki_pagename() {
    $n = str_replace('/', '.', Env::get('n', false));
    if (!$n) {
        return null;
    }
    $keywords = explode('.', $n);
    $count = count($keywords);
    if ($count == 1)
        $n = $keywords[0].".".$keywords[0];
    else
        $n = $keywords[$count - 2].".".$keywords[$count - 1];
    global $globals;
    if (($urln = str_replace('.', '/', $n)) != Env::get('n') && $n != Env::get('n'))
        redirect($globals->baseurl.'/'.$urln);
    $_REQUEST['n'] = $n;
    return $n;
}

function wiki_work_dir() {
    global $globals;
    return realpath($globals->spoolroot.'htdocs/'.$globals->wiki->workdir);
}

function wiki_template($n) {
    global $wikisite;
    return $tpl = wiki_work_dir().'/cache_'.$wikisite.'_'.$n.'.tpl';
}

// several files are used for wiki :
// - spool/wiki.d/PageName                           : the wiki page
// - spool/wiki.d/cache_PageName.tpl                 : the template cache
// - spool/templates_c/%%...%%cache_PageName.tpl.php : the PHP from Smarty
function wiki_clear_cache($n) {
    global $page, $wikisite, $wikisites;
    $oldwikisite = $wikisite;
    foreach ($wikisites as $s) {
        $wikisite = $s;
        $tpl = wiki_template($n);
        @unlink($tpl);
        $page->clear_compiled_tpl($tpl);
    }
    $wikisite = $oldwikisite;
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
    $page->assign('logged', S::logged());
    $page->assign('identified', S::identified());
    $page->assign('has_perms', S::has_perms());
    $page->assign('public', true);
    $page->assign('wiki_admin', S::has_perms() && S::identified());
}

// cannot be in a function because pmwiki use all vars as if it was globals
//function new_wiki_page() {
    // the wiki keword is given in the n var
    if ( $n = wiki_pagename() )
    {

        $wiki_template = wiki_template($n);
        $tmpfile_exists = file_exists($wiki_template);

        // don't recreate the tpl if it already exists
        if (Env::get('action') || !$tmpfile_exists)
        {
            if ($tmpfile_exists) {
                wiki_clear_cache($n);
            }

            // we leave pmwiki do whatever it wants and store everything
            ob_start();
            require_once($globals->spoolroot.'/'.$globals->wiki->wikidir.'/pmwiki.php');

            $wikiAll = ob_get_clean();
            // the pmwiki skin we are using (almost empty) has these keywords:
            $i = strpos($wikiAll, "<!--/HeaderText-->");
            $j = strpos($wikiAll, "<!--/PageLeftFmt-->", $i);

        }
        if (Env::get('action'))
        {
            // clean old tmp files
            wiki_clean_tmp();
            $page->assign('xorg_extra_header', substr($wikiAll, 0, $i));
            // create new tmp files with editing page from wiki engine
            $wiki_template = wiki_create_tmp(substr($wikiAll, $j));
        } else {
            if (!$tmpfile_exists)
            {
                $f = fopen($wiki_template, 'w');
                fputs($f, substr($wikiAll, $j));
                fclose($f);
            }
        } 
    }
   //return $wiki_template;    
//}
?>
