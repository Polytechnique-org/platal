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

// {{{ function user_clear_all_subs()
/** kills the inscription of a user.
 * we still keep his birthdate, adresses, and personnal stuff
 * kills the entreprises, mentor, emails and lists subscription stuff
 */
function user_clear_all_subs($user_id, $really_del=true)
{
    // keep datas in : aliases, adresses, tels, profile_education, binets_ins, contacts, groupesx_ins, homonymes, identification_ax, photo
    // delete in     : competences_ins, emails, entreprises, langues_ins, mentor,
    //                 mentor_pays, mentor_secteurs, newsletter_ins, perte_pass, requests, user_changes, virtual_redirect, watch_sub
    // + delete maillists

    global $globals;
    $uid = intval($user_id);
    $user = User::getSilent($uid);
    list($alias) = explode('@', $user->forlifeEmail());

    // TODO: clear profile.
    $tables_to_clear = array('uid' => array('competences_ins', 'profile_job', 'langues_ins', 'profile_mentor_country',
                                            'profile_mentor_sector', 'profile_mentor', 'perte_pass', 'watch_sub'),
                             'user_id' => array('requests', 'user_changes'));

    if ($really_del) {
        array_push($tables_to_clear['uid'], 'emails', 'group_members', 'contacts', 'adresses', 'profile_phones',
                                            'photo', 'perte_pass', 'langues_ins', 'forum_subs', 'forum_profiles');
        array_push($tables_to_clear['user_id'], 'newsletter_ins', 'binets_ins');
        $tables_to_clear['id'] = array('aliases');
        $tables_to_clear['contact'] = array('contacts');
        XDB::execute("UPDATE accounts
                         SET registration_date = 0, state = 'pending', password = NULL, weak_password = NULL, token = NULL, is_admin = 0
                       WHERE uid = {?}", $uid);
        XDB::execute("DELETE virtual.* FROM virtual INNER JOIN virtual_redirect AS r USING(vid) WHERE redirect = {?}",
                     $alias.'@'.$globals->mail->domain);
        XDB::execute("DELETE virtual.* FROM virtual INNER JOIN virtual_redirect AS r USING(vid) WHERE redirect = {?}",
                     $alias.'@'.$globals->mail->domain2);
    } else {
        XDB::execute("UPDATE  accounts
                         SET  password = NULL, weak_password = NULL, token = NULL
                       WHERE  uid = {?}", $uid);
    }

    XDB::execute("DELETE FROM virtual_redirect WHERE redirect = {?}", $alias.'@'.$globals->mail->domain);
    XDB::execute("DELETE FROM virtual_redirect WHERE redirect = {?}", $alias.'@'.$globals->mail->domain2);

    /* TODO: handle both account and profile
    foreach ($tables_to_clear as $key=>&$tables) {
        foreach ($tables as $table) {
            XDB::execute("DELETE FROM $table WHERE $key={?}", $uid);
        }
    }*/

    $mmlist = new MMList($user);
    $mmlist->kill($alias, $really_del);

    // Deactivates, when available, the Google Apps account of the user.
    if ($globals->mailstorage->googleapps_domain) {
        require_once 'googleapps.inc.php';
        if (GoogleAppsAccount::account_status($uid)) {
            $account = new GoogleAppsAccount($user);
            $account->suspend();
        }
    }
}

// }}}
// {{{ function _user_reindex

function _user_reindex($uid, $keys)
{
    foreach ($keys as $i => $key) {
        if ($key['name'] == '') {
            continue;
        }
        $toks  = preg_split('/[ \'\-]+/', $key['name']);
        $token = "";
        $first = 5;
        while ($toks) {
            $token = strtolower(replace_accent(array_pop($toks) . $token));
            $score = ($toks ? 0 : 10 + $first) * ($key['score'] / 10);
            XDB::execute("REPLACE INTO  search_name (token, uid, soundex, score, flags)
                                VALUES  ({?}, {?}, {?}, {?}, {?})",
                         $token, $uid, soundex_fr($token), $score, $key['public']);
            $first = 0;
        }
    }
}

// }}}
// {{{ function user_reindex

function user_reindex($uid) {
    XDB::execute("DELETE FROM  search_name
                        WHERE  uid = {?}",
                 $uid);
    $res = XDB::iterator("SELECT  CONCAT(n.particle, n.name) AS name, e.score,
                                  FIND_IN_SET('public', e.flags) AS public
                            FROM  profile_name      AS n
                      INNER JOIN  profile_name_enum AS e ON (n.typeid = e.id)
                           WHERE  n.pid = {?}",
                         $uid);
    _user_reindex($uid, $res);
}

// }}}
// {{{ function get_X_mat
function get_X_mat($ourmat)
{
    if (!preg_match('/^[0-9]{8}$/', $ourmat)) {
        // le matricule de notre base doit comporter 8 chiffres
        return 0;
    }

    $year = intval(substr($ourmat, 0, 4));
    $rang = intval(substr($ourmat, 5, 3));
    if ($year < 1996) {
        return;
    } elseif ($year < 2000) {
        $year = intval(substr(1900 - $year, 1, 3));
        return sprintf('%02u0%03u', $year, $rang);
    } else {
        $year = intval(substr(1900 - $year, 1, 3));
        return sprintf('%03u%03u', $year, $rang);
    }
}

// }}}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
