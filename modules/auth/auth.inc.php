<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
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

function gpex_prepare_param($name, $val, &$to_hash,  $charset)
{
    $val = iconv('UTF-8', $charset, $val);
    $to_hash .= $val;
    return '&' . $name . '=' . urlencode($val);
}

function gpex_make($chlg, $privkey, $datafields, $charset)
{
    $tohash   = "1$chlg$privkey";
    $params   = "";
    $fieldarr = explode(',', $datafields);

    $user =& S::user();
    if ($user->hasProfile()) {
        /* Transition table for authentification. */
        $personnal_data = $user->profile()->data();
        $personnal_data['full_promo'] = $personnal_data['promo'];
        $personnal_data['promo'] = $personnal_data['entry_year'];
        $personnal_data['matricule'] = $personnal_data['xorg_id'];
        $personnal_data['matricule_ax'] = $personnal_data['ax_id'];
        $personnal_data['promo_sortie'] = $personnal_data['grad_year'];
        $personnal_data['nationalite'] = $personnal_data['nationality1'];
        $personnal_data['naissance'] = $personnal_data['birthdate'];
        $personnal_data['deces'] = $personnal_data['deathdate'];
        $personnal_data['nom'] = $personnal_data['lastname'];
        $personnal_data['prenom'] = $personnal_data['firstname'];
        $personnal_data['flags'] = $user->profile()->isFemale() ? 'femme' : '';
    } else {
        // Missing fields: promo, entry_year, grad_year, ax_id, xorg_id, forlife
        $personnal_data = array(
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'sex' => $user->gender
        );
    }

    foreach ($fieldarr as $val) {
        // Determine the requested value, and add it to the answer.
        if ($val == 'perms') {
            $params .= gpex_prepare_param($val, S::admin() ? 'admin' : 'user', $tohash, $charset);
        } else if ($val == 'forlife') {
            $params .= gpex_prepare_param($val, S::v('hruid'), $tohash, $charset);
        } else if (S::has($val)) {
            $params .= gpex_prepare_param($val, S::v($val), $tohash, $charset);
        } else if (isset($personnal_data[$val])) {
            $params .= gpex_prepare_param($val, $personnal_data[$val], $tohash, $charset);
        } else if ($val == 'username') {
            $min_username = XDB::fetchOneCell('SELECT  email
                                                 FROM  email_source_account
                                                WHERE  uid = {?} AND FIND_IN_SET(\'bestalias\', flags)',
                                              S::i('uid'));
            $params .= gpex_prepare_param($val, (is_null($min_username) ? '' : $min_username), $tohash, $charset);
        } else if ($val == 'grpauth') {
            if (isset($_GET['group'])) {
                $res = XDB::query("SELECT  perms
                                     FROM  group_members
                               INNER JOIN  groups ON(id = asso_id)
                                    WHERE  uid = {?} AND diminutif = {?}",
                                  S::v('uid'), $_GET['group']);
                $perms = $res->fetchOneCell();
            } else {
                // if no group asked, return main rights
                $perms = S::admin() ? 'admin' : 'membre';
            }
            $params .= gpex_prepare_param($val, $perms, $tohash, $charset);
        } else {
            $params .= gpex_prepare_param($val, '', $tohash, $charset);
        }
    }
    $tohash .= "1";
    $auth = md5($tohash);
    return array($auth, "&auth=" . $auth . $params);
}

/* cree les parametres de l'URL de retour avec les champs demandes */
function gpex_make_params($chlg, $privkey, $datafields, $charset)
{
    list ($auth, $param) = gpex_make($chlg, $privkey, $datafields, $charset);
    return $param;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
