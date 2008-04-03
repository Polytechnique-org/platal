<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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
    return date('r', $t);
}

function init_rss($template, $alias, $hash, $require_uid = true)
{
    global $page;
    $page->changeTpl($template, NO_SKIN);
    $page->register_modifier('rss_date', '_rss_encode_date');

    $res = XDB::query(
        'SELECT  a.id
           FROM  aliases         AS a
     INNER JOIN  auth_user_md5   AS u ON (a.id = u.user_id AND u.perms IN ("admin", "user"))
     INNER JOIN  auth_user_quick AS q ON (a.id = q.user_id AND q.core_rss_hash = {?})
          WHERE  a.alias = {?} AND a.type != "homonyme"', $hash, $alias);
    $uid = $res->fetchOneCell();

    if (empty($uid)) {
        if ($require_uid) {
            exit;
        } else {
            $uid = null;
        }
    }

    if ($template) {
        header('Content-Type: application/rss+xml; charset=utf8');
    }
    return $uid;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
