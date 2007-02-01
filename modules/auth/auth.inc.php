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

/* cree le champs "auth" renvoye au Groupe X */
function gpex_make_auth($chlg, $privkey, $datafields) {
    $fieldarr = explode(",",$datafields);
    $tohash   = "1$chlg$privkey";

    $res = XDB::query("SELECT matricule, matricule_ax, promo,
                                        promo_sortie, flags, deces, nom,
                                        prenom, nationalite, section,
                                        naissance
                                   FROM auth_user_md5 WHERE user_id = {?}",
                                S::v('uid'));
    $personnal_data = $res->fetchOneAssoc();

    foreach ($fieldarr as $val) {
        /* on verifie qu'on n'a pas demandÃ© une variable inexistante ! */
        if (S::has($val)) {
            $tohash .= S::v($val);
            $params .= "&$val=".S::v($val);
        } else if (isset($personnal_data[$val])) {
            $tohash .= $personnal_data[$val];
            $params .= "&$val=".$personnal_data[$val];
        } else if ($val == 'username') {
            $res = XDB::query("SELECT alias FROM aliases
                                          WHERE id = {?} AND FIND_IN_SET('bestalias', flags)",
                                        S::v('uid'));
            $min_username = $res->fetchOneCell();
            $tohash      .= $min_username;
        }
    }
    $tohash .= "1";
    return md5($tohash);
}

/* cree les parametres de l'URL de retour avec les champs demandes */
function gpex_make_params($chlg, $privkey, $datafields) {
    $params   = "&auth=".gpex_make_auth($chlg, $privkey, $datafields);

    $res = XDB::query("SELECT matricule, matricule_ax, promo,
                              promo_sortie, flags, deces, nom,
                              prenom, nationalite, section,
                              naissance
                         FROM auth_user_md5 WHERE user_id = {?}",
                      S::v('uid'));
    $personnal_data = $res->fetchOneAssoc();

    $fieldarr = explode(",",$datafields);

    foreach ($fieldarr as $val) {
        if (S::has($val)) {
            $tohash .= S::v($val);
            $params .= "&$val=".S::v($val);
        } else if (isset($personnal_data[$val])) {
            $tohash .= $personnal_data[$val];
            $params .= "&$val=".$personnal_data[$val];
        } else if ($val == 'username') {
            $res = XDB::query("SELECT alias FROM aliases 
                                          WHERE id = {?} AND FIND_IN_SET('bestalias', flags)",
                                        S::v('uid'));
            $min_username = $res->fetchOneCell();
            $params      .= "&$val=".$min_username;
        }
    }
    return $params;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
