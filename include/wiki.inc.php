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

function wiki_work_dir() {
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
    $file  = wiki_work_dir().'/'.str_replace('/', '.', $n);
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
    $file  = wiki_work_dir().'/'.str_replace('/', '.', $n);
    if (!file_exists($file))
        return false;

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

function wiki_apply_perms($perm) {
    global $page, $platal, $globals;

    switch ($perm) {
      case 'public':
        return;

      case 'logged':
        if (!call_user_func(array($globals->session, 'doAuthCookie'))) {
            $platal = new Platal();
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
    if (is_file(wiki_work_dir().'/cache_'.$pagename_dots.'.tpl')) return;
    system('wget '.$globals->baseurl.'/'.$pagename_slashes.' -O /dev/null');
}

?>
