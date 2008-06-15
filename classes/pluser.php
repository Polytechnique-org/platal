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

// PlUserNotFound is raised when a given id cannot be linked to an existing user.
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
// this abstract PlUser class.
abstract class PlUser
{
    // User's data storage. By convention, null means the information hasn't
    // been fetched yet, and false means the information is not available.

    // Main (unique) identifiers.
    protected $user_id = null;
    protected $hruid = null;

    // User's emails (bestalias should be preferred when sending emails).
    protected $forlife = null;
    protected $bestalias = null;


    // Constructs the object from an identifier (hruid/uid/email alias/email
    // redirection) and an optionnal array of known user properties.
    public function __construct($login, $values = array())
    {
        $this->fillFromArray($values);
        if (!isset($this->user_id) || !isset($this->hruid)) {
            list($this->user_id, $this->hruid) = $this->getLogin($login);
        }
    }


    // Properties accessors.
    public function id() { return $this->user_id; }
    public function login() { return $this->hruid; }
    abstract public function bestEmail();
    abstract public function forlifeEmail();


    // Determines if the @p id is a valid identifier; if so, returns the user_id
    // and the hruid. Otherwise raises UserNotFoundException.
    abstract protected function getLogin($login);

    // Fills the object from associative arrays containing our data.
    // The use case is for arrays got directly from anoter SQL request.
    protected function fillFromArray(array $values)
    {
        // It might happen that the 'user_id' field is called uid in some places
        // (eg. in sessions), so we hard link uid to user_id to prevent useless
        // SQL requests.
        if (!isset($values['user_id']) && isset($values['uid'])) {
            $values['user_id'] = $values['uid'];
        }

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
    abstract public static function _default_user_callback($login, $results);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
