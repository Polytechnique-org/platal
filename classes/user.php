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
    // Implementation of properties accessors.
    public function bestEmail()
    {
        if (!isset($this->bestalias)) {
            global $globals;
            $res = XDB::query("SELECT  CONCAT(alias, '@{$globals->mail->domain}')
                                 FROM  aliases
                                WHERE  FIND_IN_SET('bestalias', flags)
                                       AND id = {?}", $this->user_id);
            $this->bestalias = $res->numRows() ? $res->fetchOneCell() : false;
        }
        return $this->bestalias;
    }

    public function forlifeEmail()
    {
        if (!isset($this->forlife)) {
            global $globals;
            $res = XDB::query("SELECT  CONCAT(alias, '@{$globals->mail->domain}')
                                 FROM  aliases
                                WHERE  type = 'a_vie' AND id = {?}", $this->user_id);
            $this->forlife = $res->numRows() ? $res->fetchOneCell() : false;
        }
        return $this->forlife;
    }

    // Implementation of the login to array(user_id, hruid) function.
    protected function getLogin($login)
    {
        global $globals;

        // If $data is an integer, fetches directly the result.
        if (is_numeric($login)) {
            $res = XDB::query("SELECT user_id, hruid FROM auth_user_md5 WHERE user_id = {?}", $login);
            if ($res->numRows()) {
                return $res->fetchOneRow();
            }

            throw new UserNotFoundException();
        }

        // Checks whether $login is a valid hruid or not.
        $res = XDB::query("SELECT user_id, hruid FROM auth_user_md5 WHERE hruid = {?}", $login);
        if ($res->numRows()) {
            return $res->fetchOneRow();
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
            $res = XDB::query("SELECT  u.user_id, u.hruid
                                 FROM  auth_user_md5 AS u
                           INNER JOIN  aliases AS a ON (a.id = u.user_id AND a.type IN ('alias', 'a_vie'))
                                WHERE  a.alias = {?}", $mbox);
            if ($res->numRows()) {
                return $res->fetchOneRow();
            }

            if (preg_match('/^(.*)\.([0-9]{4})$/u', $mbox, $matches)) {
                $res = XDB::query("SELECT  u.user_id, u.hruid
                                     FROM  auth_user_md5 AS u
                               INNER JOIN  aliases AS a ON (a.id = u.user_id AND a.type IN ('alias', 'a_vie'))
                                    WHERE  a.alias = {?} AND u.promo = {?}", $matches[1], $matches[2]);
                if ($res->numRows() == 1) {
                    return $res->fetchOneRow();
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
                $res = XDB::query("SELECT  u.user_id, u.hruid
                                     FROM  auth_user_md5 AS u
                                LEFT JOIN  aliases AS a ON (a.id = u.user_id AND a.type IN ('alias', 'a_vie'))
                                    WHERE  a.alias = {?}", $alias);
                if ($res->numRows()) {
                    return $res->fetchOneRow();
                }
            }

            throw new UserNotFoundException();
        }

        // Otherwise, we do suppose $login is an email redirection.
        $res = XDB::query("SELECT  u.user_id, u.hruid
                             FROM  auth_user_md5 AS u
                        LEFT JOIN  emails AS e ON (e.uid = u.user_id)
                            WHERE  e.email = {?}", $login);
        if ($res->numRows() == 1) {
            return $res->fetchOneRow();
        }

        throw new UserNotFoundException($res->fetchColumn(1));
    }

    // Implementation of the default user callback.
    public static function _default_user_callback($login, $results)
    {
        global $page;

        $result_count = count($results);
        if ($result_count == 0 || !S::has_perms()) {
            $page->trigError("Il n'y a pas d'utilisateur avec l'identifiant : $login");
        } else {
            $page->trigError("Il y a $result_count utilisateurs avec cet identifiant : " . join(', ', $results));
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
