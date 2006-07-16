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

function _rss_encode_date($d) {
    if (preg_match('/^\d{14}$/', $d)) {
        $t = mktime(substr($d,8,2), substr($d,10,2), substr($d,12,2), substr($d,4,2), substr($d,6,2), substr($d,0,4));
    } else {
        $t = strtotime($d);
    }
    return utf8_encode(date('r', $t));
}

function to_rss ($s)
{
    if(is_string($s)) {
        return utf8_encode($s);
    } else {
        return $s;
    }
}

function init_rss($template, $alias, $hash)
{
    global $page, $globals;
    new_nonhtml_page($template, AUTH_PUBLIC);
    $page->register_modifier('rss_date', '_rss_encode_date');
    $page->default_modifiers = Array('@to_rss');

    $res = XDB::query(
        'SELECT  a.id
           FROM  aliases         AS a
     INNER JOIN  auth_user_quick AS q ON ( a.id = q.user_id AND q.core_rss_hash = {?} )
          WHERE  a.alias = {?} AND a.type != "homonyme"', $hash, $alias);
    $uid = $res->fetchOneCell();

    if (empty($uid)) {
        exit;
    }

    header('Content-Type: application/rss+xml; charset=utf8');
    return $uid;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
