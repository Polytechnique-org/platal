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

function list_sort_owners(&$members, $tri_promo = true)
{
    global $globals;

    $membres = Array();

    foreach($members as $mem) {
        $user = User::getSilent($mem);
        if (!$user) {
            $membres[0][] = array('l' => $mem, 'p' => (!$tri_promo ? 'inconnue' : null));
        } else {
            $uid = $user->id();
            $nom = $user->directoryName();
            $promo = $user->promo();
            if (!$promo) {
                $promo = 'non-X';
            }
            $key = $tri_promo ? ($promo != 'non-X' ? $promo : 0) : strtoupper(@$nom{0});
            if ($tri_promo) {
                $promo = null;
            }
            $membres[$key][$nom.$mem] = array('n' => $nom, 'l' => $mem, 'p' => $promo, 'x' => $uid);
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

function list_fetch_names($members)
{
    $res = array();
    foreach ($members as $member) {
        $user = User::getSilent($member);
        if (!$user) {
            $res[] = $member;
        } else {
            $res[] = $user->fullName();
        }
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
