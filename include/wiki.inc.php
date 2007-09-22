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

function wiki_pagename()
{
    if (!Get::v('n')) {
        return null;
    }

    $words = explode('/', trim(Get::v('n'), '/'));
    if (count($words) == 2) {
        return join('.', $words);
    }

    array_unshift($words, $words[0]);
    $b = array_pop($words);
    $a = array_pop($words);

    pl_redirect($a.'/'.$b);
}

function wiki_filename($s)
{
    if (@iconv('utf-8', 'utf-8', $s) == $s) {
        return $s;
    }
    return utf8_encode($s);
}

function wiki_work_dir()
{
    global $globals;
    return $globals->spoolroot.'/spool/wiki.d';
}

function wiki_clear_all_cache()
{
    system('rm -f '.wiki_work_dir().'/cache_*');
}

function wiki_perms_options() {
    return array('public' => 'Public', 'logged' => 'Connecté',
                  'mdp' => 'Authentifié', 'admin' => 'Admin');
}

function wiki_get_perms($n)
{
    $file  = wiki_work_dir().'/'.wiki_filename(str_replace('/', '.', $n));
    $lines = explode("\n", @file_get_contents($file));
    foreach ($lines as $line) {
        @list($k, $v) = explode('=', $line, 2);
        if ($k == 'platal_perms') {
            return explode(':', $v);
        }
    }
    return array('logged', 'admin');
}

function wiki_putfile($f, $s)
{
    $fp = fopen($f, 'w');
    fputs($fp, $s);
    fclose($fp);
}

function wiki_set_perms($n, $pr, $pw)
{
    $file  = wiki_work_dir().'/'.wiki_filename(str_replace('/', '.', $n));
    if (!file_exists($file)) {
        return false;
    }

    $p = $pr . ':' . $pw;

    $lines = explode("\n", file_get_contents($file));
    foreach ($lines as $i => $line) {
        list($k, $v) = explode('=', $line, 2);
        if ($k == 'platal_perms') {
            $lines[$i] = 'platal_perms='.$p;
            wiki_putfile($file, join("\n", $lines));
            return true;
        }
    }

    array_splice($lines, 1, 0, array('platal_perms='.$p));
    wiki_putfile($file, join("\n", $lines));
    return true;
}

function wiki_may_have_perms($perm) {
    switch ($perm) {
      case 'public': return true;
      case 'logged': return S::logged();
      case 'mdp':    return S::logged();
      default:       return S::has_perms();
    }
}

function wiki_apply_feed_perms($perm)
{
    if ($perm == 'public') {
        return;
    }

    require_once 'rss.inc.php';
    $uid = init_rss(null, Env::v('user'), Env::v('hash'));
    $res = XDB::query('SELECT user_id AS uid, IF (nom_usage <> \'\', nom_usage, nom) AS nom, prenom, perms
                         FROM auth_user_md5
                        WHERE user_id = {?}', $uid);
    if (!$res->numRows()) {
        exit;
    }
    $table = $res->fetchOneAssoc();
    $_SESSION = array_merge($_SESSION, $table, array('forlife' => Env::v('user')));
    require_once 'xorg/session.inc.php';
    $_SESSION['perms'] =& XorgSession::make_perms($_SESSION['perms']);
    if ($perm == 'logged' || $_SESSION['perms']->hasFlag('admin')) {
        return;
    }
    exit;
}

function wiki_apply_perms($perm) {
    global $page, $platal, $globals;

    switch ($perm) {
      case 'public':
        return;

      case 'logged':
        if (!call_user_func(array($globals->session, 'doAuthCookie'))) {
            $platal =  empty($GLOBALS['IS_XNET_SITE']) ? new Platal() : new Xnet();
            $platal->force_login($page);
        }
        return;

      default:
        if (!call_user_func(array($globals->session, 'doAuth'))) {
            $platal = empty($GLOBALS['IS_XNET_SITE']) ? new Platal() : new Xnet();
            $platal->force_login($page);
        }
        if ($perm == 'admin') {
            check_perms();
        }
        return;
    }
}

function wiki_require_page($pagename)
{
    global $globals;
    $pagename_slashes = str_replace('.','/',$pagename);
    $pagename_dots = str_replace('/','.',$pagename);
    if (is_file(wiki_work_dir().'/cache_'.$pagename_dots.'.tpl')) {
        return;
    }
    system('wget --no-check-certificate '. escapeshellarg($globals->baseurl.'/'.$pagename_slashes) . ' -O /dev/null');
}

function wiki_delete_page($pagename)
{
    $pagename_dots = str_replace('/','.',$pagename);
    if (!strpos($pagename_dots, '.')) {
        return false;
    }
    $file  = wiki_work_dir().'/'.wiki_filename($pagename_dots);
    $cachefile = wiki_work_dir().'/cache_'.$pagename_dots.'.tpl';
    if (is_file($cachefile)) {
        unlink($cachefile);
    }
    if (!is_file($file)) {
        return false;
    }
    unlink($file);
    return true;
}

function wiki_links_in_line($line, $groupname)
{
    $links = array();
    if (preg_match_all('@\[\[([^~][^\]\|\?#]*)((\?|#)[^\]\|]+)?(\\|[^\]]+)?\]\]@', $line, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[1] as $j => $link) if (!preg_match('@http://@', $link[0])) {
            $mylink = str_replace('/','.',trim($link[0]));
            $sup = trim(substr($matches[2][$j][0],1));
            $alt = trim(substr($matches[4][$j][0],1));
            $newlink = str_replace(' ','',ucwords($mylink));
            if (strpos($newlink,'.') === false) {
                $newlink = $groupname.'.'.$newlink;
            }
            if (!$alt && $mylink != $newlink) {
                $alt = trim($link[0]);
            }
            $links[] = array(
              'pos' => $matches[0][$j][1],
              'size' => strlen($matches[0][$j][0]),
              'href' => $newlink,
              'sup' => $sup,
              'alt' => $alt,
              'group' => substr($mylink, 0, strpos($mylink, '.')));
        }
    }
    return $links;
}

function wiki_rename_page($pagename, $newname, $changeLinks = true)
{
    $pagename_dots = str_replace('/','.',$pagename);
    $newname_dots = str_replace('/','.',$newname);
    if (!strpos($pagename_dots, '.') || !strpos($newname_dots, '.')) {
        return false;
    }
    $groupname = substr($pagename_dots, 0, strpos($pagename_dots,'.'));
    $newgroupname = substr($newname_dots, 0, strpos($pagename_dots,'.'));

    $file  = wiki_work_dir().'/'.wiki_filename($pagename_dots);
    $newfile  = wiki_work_dir().'/'.wiki_filename($newname_dots);
    if (!is_file($file)) {
        // old page doesn't exist
        return false;
    }
    if (!rename($file, $newfile)) {
        // impossible to renama page
        return false;
    }

    if (!$changeLinks) {
        return true;
    }

    $changedLinks = 0;
    // change name inside this folder and ingroup links if changing group
    $lines = explode("\n", file_get_contents($newfile));
    $changed = false;
    foreach ($lines as $i => $line) {
        list($k, $v) = explode('=', $line, 2);
        if ($k == 'name' && $v == $pagename_dots) {
            $lines[$i] = 'name='.$newname_dots;
            $changed = true;
        } else if ($groupname != $newgroupname) {
            $links = wiki_links_in_line($line, $groupname);
            $newline = ''; $last = 0;
            foreach ($links as $link) if ($link['group'] == $groupname) {
                $newline .= substr($line, $last, $link['pos']);
                $newline .= '[['.$link['href'].$link['sup'].($link['alt']?(' |'.$link['alt']):'').']]';
                $last = $link['pos']+$link['size'];
                $changedLinks++;
            }
            if ($last != 0) {
                $newline .= substr($line, $last);
                $lines[$i] = $newline;
                $changed = true;
            }
        }
    }
    wiki_putfile($newfile, join("\n", $lines));

    // change wiki links in all wiki pages
    $endname = substr($pagename_dots, strpos($pagename_dots,'.')+1);
    $pages = array();
    exec("grep ".$endname."  ".wiki_work_dir()."/* -sc", $pages);
    foreach($pages as $line) {
        if (preg_match('%/([^/:]+):([0-9]+)$%', $line, $vals) && $vals[2] > 0) {
            $inpage = $vals[1];
            $lines = explode("\n", file_get_contents(wiki_work_dir().'/'.$inpage));
            $changed = false;
            // find all wiki links in page and change if linked to this page
            foreach ($lines as $i => $line) {
                $links = wiki_links_in_line($line, substr($inpage, 0, strpos($inpage, '.')));
                $newline = ''; $last = 0;
                foreach ($links as $link) {
                    if ($link['href'] == $pagename_dots) {
                        $newline .= substr($line, $last, $link['pos']);
                        $newline .= '[['.$newname_dots.$link['sup'].($link['alt']?(' |'.$link['alt']):'').']]';
                        $last = $link['pos']+$link['size'];
                        $changedLinks++;
                    }
                }
                if ($last != 0) {
                    $newline .= substr($line, $last);
                    $lines[$i] = $newline;
                    $changed = true;
                }
            }
            if ($changed)
            {
                wiki_putfile(wiki_work_dir().'/'.$inpage, join("\n", $lines));
            }
        }
    }
    if ($changedLinks > 0) {
        return $changedLinks;
    }
    return true;
}

function wiki_rename_folder($pagename, $newname, $changeLinks = true)
{
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
