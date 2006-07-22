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
    if (!Get::get('n')) {
        return null;
    }

    $words = explode('/', trim(Get::get('n'), '/'));
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

function get_perms($n)
{
    $file  = wiki_work_dir().'/'.str_replace('/', '.', $n);
    $lines = explode("\n", file_get_contents($file));
    foreach ($lines as $line) {
        list($k, $v) = explode('=', $line, 2);
        if ($k == 'platal_perms') {
            return explode(':', $v);
        }
    }
    return array('logged', 'admin');
}

function wiki_apply_perms($perm) {
    global $page, $platal;

    switch ($perm) {
      case 'public':
        return;

      case 'logged':
        if (!XorgSession::doAuthCookie()) {
            $platal = new Platal();
            $platal->force_login($page);
        }
        return;

      default:
        if (!XorgSession::doAuth()) {
            $platal = new Platal();
            $platal->force_login($page);
        }
        if ($perm == 'admin') {
            check_perms();
        }
        return;
    }
}

?>
