<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

    // Implementation of the login to uid method.
    protected function getLogin($login)
    {
        global $globals;

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
                           INNER JOIN  aliases AS al ON (al.id = a.uid AND al.type IN (\'alias\', \'a_vie\'))
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
                                LEFT JOIN  aliases AS al ON (al.id = a.uid AND al.type IN ('alias', 'a_vie'))
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

    // Implementation of the data loader.
    protected function loadMainFields()
    {
        if ($this->hruid !== null && $this->forlife !== null
            && $this->bestalias !== null && $this->display_name !== null
            && $this->full_name !== null && $this->perms !== null
            && $this->gender !== null && $this->email_format !== null) {
            return;
        }

        global $globals;
        /** TODO: promo stuff again */
        /** TODO: fix perms field to fit new perms system */
        $res = XDB::query("SELECT  a.hruid, a.registration_date,
                                   CONCAT(af.alias, '@{$globals->mail->domain}') AS forlife,
                                   CONCAT(ab.alias, '@{$globals->mail->domain}') AS bestalias,
                                   a.full_name, a.display_name, a.sex = 'female' AS gender,
                                   IF(a.state = 'active', at.perms, '') AS perms,
                                   a.email_format, a.is_admin, a.state, a.type, a.skin,
                                   FIND_IN_SET('watch', a.flags) AS watch, a.comment,
                                   a.weak_password IS NOT NULL AS weak_access,
                                   a.token IS NOT NULL AS token_access
                             FROM  accounts AS a
                       INNER JOIN  account_types AS at ON (at.type = a.type)
                        LEFT JOIN  aliases AS af ON (af.id = a.uid AND af.type = 'a_vie')
                        LEFT JOIN  aliases AS ab ON (ab.id = a.uid AND FIND_IN_SET('bestalias', ab.flags))
                            WHERE  a.uid = {?}", $this->user_id);
        $this->fillFromArray($res->fetchOneAssoc());
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
    public function emailAliases($domain = null)
    {
        $where = '';
        if (!is_null($domain)) {
            $where = XDB::format(' AND alias LIKE CONCAT("%@", {?})', $domain);
        }
        return XDB::fetchColumn('SELECT  v.alias
                                   FROM  virtual AS v
                             INNER JOIN  virtual_redirect AS vr ON (v.vid = vr.vid)
                                  WHERE  (vr.redirect = {?} OR vr.redirect = {?})
                                         ' . $where,
                               $this->forlifeEmail(), $this->m4xForlifeEmail());
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
        if ($result_count == 0 || !S::has_perms()) {
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
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
