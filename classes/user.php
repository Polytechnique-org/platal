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

class User extends PlUser
{
    private $_profile_fetched = false;
    private $_profile = null;

    // Additional fields (non core)
    protected $promo = null;

    // Implementation of the login to uid method.
    protected function getLogin($login)
    {
        global $globals;

        if (!$login) {
            throw new UserNotFoundException();
        }

        if ($login instanceof User) {
            $machin->id();
        }

        if ($login instanceof Profile) {
            $this->_profile = $login;
            $this->_profile_fetched = true;
            $res = XDB::query('SELECT  ap.uid
                                 FROM  account_profiles AS ap
                                WHERE  ap.pid = {?} AND FIND_IN_SET(\'owner\', perms)',
                              $login->id());
            if ($res->numRows()) {
                return $res->fetchOneCell();
            }
            throw new UserNotFoundException();
        }

        // If $data is an integer, fetches directly the result.
        if (is_numeric($login)) {
            $res = XDB::query('SELECT  a.uid
                                 FROM  accounts AS a
                                WHERE  a.uid = {?}', $login);
            if ($res->numRows()) {
                return $res->fetchOneCell();
            }

            throw new UserNotFoundException();
        }

        // Checks whether $login is a valid hruid or not.
        $res = XDB::query('SELECT  a.uid
                             FROM  accounts AS a
                            WHERE  a.hruid = {?}', $login);
        if ($res->numRows()) {
            return $res->fetchOneCell();
        }

        // From now, $login can only by an email alias, or an email redirection.
        // If it doesn't look like a valid address, appends the plat/al's main domain.
        $login = trim(strtolower($login));
        if (strstr($login, '@') === false) {
            $login = $login . '@' . $globals->mail->domain;
        }

        // Checks if $login is a valid alias on the main domains.
        list($mbox, $fqdn) = explode('@', $login);
        if ($fqdn == $globals->mail->domain || $fqdn == $globals->mail->domain2) {
            $res = XDB::query('SELECT  a.uid
                                 FROM  accounts AS a
                           INNER JOIN  aliases AS al ON (al.uid = a.uid AND al.type IN (\'alias\', \'a_vie\'))
                                WHERE  al.alias = {?}', $mbox);
            if ($res->numRows()) {
                return $res->fetchOneCell();
            }

            if (preg_match('/^(.*)\.([0-9]{4})$/u', $mbox, $matches)) {
                $res = XDB::query('SELECT  a.uid
                                     FROM  accounts          AS a
                               INNER JOIN  aliases           AS al ON (al.uid = a.uid AND al.type IN (\'alias\', \'a_vie\'))
                               INNER JOIN  account_profiles  AS ap ON (a.uid = ap.uid AND FIND_IN_SET(\'owner\', ap.perms))
                               INNER JOIN  profiles          AS p  ON (p.pid = ap.pid)
                               INNER JOIN  profile_education AS pe ON (p.pid = pe.pid AND FIND_IN_SET(\'primary\', pe.flags))
                                    WHERE  p.hrpid = {?} OR ((pe.entry_year <= {?} AND pe.grad_year >= {?}) AND al.alias = {?})
                                 GROUP BY  a.uid',
                                   $matches[0], $matches[2], $matches[2], $matches[1]);
                if ($res->numRows() == 1) {
                    return $res->fetchOneCell();
                }
            }

            throw new UserNotFoundException();
        }

        // Looks for $login as an email alias from the dedicated alias domain.
        if ($fqdn == $globals->mail->alias_dom || $fqdn == $globals->mail->alias_dom2) {
            $res = XDB::query("SELECT  redirect
                                 FROM  virtual_redirect
                           INNER JOIN  virtual USING(vid)
                                WHERE  alias = {?}", $mbox . '@' . $globals->mail->alias_dom);
            if ($redir = $res->fetchOneCell()) {
                // We now have a valid alias, which has to be translated to an hruid.
                list($alias, $alias_fqdn) = explode('@', $redir);
                $res = XDB::query("SELECT  a.uid
                                     FROM  accounts AS a
                                LEFT JOIN  aliases AS al ON (al.uid = a.uid AND al.type IN ('alias', 'a_vie'))
                                    WHERE  al.alias = {?}", $alias);
                if ($res->numRows()) {
                    return $res->fetchOneCell();
                }
            }

            throw new UserNotFoundException();
        }

        // Looks for an account with the given email.
        $res = XDB::query('SELECT  a.uid
                             FROM  accounts AS a
                            WHERE  a.email = {?}', $login);
        if ($res->numRows() == 1) {
            return $res->fetchOneCell();
        }

        // Otherwise, we do suppose $login is an email redirection.
        $res = XDB::query("SELECT  a.uid
                             FROM  accounts AS a
                        LEFT JOIN  emails AS e ON (e.uid = a.uid)
                            WHERE  e.email = {?}", $login);
        if ($res->numRows() == 1) {
            return $res->fetchOneCell();
        }

        throw new UserNotFoundException($res->fetchColumn(1));
    }

    protected static function loadMainFieldsFromUIDs(array $uids, $respect_order = true)
    {
        if (empty($uids)) {
            return PlIteratorUtils::emptyIterator();
        }

        global $globals;
        $joins = '';
        $fields = array();
        if ($globals->asso('id')) {
            $joins .= XDB::format("LEFT JOIN group_members AS gpm ON (gpm.uid = a.uid AND gpm.asso_id = {?})\n", $globals->asso('id'));
            $fields[] = 'gpm.perms AS group_perms';
            $fields[] = 'gpm.comm AS group_comm';
        }
        if (count($fields) > 0) {
            $fields = ', ' . implode(', ', $fields);
        } else {
            $fields = '';
        }

        if ($respect_order) {
            $order = 'ORDER BY ' . XDB::formatCustomOrder('a.uid', $uids);
        } else {
            $order = '';
        }

        $uids = array_map(array('XDB', 'escape'), $uids);

        return XDB::iterator('SELECT  a.uid, a.hruid, a.registration_date, ah.alias AS homonym,
                                      IF (af.alias IS NULL, NULL, CONCAT(af.alias, \'@' . $globals->mail->domain . '\')) AS forlife,
                                      IF (af.alias IS NULL, NULL, CONCAT(af.alias, \'@' . $globals->mail->domain2 . '\')) AS forlife_alternate,
                                      IF (ab.alias IS NULL, NULL, CONCAT(ab.alias, \'@' . $globals->mail->domain . '\')) AS bestalias,
                                      IF (ab.alias IS NULL, NULL, CONCAT(ab.alias, \'@' . $globals->mail->domain2 . '\')) AS bestalias_alternate,
                                      a.email, a.full_name, a.directory_name, a.display_name, a.sex = \'female\' AS gender,
                                      IF(a.state = \'active\', at.perms, \'\') AS perms,
                                      a.email_format, a.is_admin, a.state, a.type, a.skin,
                                      FIND_IN_SET(\'watch\', a.flags) AS watch, a.comment,
                                      a.weak_password IS NOT NULL AS weak_access, g.g_account_name IS NOT NULL AS googleapps,
                                      a.token IS NOT NULL AS token_access, a.token, a.last_version,
                                      (e.email IS NULL AND NOT FIND_IN_SET(\'googleapps\', eo.storage)) AND a.state != \'pending\' AS lost,
                                      UNIX_TIMESTAMP(s.start) AS lastlogin, s.host, UNIX_TIMESTAMP(fp.last_seen) AS banana_last
                                      ' . $fields . '
                                FROM  accounts AS a
                          INNER JOIN  account_types AS at ON (at.type = a.type)
                           LEFT JOIN  aliases AS af ON (af.uid = a.uid AND af.type = \'a_vie\')
                           LEFT JOIN  aliases AS ab ON (ab.uid = a.uid AND FIND_IN_SET(\'bestalias\', ab.flags))
                           LEFT JOIN  aliases AS ah ON (ah.uid = a.uid AND ah.type = \'homonyme\')
                           LEFT JOIN  emails AS e ON (e.uid = a.uid AND e.flags = \'active\')
                           LEFT JOIN  email_options AS eo ON (eo.uid = a.uid)
                           LEFT JOIN  gapps_accounts AS g ON (a.uid = g.l_userid AND g.g_status = \'active\')
                           LEFT JOIN  log_last_sessions AS ls ON (ls.uid = a.uid)
                           LEFT JOIN  log_sessions AS s ON (s.id = ls.id)
                           LEFT JOIN  forum_profiles AS fp ON (fp.uid = a.uid)
                                   ' . $joins . '
                               WHERE  a.uid IN (' . implode(', ', $uids) . ')
                            GROUP BY  a.uid
                                   ' . $order);
    }

    // Implementation of the data loader.
    protected function loadMainFields()
    {
        if ($this->hruid !== null && $this->forlife !== null
            && $this->bestalias !== null && $this->display_name !== null
            && $this->full_name !== null && $this->perms !== null
            && $this->gender !== null && $this->email_format !== null) {
            return;
        }
        $this->fillFromArray(self::loadMainFieldsFromUIDs(array($this->uid))->next());
    }

    // Specialization of the buildPerms method
    // This function build 'generic' permissions for the user. It does not take
    // into account page specific permissions (e.g X.net group permissions)
    protected function buildPerms()
    {
        if (!is_null($this->perm_flags)) {
            return;
        }
        if ($this->perms === null) {
             $this->loadMainFields();
        }
        $this->perm_flags = self::makePerms($this->perms, $this->is_admin);
    }

    // We do not want to store the password in the object.
    // So, fetch it 'on demand'
    public function password()
    {
        return XDB::fetchOneCell('SELECT  a.password
                                    FROM  accounts AS a
                                   WHERE  a.uid = {?}', $this->id());
    }

    public function isActive()
    {
        return $this->state == 'active';
    }

    /** Overload PlUser::promo(): there no promo defined for a user in the current
     * schema. The promo is a field from the profile.
     */
    public function promo()
    {
        if (!$this->hasProfile()) {
            return '';
        }
        return $this->profile()->promo();
    }

    public function firstName()
    {
        if (!$this->hasProfile()) {
            return $this->displayName();
        }
        return $this->profile()->firstName();
    }

    public function lastName()
    {
        if (!$this->hasProfile()) {
            return '';
        }
        return $this->profile()->lastName();
    }

    public function displayName()
    {
        if (!$this->hasProfile()) {
            return $this->display_name;
        }
        return $this->profile()->yourself;
    }

    public function fullName($with_promo = false)
    {
        if (!$this->hasProfile()) {
            return $this->full_name;
        }
        return $this->profile()->fullName($with_promo);
    }

    public function directoryName()
    {
        if (!$this->hasProfile()) {
            return $this->directory_name;
        }
        return $this->profile()->directory_name;
    }

    /** Return the main profile attached with this account if any.
     */
    public function profile($forceFetch = false)
    {
        if (!$this->_profile_fetched || $forceFetch) {
            $this->_profile_fetched = true;
            $this->_profile = Profile::get($this);
        }
        return $this->_profile;
    }

    /** Return true if the user has an associated profile.
     */
    public function hasProfile()
    {
        return !is_null($this->profile());
    }

    /** Check if the user can edit to given profile.
     */
    public function canEdit(Profile $profile)
    {
        // XXX: Check permissions (e.g. secretary permission)
        //      and flags from the profile
        return XDB::fetchOneCell('SELECT  pid
                                    FROM  account_profiles
                                   WHERE  uid = {?} AND pid = {?}',
                                 $this->id(), $profile->id());
    }

    /** Get the email alias of the user.
     */
    public function emailAlias()
    {
        global $globals;
        $data = $this->emailAliases($globals->mail->alias_dom);
        if (count($data) > 0) {
            return array_pop($data);
        }
        return null;
    }

    /** Get all the aliases the user belongs to.
     */
    public function emailAliases($domain = null, $type = 'user',  $sub_state = false)
    {
        $join = XDB::format('(vr.redirect = {?} OR vr.redirect = {?}) ',
                             $this->forlifeEmail(), $this->m4xForlifeEmail());
        $where = '';
        if (!is_null($domain)) {
            $where = XDB::format('WHERE v.alias LIKE CONCAT("%@", {?})', $domain);
        }
        if (!is_null($type)) {
            if (empty($where)) {
                $where = XDB::format('WHERE v.type = {?}', $type);
            } else {
                $where .= XDB::format(' AND v.type = {?}', $type);
            }
        }
        if ($sub_state) {
            return XDB::fetchAllAssoc('alias', 'SELECT  v.alias, vr.redirect IS NOT NULL AS sub
                                                  FROM  virtual AS v
                                             LEFT JOIN  virtual_redirect AS vr ON (v.vid = vr.vid AND ' . $join . ')
                                                 ' . $where);
        } else {
            return XDB::fetchColumn('SELECT  v.alias
                                       FROM  virtual AS v
                                 INNER JOIN  virtual_redirect AS vr ON (v.vid = vr.vid AND ' . $join . ')
                                     ' . $where);
        }
    }

    /** Get the alternative forlife email
     * TODO: remove this uber-ugly hack. The issue is that you need to remove
     * all @m4x.org addresses in virtual_redirect first.
     * XXX: This is juste to make code more readable, to be remove as soon as possible
     */
    public function m4xForlifeEmail()
    {
        global $globals;
        trigger_error('USING M4X FORLIFE', E_USER_NOTICE);
        return $this->login() . '@' . $globals->mail->domain2;
    }


    /** Get marketing informations
     */
    private function fetchMarketingData()
    {
        if (isset($this->pending_registration_date)) {
            return;
        }
        $infos = XDB::fetchOneAssoc('SELECT  rp.date AS pending_registration_date, rp.email AS pending_registration_email,
                                             rm.last AS last_marketing_date, rm.email AS last_marketing_email
                                       FROM  accounts           AS a
                                  LEFT JOIN  register_pending   AS rp ON (rp.uid = a.uid)
                                  LEFT JOIN  register_marketing AS rm ON (rm.uid = a.uid AND rm.last != \'0000-00-00\')
                                      WHERE  a.uid = {?}
                                   ORDER BY  rm.last DESC', $this->id());
        if (is_null($infos)) {
            $infos = array(
                'pending_registration_date'  => null,
                'pending_registration_email' => null,
                'last_marketing_date'        => null,
                'last_marketing_email'       => null
            );
        }
        $this->fillFromArray($infos);
    }

    public function pendingRegistrationDate()
    {
        $this->fetchMarketingData();
        return $this->pending_registration_date;
    }

    public function pendingRegistrationEmail()
    {
        $this->fetchMarketingData();
        return $this->pending_registration_email;
    }

    public function lastMarketingDate()
    {
        $this->fetchMarketingData();
        return $this->last_marketing_date;
    }

    public function lastMarketingEmail()
    {
        $this->fetchMarketingData();
        return $this->last_marketing_email;
    }

    public function lastKnownEmail()
    {
        $this->fetchMarketingData();
        if ($this->pending_registration_email > $this->last_marketing_date) {
            return $this->pending_registration_email;
        }
        return $this->last_marketing_email;
    }


    /** Format of the emails sent by the site
     */
    public function setEmailFormat($format)
    {
        Platal::assert($format == self::FORMAT_HTML || $format == self::FORMAT_TEXT,
                       "Invalid email format \"$format\"");
        XDB::execute("UPDATE  accounts
                         SET  email_format = {?}
                       WHERE  uid = {?}",
                     $format, $this->uid);
        $this->email_format = $format;
    }

    /** Get watch informations
     */
    private function fetchWatchData()
    {
        if (isset($this->watch_actions)) {
            return;
        }
        $watch = XDB::fetchOneAssoc('SELECT  flags AS watch_flags, actions AS watch_actions,
                                             UNIX_TIMESTAMP(last) AS watch_last
                                       FROM  watch
                                      WHERE  uid = {?}', $this->id());
        $watch['watch_flags'] = new PlFlagSet($watch['watch_flags']);
        $watch['watch_actions'] = new PlFlagSet($watch['watch_actions']);
        $watch['watch_promos'] = XDB::fetchColumn('SELECT  promo
                                                     FROM  watch_promo
                                                    WHERE  uid = {?}', $this->id());
        $watch['watch_users'] = XDB::fetchColumn('SELECT  ni_id
                                                    FROM  watch_nonins
                                                   WHERE  uid = {?}', $this->id());
        $this->fillFromArray($watch);
    }

    public function watchType($type)
    {
        $this->fetchWatchData();
        return $this->watch_actions->hasFlag($type);
    }

    public function watchContacts()
    {
        $this->fetchWatchData();
        return $this->watch_flags->hasFlag('contacts');
    }

    public function watchEmail()
    {
        $this->fetchWatchData();
        return $this->watch_flags->hasFlag('mail');
    }

    public function watchPromos()
    {
        $this->fetchWatchData();
        return $this->watch_promos;
    }

    public function watchUsers()
    {
        $this->fetchWatchData();
        return $this->watch_users;
    }

    public function watchLast()
    {
        $this->fetchWatchData();
        return $this->watch_last;
    }

    public function invalidWatchCache()
    {
        unset($this->watch_actions);
        unset($this->watch_users);
        unset($this->watch_last);
        unset($this->watch_promos);
    }


    // Contacts
    private $contacts = null;
    private function fetchContacts()
    {
        if (is_null($this->contacts)) {
            $this->contacts = XDB::fetchAllAssoc('contact', 'SELECT  *
                                                               FROM  contacts
                                                              WHERE  uid = {?}',
                                                 $this->id());
        }
    }

    public function iterContacts()
    {
        $this->fetchContacts();
        return Profile::iterOverPIDs(array_keys($this->contacts));
    }

    public function getContacts()
    {
        $this->fetchContacts();
        return Profile::getBulkProfilesWithPIDs(array_keys($this->contacts));
    }

    public function isContact(Profile &$profile)
    {
        $this->fetchContacts();
        return isset($this->contacts[$profile->id()]);
    }

    public function isWatchedUser(Profile &$profile)
    {
        return in_array($profile->id(), $this->watchUsers());
    }

    // Groupes X
    private $groups = null;
    public function groups()
    {
        if (is_null($this->groups)) {
            $this->groups = XDB::fetchAllAssoc('asso_id', 'SELECT  asso_id, perms, comm
                                                             FROM  group_members
                                                            WHERE  uid = {?}',
                                                $this->id());
        }
        return $this->groups;
    }

    public function groupNames($institutions = false)
    {
        if ($institutions) {
            $where = ' AND (g.cat = \'GroupesX\' OR g.cat = \'Institutions\')';
        } else {
            $where = '';
        }
        return XDB::fetchAllAssoc('SELECT  g.diminutif, g.nom, g.site
                                     FROM  group_members AS gm
                                LEFT JOIN  groups AS g ON (g.id = gm.asso_id)
                                    WHERE  gm.uid = {?}' . $where,
                                  $this->id());
    }

    public function groupCount()
    {
        return XDB::fetchOneCell('SELECT  COUNT(DISTINCT(asso_id))
                                    FROM  group_members
                                   WHERE  uid = {?}',
                                 $this->id());
    }

    public function inGroup($asso_id)
    {
        $res = XDB::fetchOneCell('SELECT  COUNT(*)
                                    FROM  group_members
                                   WHERE  uid = {?} AND asso_id = {?}',
                                 $this->id(), $asso_id);
        return ($res > 0);
    }

    /**
     * Clears a user.
     *  *always deletes in: account_lost_passwords, register_marketing,
     *      register_pending, register_subs, watch_nonins, watch, watch_promo
     *  *always keeps in: account_types, accounts, aliases, axletter_ins, carvas,
     *      group_members, homonyms, newsletter_ins, register_mstats,
     *  *deletes if $clearAll: account_auth_openid, announce_read, contacts,
     *      email_options, email_send_save, emails, forum_innd, forum_profiles,
     *      forum_subs, gapps_accounts, gapps_nicknames, group_announces_read,
     *      group_member_sub_requests, reminder, requests, requests_hidden,
     *      virtual, virtual_redirect, ML
     *  *modifies if $clearAll: accounts
     *
     * Use cases:
     *  *$clearAll == false: when a user dies, her family still needs to keep in
     *      touch with the community.
     *  *$clearAll == true: in every other case we want the account to be fully
     *      deleted so that it can not be used anymore.
     */
    public function clear($clearAll = true)
    {
        $tables = array('account_lost_passwords', 'register_marketing',
                        'register_pending', 'register_subs', 'watch_nonins',
                        'watch', 'watch_promo');

        foreach ($tables as $t) {
            XDB::execute('DELETE FROM  ' . $t . '
                                WHERE  uid = {?}',
                                $this->id());
        }

        if ($clearAll) {
            $groupIds = XDB::iterator('SELECT  asso_id
                                         FROM  group_members
                                        WHERE  uid = {?}',
                                      $this->id());
            while ($groupId = $groupIds->next()) {
                $group = Group::get($groupId);
                if ($group->notif_unsub) {
                    $mailer = new PlMailer('xnetgrp/unsubscription-notif.mail.tpl');
                    $admins = $group->iterAdmins();
                    while ($admin = $admins->next()) {
                        $mailer->addTo($admin);
                    }
                    $mailer->assign('group', $group->shortname);
                    $mailer->assign('user', $this);
                    $mailer->assign('selfdone', false);
                    $mailer->send();
                }
            }

            $tables = array('account_auth_openid', 'gannounce_read', 'contacts',
                            'email_options', 'gemail_send_save', 'emails',
                            'forum_innd', 'gforum_profiles', 'forum_subs',
                            'gapps_accounts', 'ggapps_nicknames', 'group_announces_read',
                            'group_members', 'ggroup_member_sub_requests', 'reminder', 'requests',
                            'requests_hidden');

            foreach ($tables as $t) {
                XDB::execute('DELETE FROM  ' . $t . '
                                    WHERE  uid = {?}',
                    $this->id());
            }

            XDB::execute("UPDATE  accounts
                             SET  registration_date = 0, state = 'pending', password = NULL,
                                  weak_password = NULL, token = NULL, is_admin = 0
                           WHERE  uid = {?}",
                         $this->id());

            XDB::execute('DELETE  v.*
                            FROM  virtual          AS v
                      INNER JOIN  virtual_redirect AS r ON (v.vid = r.vid)
                           WHERE  redirect = {?} OR redirect = {?}',
                         $this->forlifeEmail(), $this->m4xForlifeEmail());
            XDB::execute('DELETE FROM  virtual_redirect
                                WHERE  redirect = {?} OR redirect = {?}',
                         $this->forlifeEmail(), $this->m4xForlifeEmail());

            if ($globals->mailstorage->googleapps_domain) {
                require_once 'googleapps.inc.php';

                if (GoogleAppsAccount::account_status($uid)) {
                    $account = new GoogleAppsAccount($user);
                    $account->suspend();
                }
            }
        }

        $mmlist = new MMList($this);
        $mmlist->kill($this->hruid, $clearAll);
    }

    // Merge all infos in other user and then clean this one
    public function mergeIn(User &$newuser) {
        if ($this->profile()) {
            // Don't disable user with profile in this way.
            global $globals;
            Platal::page()->trigError('Impossible de fusionner les comptes ' . $this->hruid . ' et ' . $newuser->hruid .
                                      '. Contacte support@' . $globals->mail->domain . '.');
            return false;
        }

        if ($this->forlifeEmail()) {
            // If the new user is not registered and does not have already an email address,
            // we need to give him the old user's email address if he has any.
            if (!$newuser->perms) {
                XDB::execute('UPDATE  accounts
                                 SET  email = {?}
                               WHERE  uid = {?} AND email IS NULL',
                             $this->forlifeEmail(), $newuser->id());
            }
            $newemail = XDB::fetchOneCell('SELECT  email
                                             FROM  accounts
                                            WHERE  uid = {?}',
                                          $newuser->id());

            // Change email used in aliases and mailing lists.
            if ($this->forlifeEmail() != $newemail) {
                // virtual_redirect (email aliases)
                XDB::execute('DELETE  v1
                                FROM  virtual_redirect AS v1, virtual_redirect AS v2
                               WHERE  v1.vid = v2.vid AND v1.redirect = {?} AND v2.redirect = {?}',
                             $this->forlifeEmail(), $newemail);
                XDB::execute('UPDATE  virtual_redirect
                                 SET  redirect = {?}
                               WHERE  redirect = {?}',
                             $newemail, $this->forlifeEmail());

                // group mailing lists
                $group_domains = XDB::fetchColumn('SELECT  g.mail_domain
                                                     FROM  groups        AS g
                                               INNER JOIN  group_members AS gm ON(g.id = gm.asso_id)
                                                    WHERE  g.mail_domain != \'\' AND gm.uid = {?}',
                                                  $this->id());
                foreach ($group_domains as $mail_domain) {
                    $mmlist = new MMList($this, $mail_domain);
                    $mmlist->replace_email_in_all($this->forlifeEmail(), $newemail);
                }
                // main domain lists
                $mmlist = new MMList($this);
                $mmlist->replace_email_in_all($this->forlifeEmail(), $newemail);
            }
        }

        // Updates user in following tables.
        foreach (array('group_announces', 'payment_transactions', 'log_sessions') as $table) {
            XDB::execute('UPDATE  ' . $table . '
                             SET  uid = {?}
                           WHERE  uid = {?}',
                         $newuser->id(), $this->id());
        }
        XDB::execute('UPDATE  group_events
                         SET  organisateur_uid = {?}
                       WHERE  organisateur_uid = {?}',
                     $newuser->id(), $this->id());

        // Merges user in following tables, ie updates when possible, then deletes remaining occurences of the old user.
        foreach (array('group_announces_read', 'group_event_participants', 'group_member_sub_requests', 'group_members') as $table) {
            XDB::execute('UPDATE IGNORE  ' . $table . '
                                    SET  uid = {?}
                                  WHERE  uid = {?}',
                         $newuser->id(), $this->id());
            XDB::execute('DELETE FROM  ' . $table . '
                                WHERE  uid = {?}',
                         $this->id());
        }

        // Eventually updates last session id and deletes old user's accounts entry.
        $lastSession = XDB::fetchOneCell('SELECT  id
                                            FROM  log_sessions
                                           WHERE  uid = {?}
                                        ORDER BY  start DESC
                                           LIMIT  1',
                                         $newuser->id());
        XDB::execute('UPDATE  log_last_sessions
                         SET  id = {?}
                       WHERE  uid = {?}',
                     $newuser->id());
        XDB::execute('DELETE FROM  accounts
                            WHERE  uid = {?}',
                     $this->id());

        return true;
    }

    // Return permission flags for a given permission level.
    public static function makePerms($perms, $is_admin)
    {
        $flags = new PlFlagSet($perms);
        $flags->addFlag(PERMS_USER);
        if ($is_admin) {
            $flags->addFlag(PERMS_ADMIN);
        }
        return $flags;
    }

    // Implementation of the default user callback.
    public static function _default_user_callback($login, $results)
    {
        $result_count = count($results);
        if ($result_count == 0 || !S::admin()) {
            Platal::page()->trigError("Il n'y a pas d'utilisateur avec l'identifiant : $login");
        } else {
            Platal::page()->trigError("Il y a $result_count utilisateurs avec cet identifiant : " . join(', ', $results));
        }
    }

    // Implementation of the static email locality checker.
    public static function isForeignEmailAddress($email)
    {
        global $globals;
        if (strpos($email, '@') === false) {
            return false;
        }

        list($user, $dom) = explode('@', $email);
        return $dom != $globals->mail->domain &&
               $dom != $globals->mail->domain2 &&
               $dom != $globals->mail->alias_dom &&
               $dom != $globals->mail->alias_dom2;
    }

    public static function isVirtualEmailAddress($email)
    {
        global $globals;
        if (strpos($email, '@') === false) {
            return false;
        }

        list($user, $dom) = explode('@', $email);
        return $dom == $globals->mail->alias_dom
            || $dom == $globals->mail->alias_dom2;
    }

    /* Tries to find pending accounts with an hruid close to $login. */
    public static function getPendingAccounts($login, $iterator = false)
    {
        global $globals;

        if (strpos($login, '@') === false) {
            return null;
        }

        list($login, $domain) = explode('@', $login);

        if ($domain && $domain != $globals->mail->domain && $domain != $globals->mail->domain2) {
            return null;
        }

        $sql = "SELECT  uid, full_name
                  FROM  accounts
                 WHERE  state = 'pending' AND REPLACE(hruid, '-', '') LIKE
                        CONCAT('%', REPLACE(REPLACE(REPLACE({?}, ' ', ''), '-', ''), '\'', ''), '%')
              ORDER BY  full_name";
        if ($iterator) {
            return XDB::iterator($sql, $login);
        } else {
            $res = XDB::query($sql, $login);
            return $res->fetchAllAssoc();
        }
    }


    public static function iterOverUIDs($uids, $respect_order = true)
    {
        return new UserIterator(self::loadMainFieldsFromUIDs($uids, $respect_order));
    }

    /** Fetch a set of users from a list of UIDs
     * @param $data The list of uids to fetch, or an array of arrays
     * @param $orig If $data is an array of arrays, the subfield where uids are stored
     * @param $dest If $data is an array of arrays, the subfield to fill with Users
     * @param $fetchProfile Whether to fetch Profiles as well
     * @return either an array of $uid => User, or $data with $data[$i][$dest] = User
     */
    public static function getBulkUsersWithUIDs(array $data, $orig = null, $dest = null, $fetchProfile = true)
    {
        // Fetch the list of uids
        if (is_null($orig)) {
            $uids = $data;
        } else {
            if (is_null($dest)) {
                $dest = $orig;
            }
            $uids = array();
            foreach ($data as $key=>$entry) {
                if (isset($entry[$orig])) {
                    $uids[] = $entry[$orig];
                }
            }
        }

        // Fetch users
        if (count($uids) == 0) {
            return $data;
        }
        $users = self::iterOverUIDs($uids, true);

        $table = array();
        if ($fetchProfile) {
            $profiles = Profile::iterOverUIDS($uids, true);
            if ($profiles != null) {
                $profile = $profiles->next();
            } else {
                $profile = null;
            }
        }

        /** We iterate through the users, moving in
         * profiles when they match the user ID :
         * there can be users without a profile, but not
         * the other way around.
         */
        while (($user = $users->next())) {
            if ($fetchProfile) {
                if ($profile != null && $profile->owner_id == $user->id()) {
                    $user->_profile = $profile;
                    $profile = $profiles->next();
                }
                $user->_profile_fetched = true;
            }
            $table[$user->id()] = $user;
        }

        // Build the result with respect to input order.
        if (is_null($orig)) {
            return $table;
        } else {
            foreach ($data as $key=>$entry) {
                if (isset($entry[$orig])) {
                    $entry[$dest] = $table[$entry[$orig]];
                    $data[$key] = $entry;
                }
            }
            return $data;
        }
    }

    public static function getBulkUsersFromDB($fetchProfile = true)
    {
        $args = func_get_args();
        $uids = call_user_func_array(array('XDB', 'fetchColumn'), $args);
        return self::getBulkUsersWithUIDs($uids, null, null, $fetchProfile);
    }
}

/** Iterator over a set of Users
 * @param an XDB::Iterator obtained from a User::loadMainFieldsFromUIDs
 */
class UserIterator implements PlIterator
{
    private $dbiter;

    public function __construct($dbiter)
    {
        $this->dbiter = $dbiter;
    }

    public function next()
    {
        $data = $this->dbiter->next();
        if ($data == null) {
            return null;
        } else {
            return User::getSilentWithValues(null, $data);
        }
    }

    public function total()
    {
        return $this->dbiter->total();
    }

    public function first()
    {
        return $this->dbiter->first();
    }

    public function last()
    {
        return $this->dbiter->last();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
