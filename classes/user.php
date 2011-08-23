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

class User extends PlUser
{
    const PERM_API_USER_READONLY = 'api_user_readonly';
    const PERM_DIRECTORY_AX      = 'directory_ax';
    const PERM_DIRECTORY_PRIVATE = 'directory_private';
    const PERM_EDIT_DIRECTORY    = 'edit_directory';
    const PERM_FORUMS            = 'forums';
    const PERM_GROUPS            = 'groups';
    const PERM_LISTS             = 'lists';
    const PERM_MAIL              = 'mail';
    const PERM_PAYMENT           = 'payment';

    public static $sub_mail_domains = array(
        'x'      => '',
        'master' => 'master.',
        'phd'    => 'doc.',
        'all'    => 'alumni.'
    );

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
            return $login->id();
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
            $res = XDB::query('SELECT  uid
                                 FROM  accounts
                                WHERE  uid = {?}', $login);
            if ($res->numRows()) {
                return $res->fetchOneCell();
            }

            throw new UserNotFoundException();
        }

        // Checks whether $login is a valid hruid or not.
        $res = XDB::query('SELECT  uid
                             FROM  accounts
                            WHERE  hruid = {?}', $login);
        if ($res->numRows()) {
            return $res->fetchOneCell();
        }

        // From now, $login can only by an email alias, or an email redirection.
        $login = trim(strtolower($login));
        if (strstr($login, '@') === false) {
            $res = XDB::fetchOneCell('SELECT  uid
                                        FROM  email_source_account
                                       WHERE  email = {?}',
                                     $login);
        } else {
            list($email, $domain) = explode('@', $login);
            $res = XDB::fetchOneCell('SELECT  s.uid
                                        FROM  email_source_account  AS s
                                  INNER JOIN  email_virtual_domains AS m ON (s.domain = m.id)
                                  INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                                       WHERE  s.email = {?} AND d.name = {?}',
                                     $email, $domain);
        }

        if ($res) {
            return $res;
        }

        // Looks for an account with the given email.
        $res = XDB::query('SELECT  uid
                             FROM  accounts
                            WHERE  email = {?}', $login);
        if ($res->numRows() == 1) {
            return $res->fetchOneCell();
        }

        // Otherwise, we do suppose $login is an email redirection.
        $res = XDB::query('SELECT  uid
                             FROM  email_redirect_account
                            WHERE  redirect = {?}', $login);
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
            $fields[] = 'gpm.position AS group_position';
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

        return XDB::iterator('SELECT  a.uid, a.hruid, a.registration_date, h.uid IS NOT NULL AS homonym, a.firstname, a.lastname,
                                      IF(ef.email IS NULL, NULL, CONCAT(ef.email, \'@\', mf.name)) AS forlife,
                                      IF(ef.email IS NULL, NULL, CONCAT(ef.email, \'@\', df.name)) AS forlife_alternate,
                                      IF(eb.email IS NULL, NULL, CONCAT(eb.email, \'@\', mb.name)) AS bestalias,
                                      (er.redirect IS NULL AND a.state = \'active\' AND FIND_IN_SET(\'mail\', at.perms)) AS lost,
                                      a.email, a.full_name, a.directory_name, a.display_name, a.sex = \'female\' AS gender,
                                      IF(a.state = \'active\', CONCAT(at.perms, \',\', IF(a.user_perms IS NULL, \'\', a.user_perms)), \'\') AS perms,
                                      a.user_perms, a.email_format, a.is_admin, a.state, a.type, at.description AS type_description, a.skin,
                                      FIND_IN_SET(\'watch\', a.flags) AS watch, a.comment,
                                      a.weak_password IS NOT NULL AS weak_access, g.g_account_name IS NOT NULL AS googleapps,
                                      a.token IS NOT NULL AS token_access, a.token, a.last_version,
                                      UNIX_TIMESTAMP(s.start) AS lastlogin, s.host, UNIX_TIMESTAMP(fp.last_seen) AS banana_last
                                      ' . $fields . '
                                FROM  accounts               AS a
                          INNER JOIN  account_types          AS at ON (at.type = a.type)
                           LEFT JOIN  email_source_account   AS ef ON (ef.uid = a.uid AND ef.type = \'forlife\')
                           LEFT JOIN  email_virtual_domains  AS mf ON (ef.domain = mf.id)
                           LEFT JOIN  email_virtual_domains  AS df ON (df.aliasing = mf.id AND
                                                                       df.name LIKE CONCAT(\'%\', {?}) AND df.name NOT LIKE \'alumni.%\')
                           LEFT JOIN  email_source_account   AS eb ON (eb.uid = a.uid AND FIND_IN_SET(\'bestalias\',eb.flags))
                           LEFT JOIN  email_virtual_domains  AS mb ON (a.best_domain = mb.id)
                           LEFT JOIN  email_redirect_account AS er ON (er.uid = a.uid AND er.flags = \'active\' AND er.broken_level < 3
                                                                       AND er.type != \'imap\' AND er.type != \'homonym\')
                           LEFT JOIN  homonyms_list          AS h  ON (h.uid = a.uid)
                           LEFT JOIN  gapps_accounts         AS g  ON (a.uid = g.l_userid AND g.g_status = \'active\')
                           LEFT JOIN  log_last_sessions      AS ls ON (ls.uid = a.uid)
                           LEFT JOIN  log_sessions           AS s  ON (s.id = ls.id)
                           LEFT JOIN  forum_profiles         AS fp ON (fp.uid = a.uid)
                                   ' . $joins . '
                               WHERE  a.uid IN (' . implode(', ', $uids) . ')
                            GROUP BY  a.uid
                                   ' . $order, $globals->mail->domain2, $globals->mail->domain2);
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

    public function setPerms($perms)
    {
        $this->perms = $perms;
        $this->perm_flags = null;
    }

    /** Retrieve the 'general' read visibility.
     * This is the maximum level of fields that may be viewed by the current user on other profiles.
     *
     * Rules are:
     *  - Everyone can view 'public'
     *  - directory_ax gives access to 'AX' level
     *  - directory_private gives access to 'private' level
     *  - admin gives access to 'hidden' level
     */
    public function readVisibility()
    {
        $level = Visibility::VIEW_NONE;
        if ($this->is_admin) {
            $level = Visibility::VIEW_ADMIN;
        } elseif ($this->checkPerms('directory_private')) {
            $level = Visibility::VIEW_PRIVATE;
        } elseif ($this->checkPerms('directory_ax')) {
            $level = Visibility::VIEW_AX;
        } else {
            $level = Visibility::VIEW_PUBLIC;
        }
        return Visibility::get($level);
    }

    /** Retrieve the 'general' edit visibility.
     * This is the maximum level of fields that may be edited by the current user on other profiles.
     *
     * Rules are:
     *  - Only admins can edit the 'hidden' fields
     *  - If someone has 'directory_edit' (which is actually directory_ax_edit): AX level
     *  - Otherwise, nothing.
     */
    public function editVisibility()
    {
        $level = Visibility::VIEW_NONE;
        if ($this->is_admin) {
            $level = Visibility::VIEW_ADMIN;
        } elseif ($this->checkPerms('directory_edit')) {
            $level = Visibility::VIEW_AX;
        }
        return Visibility::get($level);
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

    public function category()
    {
        $promo = $this->promo();
        if (!empty($promo)) {
            return $promo;
        } else {
            return $this->type_description;
        }
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

    public function shortName($with_promo = false)
    {
        if (!$this->hasProfile()) {
            return $this->full_name;
        }
        return $this->profile()->shortName($with_promo);
    }

    public function directoryName()
    {
        if (!$this->hasProfile()) {
            return $this->directory_name;
        }
        return $this->profile()->directory_name;
    }

    static public function compareDirectoryName($a, $b)
    {
        return strcasecmp(replace_accent($a->directoryName()), replace_accent($b->directoryName()));
    }

    /** Return the main profile attached with this account if any.
     */
    public function profile($forceFetch = false, $fields = 0x0000, $visibility = null)
    {
        if (!$this->_profile_fetched || $forceFetch) {
            $this->_profile_fetched = true;
            $this->_profile = Profile::get($this, $fields, $visibility);
        } else if ($this->_profile !== null && !$this->_profile->visibility->equals($visibility)) {
            return Profile::get($this, $fields, $visibility);
        }
        return $this->_profile;
    }

    /** Return true if the user has an associated profile.
     */
    public function hasProfile()
    {
        return !is_null($this->profile());
    }

    /** Return true if given a reference to the profile of this user.
     */
    public function isMyProfile($other)
    {
        if (!$other) {
            return false;
        } else if ($other instanceof Profile) {
            $profile = $this->profile();
            return $profile && $profile->id() == $other->id();
        }
        return false;
    }

    /** Check if the user can edit to given profile.
     */
    public function canEdit(Profile $profile)
    {
        if ($this->checkPerms(User::PERM_EDIT_DIRECTORY)) {
            return true;
        }
        return XDB::fetchOneCell('SELECT  pid
                                    FROM  account_profiles
                                   WHERE  uid = {?} AND pid = {?}',
                                 $this->id(), $profile->id());
    }

    /** Determines main email domain for this user.
     */
    public function mainEmailDomain()
    {
        if (array_key_exists($this->type, self::$sub_mail_domains)) {
            return self::$sub_mail_domains[$this->type] . Platal::globals()->mail->domain;
        }
    }

    /** Determines alternate email domain for this user.
     */
    public function alternateEmailDomain()
    {
        if (array_key_exists($this->type, self::$sub_mail_domains)) {
            return self::$sub_mail_domains[$this->type] . Platal::globals()->mail->domain2;
        }
    }

    public function forlifeEmailAlternate()
    {
        if (!empty($this->forlife_alternate)) {
            return $this->forlife_alternate;
        }
        return $this->email;
    }

    /** Fetch existing auxiliary alias.
     */
    public function emailAlias()
    {
        $aliases = $this->emailAliases();
        if (count($aliases)) {
            return $aliases[0];
        }
        return null;
    }

    /** Fetch existing auxiliary aliases.
     */
    public function emailAliases()
    {
        return XDB::fetchColumn('SELECT  CONCAT(s.email, \'@\', d.name)
                                   FROM  email_source_account  AS s
                             INNER JOIN  email_virtual_domains AS m ON (s.domain = m.id)
                             INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                                  WHERE  s.uid = {?} AND s.type = \'alias_aux\'
                               ORDER BY  d.name',
                                $this->id());
    }

    /** Get all group aliases the user belongs to.
     */
    public function emailGroupAliases($domain = null)
    {
        if (is_null($domain)) {
            return XDB::fetchColumn('SELECT  CONCAT(v.email, \'@\', dv.name) AS alias
                                       FROM  email_virtual         AS v
                                 INNER JOIN  email_virtual_domains AS dv ON (v.domain = dv.id)
                                 INNER JOIN  email_source_account  AS s  ON (s.uid = {?})
                                 INNER JOIN  email_virtual_domains AS ms ON (s.domain = ms.id)
                                 INNER JOIN  email_virtual_domains AS ds ON (ds.aliasing = ms.id)
                                      WHERE  v.redirect = CONCAT(s.email, \'@\', ds.name) AND v.type = \'alias\'',
                                    $this->id());
        } else {
            return XDB::fetchAllAssoc('alias',
                                      'SELECT  CONCAT(v.email, \'@\', dv.name) AS alias, MAX(v.redirect = CONCAT(s.email, \'@\', ds.name)) AS sub
                                         FROM  email_virtual         AS v
                                   INNER JOIN  email_virtual_domains AS dv ON (v.domain = dv.id AND dv.name = {?})
                                   INNER JOIN  email_source_account  AS s  ON (s.uid = {?})
                                   INNER JOIN  email_virtual_domains AS ms ON (s.domain = ms.id)
                                   INNER JOIN  email_virtual_domains AS ds ON (ds.aliasing = ms.id)
                                        WHERE  v.type = \'alias\'
                                     GROUP BY  v.email
                                     ORDER BY  v.email',
                                      $domain, $this->id());
        }
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

    public function isContact(Profile $profile)
    {
        $this->fetchContacts();
        return isset($this->contacts[$profile->id()]);
    }

    public function isWatchedUser(Profile $profile)
    {
        return in_array($profile->id(), $this->watchUsers());
    }

    // Groupes X
    private $groups = null;
    public function groups($institutions = false, $onlyPublic = false)
    {
        if (is_null($this->groups)) {
            $this->groups = XDB::fetchAllAssoc('asso_id', 'SELECT  gm.asso_id, gm.perms, gm.comm,
                                                                   g.diminutif, g.nom, g.site, g.cat,
                                                                   g.pub
                                                             FROM  group_members AS gm
                                                       INNER JOIN  groups AS g ON (g.id = gm.asso_id)
                                                            WHERE  uid = {?}',
                                                $this->id());
        }
        if (!$institutions && !$onlyPublic) {
            return $this->groups;
        } else {
            $result = array();
            foreach ($this->groups as $id=>$data) {
                if ($institutions) {
                    if ($data['cat'] != Group::CAT_GROUPESX && $data['cat'] != Group::CAT_INSTITUTIONS) {
                        continue;
                    }
                }
                if ($onlyPublic) {
                    if ($data['pub'] != 'public') {
                        continue;
                    }
                }
                $result[$id] = $data;
            }
            return $result;
        }
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
     *  *always keeps in: account_types, accounts, email_virtual, carvas,
     *      group_members, homonyms_list, newsletter_ins, register_mstats, email_source_account
     *  *deletes if $clearAll: account_auth_openid, announce_read, contacts,
     *      email_redirect_account, email_redirect_account, email_send_save, forum_innd, forum_profiles,
     *      forum_subs, gapps_accounts, gapps_nicknames, group_announces_read,
     *      group_member_sub_requests, reminder, requests, requests_hidden,
     *      email_virtual, ML
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
            global $globals;

            $groupIds = XDB::iterator('SELECT  asso_id
                                         FROM  group_members
                                        WHERE  uid = {?}',
                                      $this->id());
            while ($groupId = $groupIds->next()) {
                $group = Group::get($groupId);
                if (!empty($group) && $group->notif_unsub) {
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

            $tables = array('account_auth_openid', 'announce_read', 'contacts',
                            'email_send_save',
                            'forum_innd', 'forum_profiles', 'forum_subs',
                            'group_announces_read', 'group_members',
                            'group_member_sub_requests', 'reminder', 'requests',
                            'requests_hidden');
            foreach ($tables as $t) {
                XDB::execute('DELETE FROM  ' . $t . '
                                    WHERE  uid = {?}',
                             $this->id());
            }
            XDB::execute('DELETE FROM  email_redirect_account
                                WHERE  uid = {?} AND type != \'homonym\'',
                         $this->id());
            XDB::execute('DELETE FROM  email_virtual
                                WHERE  redirect = {?}',
                         $this->forlifeEmail());

            foreach (array('gapps_accounts', 'gapps_nicknames') as $t) {
                XDB::execute('DELETE FROM  ' . $t . '
                                    WHERE  l_userid = {?}',
                             $this->id());
            }

            XDB::execute("UPDATE  accounts
                             SET  registration_date = 0, state = 'pending', password = NULL,
                                  weak_password = NULL, token = NULL, is_admin = 0
                           WHERE  uid = {?}",
                         $this->id());

            if ($globals->mailstorage->googleapps_domain) {
                require_once 'googleapps.inc.php';

                if (GoogleAppsAccount::account_status($this->id())) {
                    $account = new GoogleAppsAccount($this);
                    $account->suspend();
                }
            }
        }

        $mmlist = new MMList(S::user());
        $mmlist->kill($this->hruid, $clearAll);
    }

    // Merge all infos in other user and then clean this one
    public function mergeIn(User $newuser) {
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

                // Reftech new user so its forlifeEmail will be correct.
                $newuser = self::getSilentWithUID($newuser->id());
            }

            // Change email used in mailing lists.
            if ($this->forlifeEmail() != $newuser->forlifeEmail()) {
                // The super user is the user who has the right to do the modification.
                $super_user = S::user();
                // group mailing lists
                $group_domains = XDB::fetchColumn('SELECT  g.mail_domain
                                                     FROM  groups        AS g
                                               INNER JOIN  group_members AS gm ON(g.id = gm.asso_id)
                                                    WHERE  g.mail_domain != \'\' AND gm.uid = {?}',
                                                  $this->id());
                foreach ($group_domains as $mail_domain) {
                    $mmlist = new MMList($super_user, $mail_domain);
                    $mmlist->replace_email_in_all($this->forlifeEmail(), $newuser->forlifeEmail());
                }
                // main domain lists
                $mmlist = new MMList($super_user);
                $mmlist->replace_email_in_all($this->forlifeEmail(), $newuser->forlifeEmail());
            }
        }

        // Updates user in following tables.
        foreach (array('group_announces', 'payment_transactions', 'log_sessions', 'group_events') as $table) {
            XDB::execute('UPDATE  ' . $table . '
                             SET  uid = {?}
                           WHERE  uid = {?}',
                         $newuser->id(), $this->id());
        }

        // Merges user in following tables, ie updates when possible, then deletes remaining occurences of the old user.
        foreach (array('group_announces_read', 'group_event_participants', 'group_member_sub_requests', 'group_members', 'email_redirect_account') as $table) {
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
                     $lastSession, $newuser->id());
        XDB::execute('DELETE FROM  accounts
                            WHERE  uid = {?}',
                     $this->id());

        return true;
    }

    // Return permission flags for a given permission level.
    public static function makePerms($perms, $is_admin)
    {
        $flags = new PlFlagSet($perms);
        if ($is_admin) {
            $flags->addFlag(PERMS_ADMIN);
        }

        // Access to private directory implies access to 'less'-private version.
        if ($flags->hasFlag('directory_private')) {
            $flags->addFlag('directory_ax');
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

    public static function makeHomonymHrmid($alias)
    {
        return 'h.' . $alias . '.' . Platal::globals()->mail->domain;
    }

    public static function isMainMailDomain($domain)
    {
        global $globals;

        $is_main_domain = false;
        foreach (self::$sub_mail_domains as $sub_domain) {
            $is_main_domain = $is_main_domain || $domain == ($sub_domain . $globals->mail->domain) || $domain == ($sub_domain . $globals->mail->domain2);
        }
        return $is_main_domain;
    }

    public static function isAliasMailDomain($domain)
    {
        global $globals;

        return $domain == $globals->mail->alias_dom || $domain == $globals->mail->alias_dom2;
    }

    // Implementation of the static email locality checker.
    public static function isForeignEmailAddress($email)
    {
        if (strpos($email, '@') === false) {
            return false;
        }

        list(, $domain) = explode('@', $email);
        return !(self::isMainMailDomain($domain) || self::isAliasMailDomain($domain));
    }

    /* Tries to find pending accounts with an hruid close to $login. */
    public static function getPendingAccounts($login, $iterator = false)
    {
        if (strpos($login, '@') === false) {
            return null;
        }

        list($login, $domain) = explode('@', $login);

        if ($domain && !self::isMainMailDomain($domain)) {
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
