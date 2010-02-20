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


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
