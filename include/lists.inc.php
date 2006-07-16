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

// {{{ import class definitions

require_once 'platal/xmlrpc-client.inc.php';

// }}}
// {{{ function lists_xmlrpc

function &lists_xmlrpc($uid, $pass, $fqdn=null)
{
    global $globals;

    $dom = is_null($fqdn) ? $globals->mail->domain : $fqdn;
    $url = "http://$uid:$pass@{$globals->lists->rpchost}:{$globals->lists->rpcport}/$dom";
    $client = new xmlrpc_client($url);
    return $client;
}

// }}}
// {{{ function list_sort_owners

function list_sort_owners(&$members, $tri_promo = true) {
    global $globals;

    $membres = Array();
    
    foreach($members as $mem) {
        list($m, $dom) = explode('@',$mem);
        if ($dom == $globals->mail->domain || $dom == $globals->mail->domain2) {
            $res = XDB::query('SELECT  prenom,IF(nom_usage="", nom, nom_usage), promo
                                           FROM  auth_user_md5 AS u
                                     INNER JOIN  aliases AS a ON u.user_id = a.id
                                          WHERE  a.alias = {?}', $m);
            if(list($prenom, $nom, $promo) = $res->fetchOneRow()) {
                $key = $tri_promo ? $promo : strtoupper($nom{0});
                $membres[$key][$nom.$m] = Array('n' => "$prenom $nom", 'l' => $m);
            } else {
                $membres[0][] = Array('l' => $mem);
            }
        } else {
            $res = XDB::query('SELECT prenom, nom FROM groupex.membres WHERE email={?}', $mem);
            if (list($prenom, $nom) = $res->fetchOneRow()) {
                $key = $tri_promo ? 0 : strtoupper($nom{0});
                $membres[$key][$nom.$m] = Array('n' => "$prenom $nom", 'l' => $mem);
            } else {
                $membres[0][] = Array('l' => $mem);
            }
        }
    }
    
    ksort($membres);
    foreach($membres as $key=>$val) ksort($membres[$key]);
    return $membres;
}

// }}}
// {{{ function list_sort_members

function list_sort_members(&$members, $tri_promo = true) {
    $pi1 = create_function('$arr', 'return $arr[1];');
    return list_sort_owners(array_map($pi1, $members), $tri_promo);
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
