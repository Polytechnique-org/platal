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
    // Implementation of the login to uid method.
    protected function getLogin($login)
    {
        global $globals;

        // If $data is an integer, fetches directly the result.
        if (is_numeric($login)) {
            $res = XDB::query("SELECT user_id FROM auth_user_md5 WHERE user_id = {?}", $login);
            if ($res->numRows()) {
                return $res->fetchOneCell();
            }

            throw new UserNotFoundException();
        }

        // Checks whether $login is a valid hruid or not.
        $res = XDB::query("SELECT user_id FROM auth_user_md5 WHERE hruid = {?}", $login);
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
            $res = XDB::query("SELECT  u.user_id
                                 FROM  auth_user_md5 AS u
                           INNER JOIN  aliases AS a ON (a.id = u.user_id AND a.type IN ('alias', 'a_vie'))
                                WHERE  a.alias = {?}", $mbox);
            if ($res->numRows()) {
                return $res->fetchOneCell();
            }

            if (preg_match('/^(.*)\.([0-9]{4})$/u', $mbox, $matches)) {
                $res = XDB::query("SELECT  u.user_id
                                     FROM  auth_user_md5 AS u
                               INNER JOIN  aliases AS a ON (a.id = u.user_id AND a.type IN ('alias', 'a_vie'))
                                    WHERE  a.alias = {?} AND u.promo = {?}", $matches[1], $matches[2]);
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
                $res = XDB::query("SELECT  u.user_id
                                     FROM  auth_user_md5 AS u
                                LEFT JOIN  aliases AS a ON (a.id = u.user_id AND a.type IN ('alias', 'a_vie'))
                                    WHERE  a.alias = {?}", $alias);
                if ($res->numRows()) {
                    return $res->fetchOneCell();
                }
            }

            throw new UserNotFoundException();
        }

        // Otherwise, we do suppose $login is an email redirection.
        $res = XDB::query("SELECT  u.user_id
                             FROM  auth_user_md5 AS u
                        LEFT JOIN  emails AS e ON (e.uid = u.user_id)
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
            && $this->full_name !== null && $this->promo !== null && $this->perms !== null
            && $this->gender !== null && $this->email_format !== null) {
            return;
        }

        global $globals;
        $res = XDB::query("SELECT  u.hruid, u.promo,
                                   CONCAT(af.alias, '@{$globals->mail->domain}') AS forlife,
                                   CONCAT(ab.alias, '@{$globals->mail->domain}') AS bestalias,
                                   CONCAT(u.prenom, ' ', IF(u.nom_usage <> '', u.nom_usage, u.nom)) AS full_name,
                                   IF(u.prenom != '', u.prenom, u.nom) AS display_name,
                                   FIND_IN_SET('femme', u.flags) AS gender,
                                   q.core_mail_fmt AS email_format,
                                   u.perms
                             FROM  auth_user_md5 AS u
                        LEFT JOIN  auth_user_quick AS q ON (q.user_id = u.user_id)
                        LEFT JOIN  aliases AS af ON (af.id = u.user_id AND af.type = 'a_vie')
                        LEFT JOIN  aliases AS ab ON (ab.id = u.user_id AND FIND_IN_SET('bestalias', ab.flags))
                            WHERE  u.user_id = {?}", $this->user_id);
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
        if (isset($values['email_format'])) {
            $values['email_format'] = ($values['email_format'] ? self::FORMAT_HTML : self::FORMAT_TEXT);
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
        $this->perm_flags = self::makePerms($this->perms);
    }

    // Return permission flags for a given permission level.
    public static function makePerms($perms)
    {
        $flags = new PlFlagSet();
        if (is_null($flags) || $perms == 'disabled' || $perms == 'ext') {
            return $flags;
        }
        $flags->addFlag(PERMS_USER);
        if ($perms == 'admin') {
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
