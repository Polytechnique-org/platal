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

// UserNotFound is raised when a given id cannot be linked to an existing user.
// The @p results give the list hruids (useful when several users are found).
class UserNotFoundException extends Exception
{
    public function __construct($results = array())
    {
        $this->results = $results;
        parent::__construct();
    }
}

// Represents an user of the system, with a special focus on its identification
// (hruid, forlife, and bestalias).
// Note: each implementation of platal-core MUST create a subclass 'User' of
// this abstract CoreUser class.
abstract class CoreUser
{
    // User's data storage. By convention, null means the information hasn't
    // been fetched yet, and false means the information is not available.

    // Main (unique) identifiers.
    private $user_id = null;
    private $hruid = null;

    // User's emails (bestalias should be preferred when sending emails).
    private $forlife = null;
    private $bestalias = null;


    // Constructs the object from an identifier (hruid/uid/email alias/email
    // redirection) and an optionnal array of known user properties.
    public function __construct($login, $values = array())
    {
        list($this->user_id, $this->hruid) = $this->getLogin($login);
        $this->fillFromArray($values);
    }


    // Properties accessors.
    public function id()
    {
        return $this->user_id;
    }

    public function login()
    {
        return $this->hruid;
    }

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


    // Determines if the @p id is a valid identifier; if so, returns the user_id
    // and the hruid. Otherwise raises UserNotFoundException.
    private function getLogin($login)
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

    // Fills the object from associative arrays containing our data.
    // The use case is for arrays got directly from anoter SQL request.
    private function fillFromArray(array $values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key) && !isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }


    // Returns a valid User object built from the @p id and optionnal @p values,
    // or returns false and calls the callback if the @p id is not valid.
    public static function get($login, $callback = false)
    {
        return User::getWithValues($login, array(), $callback);
    }

    public static function getWithValues($login, $values, $callback = false)
    {
        if (!$callback) {
            $callback = array(__CLASS__, '_default_user_callback');
        }

        try {
            return new User($login, $values);
        } catch (UserNotFoundException $e) {
            return call_user_func($callback, $login, $e->results);
        }
    }

    // Alias on get() with the silent callback.
    public static function getSilent($login)
    {
        return User::getWithValues($login, array(), array(__CLASS__, '_silent_user_callback'));
    }

    // Returns the forlife emails for @p members. If @p strict mode is enabled,
    // it returns the list of validated forlife emails. If strict mode is not,
    // it also returns unvalidated values (but still call the callback for them).
    public static function getBulkForlifeEmails($logins, $strict = true, $callback = false)
    {
        if (!is_array($logins)) {
            if (strlen(trim($logins)) == 0) {
                return null;
            }
            $logins = explode(' ', $logins);
        }

        if ($logins) {
            $list = array();
            foreach ($logins as $i => $login) {
                if (($user = User::get($login, $callback))) {
                    $list[$i] = $user->forlifeEmail();
                } else if(!$strict) {
                    $list[$i] = $login;
                }
            }
            return $list;
        }
        return null;
    }

    // Silent callback for the user lookup -- does nothing.
    public static function _silent_user_callback($login, $results)
    {
        return;
    }

    // Default callback for user lookup -- displays an error message w.r.t. the
    // number of matching users found.
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
