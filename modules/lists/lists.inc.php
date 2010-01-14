<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

// {{{ function list_sort_owners

function list_sort_owners(&$members, $tri_promo = true) {
    global $globals;

    $membres = Array();

    foreach($members as $mem) {
        list($m, $dom) = explode('@', $mem);
        $info = list_fetch_name($mem);
        if (!isset($info['uid']) || is_null($info['uid'])) {
            $membres[0][] = array('l' => $mem, 'p' => (!$tri_promo ? 'inconnue' : null));
        } else {
            $uid = $info['uid'];
            $nom = $info['nom'];
            $prenom = $info['prenom'];
            $promo = $info['promo'];
            $broken = $info['lost'];
            $key = $tri_promo ? ($promo != 'non-X' ? $promo : 0) : mb_strtoupper(@$nom{0});
            if ($tri_promo) {
                $promo = null;
            }
            $membres[$key][$nom.$m] = Array('n' => "$prenom $nom", 'l' => $m, 'p' => $promo, 'x' => $uid, 'b' => $broken);
        }
    }

    ksort($membres);
    foreach($membres as $key=>$val) ksort($membres[$key]);
    return $membres;
}

// }}}
// {{{ list_extract_member

function _list_extract_member($member)
{
    return $member[1];
}

function list_extract_members($members)
{
    return array_map('_list_extract_member', $members);
}

// }}}
// {{{ function list_sort_members

function list_sort_members($members, $tri_promo = true)
{
    $m = list_extract_members($members);
    return list_sort_owners($m, $tri_promo);
}

// }}}
// {{{ function list_fetch_names

function list_fetch_name($member)
{
    global $globals;
    list($m, $dom) = explode('@', $member);
    if ($dom == $globals->mail->domain || $dom == $globals->mail->domain2) {
        $res = XDB::query('SELECT  u.user_id AS uid, prenom AS prenom, IF(nom_usage="", nom, nom_usage) AS nom,
                                   promo AS promo,
                                   (e.uid IS NULL AND FIND_IN_SET("googleapps", u.mail_storage) = 0) AS lost
                             FROM  auth_user_md5 AS u
                       INNER JOIN  aliases AS a ON u.user_id = a.id
                        LEFT JOIN  emails AS e ON (e.flags = "active" AND e.uid = u.user_id)
                            WHERE  a.alias = {?}
                         GROUP BY  u.user_id', $m);
    } else {
        $res = XDB::query('SELECT m2.uid AS uid,
                                  IF(m2.origine="X", u.prenom, m1.prenom) AS prenom,
                                  IF(m2.origine="X", u.nom, m1.nom) AS nom,
                                  IF(m2.origine="X", u.promo, "non-X") AS promo,
                                  0 AS lost
                             FROM #groupex#.membres     AS m1
                        LEFT JOIN #groupex#.membres     AS m2 ON(m1.email=m2.email AND m2.asso_id={?})
                        LEFT JOIN #x4dat#.auth_user_md5 AS u  ON(m2.origine = "X" AND m2.uid = u.user_id)
                            WHERE m1.email={?}', $globals->asso('id'), $member);
    }
    if ($res->numRows() == 0) {
        return array('email' => $member);
    } else {
        return array_merge(array('email' => $member), $res->fetchOneAssoc());
    }
}

function list_fetch_names($members)
{
    $res = array();
    foreach ($members as $member) {
        $res[] = list_fetch_name($member);
    }
    return $res;
}

// }}}
// {{{ function list_header_decode

function _list_header_decode($charset, $c, $str) {
    $s = ($c == 'Q' || $c == 'q') ? quoted_printable_decode($str) : base64_decode($str);
    $s = iconv($charset, 'UTF-8', $s);
    return str_replace('_', ' ', $s);
}

function list_header_decode($value) {
    if (!$value) {
        return "[pas de sujet]";
    }
    $val = preg_replace('/(=\?[^?]*\?[BQbq]\?[^?]*\?=) (=\?[^?]*\?[BQbq]\?[^?]*\?=)/', '\1\2', $value);
    return preg_replace('/=\?([^?]*)\?([BQbq])\?([^?]*)\?=/e', '_list_header_decode("\1", "\2", "\3")', $val);
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
