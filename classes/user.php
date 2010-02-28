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

            /** TODO: implements this by inspecting the profile.
            if (preg_match('/^(.*)\.([0-9]{4})$/u', $mbox, $matches)) {
                $res = XDB::query('SELECT  a.uid
                                     FROM  accounts AS a
                               INNER JOIN  aliases AS al ON (al.id = a.uid AND al.type IN ('alias', 'a_vie'))
                                    WHERE  al.alias = {?} AND a.promo = {?}', $matches[1], $matches[2]);
                if ($res->numRows() == 1) {
                    return $res->fetchOneCell();
                }
            }*/

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
                                      CONCAT(af.alias, \'@' . $globals->mail->domain . '\') AS forlife,
                                      CONCAT(af.alias, \'@' . $globals->mail->domain2 . '\') AS forlife_alternate,
                                      CONCAT(ab.alias, \'@' . $globals->mail->domain . '\') AS bestalias,
                                      CONCAT(ab.alias, \'@' . $globals->mail->domain2 . '\') AS bestalias_alternate,
                                      a.full_name, a.display_name, a.sex = \'female\' AS gender,
                                      IF(a.state = \'active\', at.perms, \'\') AS perms,
                                      a.email_format, a.is_admin, a.state, a.type, a.skin,
                                      FIND_IN_SET(\'watch\', a.flags) AS watch, a.comment,
                                      a.weak_password IS NOT NULL AS weak_access,
                                      a.token IS NOT NULL AS token_access,
                                      (e.email IS NULL AND NOT FIND_IN_SET(\'googleapps\', eo.storage)) AND a.state != \'pending\' AS lost
                                      ' . $fields . '
                                FROM  accounts AS a
                          INNER JOIN  account_types AS at ON (at.type = a.type)
                           LEFT JOIN  aliases AS af ON (af.uid = a.uid AND af.type = \'a_vie\')
                           LEFT JOIN  aliases AS ab ON (ab.uid = a.uid AND FIND_IN_SET(\'bestalias\', ab.flags))
                           LEFT JOIN  aliases AS ah ON (ah.uid = a.uid AND ah.type = \'homonyme\')
                           LEFT JOIN  emails AS e ON (e.uid = a.uid AND e.flags = \'active\')
                           LEFT JOIN  email_options AS eo ON (eo.uid = a.uid)
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
        $this->fillFromArray(self::loadMainFieldsFromUIDs(array($this->user_id))->next());
    }

    // Specialization of the fillFromArray method, to implement hacks to enable
    // lazy loading of user's main properties from the session.
    // TODO(vzanotti): remove the conversion hacks once the old codebase will
    // stop being used actively.
    protected function fillFromArray(array $values)
    {
        // It might happen that the 'user_id' field is called uid in some places
        // (eg. in sessions), so we hard link uid to user_id to prevent useless
        // SQL requests.
        if (!isset($values['user_id']) && isset($values['uid'])) {
            $values['user_id'] = $values['uid'];
        }

        // Also, if display_name and full_name are not known, but the user's
        // surname and last name are, we can construct the former two.
        if (isset($values['prenom']) && isset($values['nom'])) {
            if (!isset($values['display_name'])) {
                $values['display_name'] = ($values['prenom'] ? $values['prenom'] : $values['nom']);
            }
            if (!isset($values['full_name'])) {
                $values['full_name'] = $values['prenom'] . ' ' . $values['nom'];
            }
        }

        // We also need to convert the gender (usually named "femme"), and the
        // email format parameter (valued "texte" instead of "text").
        if (isset($values['femme'])) {
            $values['gender'] = (bool) $values['femme'];
        }
        if (isset($values['mail_fmt'])) {
            $values['email_format'] = $values['mail_fmt'];
        }

        parent::fillFromArray($values);
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

    /** Return the main profile attached with this account if any.
     */
    public function profile()
    {
        if (!$this->_profile_fetched) {
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
        if (isset($this->last_known_email)) {
            return;
        }
        $infos = XDB::fetchOneAssoc('SELECT  IF (MAX(m.last) > p.relance, MAX(m.last), p.relance) AS last_relance,
                                             p.email AS last_known_email
                                       FROM  register_pending AS p
                                  LEFT JOIN  register_marketing AS m ON (p.uid = m.uid)
                                      WHERE  p.uid = {?}
                                   GROUP BY  p.uid', $this->id());
        if (!$infos) {
            $infos = array('last_relance' => null, 'last_known_email' => null);
        }
        $this->fillFromArray($infos);
    }

    public function lastMarketingRelance()
    {
        $this->fetchMarketingData();
        return $this->last_relance;
    }

    public function lastKnownEmail()
    {
        $this->fetchMarketingData();
        return $this->last_known_email;
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
        return self::iterOverUIDs(array_keys($this->contacts));
    }

    public function getContacts()
    {
        $this->fetchContacts();
        return self::getBulkUsersWithUIDs(array_keys($this->contacts));
    }

    public function isContact(PlUser &$user)
    {
        $this->fetchContacts();
        return isset($this->contacts[$user->id()]);
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
            $profile = $profiles->next();
        }

        /** We iterate through the users, moving in
         * profiles when they match the user ID :
         * there can be users without a profile, but not
         * the other way around.
         */
        while (($user = $users->next())) {
            if ($fetchProfile) {
                if ($profile->owner_id == $user->id()) {
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
