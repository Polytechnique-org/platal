<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

function list_sort_owners($emails, $tri_promo = true)
{
    global $globals;

    // $membres' structure is the following: $sortKey => $key => $listMember
    $membres = array();
    $seen = array();

    $members = array();

    $uf = new UserFilter(new UFC_Email($emails));
    $it = $uf->iterUsers();
    while ($u = $it->next()) {
        $members[$u->uid] = array(
            'user' => $u,
            'profile' => null,
            'email' => $u->forlifeEmail());
        $seen[] = $u->forlifeEmail();
    }

    $pf = new ProfileFilter(new UFC_Email($emails));
    $it = $pf->iterProfiles();
    while ($p = $it->next()) {
        $members[$p->owner_id]['user']->setPrefetchedProfile($p);
        $members[$p->owner_id]['profile'] = $p;
    }

    foreach ($emails as $email) {
        if (!in_array($email, $seen)) {
            $seen[] = $email;
            $members[$email] = array('user' => null, 'profile' => null,
                'email' => $email);
        }
    }

    // $members is now an array of uid => {'user': User or null, 'email': $email}

    // $sorted_members is an array of $sortKey1 => $sortKey2 => {User, email}
    $sorted_members = array();

    foreach($members as $member) {
        if (is_null($member['user'])) {
            $category = 'AAAAA';
            $name = $member['email'];
        } else {
            $category = $member['user']->category();
            $name = $member['user']->directoryName();
        }
        if (empty($category)) {
            $category = "AAAAA";
        }

        $main_sort_key = $tri_promo ? $category : strtoupper($name{0});
        $alt_sort_key = $name;

        if (!array_key_exists($main_sort_key, $sorted_members)) {
            $sorted_members[$main_sort_key] = array();
        }

        $sorted_members[$main_sort_key][$alt_sort_key] = $member;
    }

    uksort($sorted_members, 'strcasecmp');
    foreach($sorted_members as &$subsorted_members)  {
        uksort($subsorted_members, 'strcasecmp');
    }
    return $sorted_members;
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
// {{{ function list_fetch_basic_info

function list_fetch_basic_info($members)
{
    $res = array();
    foreach ($members as $member) {
        $user = User::getSilent($member);
        if (!$user) {
            $res[] = $member . ';;';
        } else {
            $res[] = $user->lastName() . ';' . $user->firstName() . ';' . $user->promo();
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
