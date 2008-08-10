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

/**
 * PlUserNotFound is raised when a user id cannot be linked to a real user.
 * The @p results give the list hruids (useful when several users are found).
 */
class UserNotFoundException extends Exception
{
    public function __construct($results = array())
    {
        $this->results = $results;
        parent::__construct();
    }
}

/**
 * Represents an user of plat/al (without any further assumption), with a
 * special focus on always-used properties (identification fields, display name,
 * forlife/bestalias emails, ...).
 * NOTE: each implementation of plat/al-code MUST subclass PlUser, and name it
 * 'User'.
 */
abstract class PlUser
{
    /**
     * User data storage.
     * By convention, null means the information hasn't been fetched yet, and
     * false means the information is not available.
     */
    protected $user_id = null;
    protected $hruid = null;

    // User main email aliases (forlife is the for-life email address, bestalias
    // is user-chosen preferred email address).
    protected $forlife = null;
    protected $bestalias = null;

    // Display name is user-chosen name to display (eg. in "Welcome
    // <display name> !"), while full name is the official full name.
    protected $display_name = null;
    protected $full_name = null;
    protected $promo = null;

    // Permissions
    protected $perms = null;
    protected $perm_flags = null;

    // Other properties are listed in this key-value hash map.
    protected $data = array();

    /**
     * Constructs the PlUser object from an identifier (any identifier which is
     * understood by getLogin() implementation).
     *
     * @param $login An user login.
     * @param $values List of known user properties.
     */
    public function __construct($login, $values = array())
    {
        $this->fillFromArray($values);

        // If the user id was not part of the known values, determines it from
        // the login.
        if (!$this->user_id) {
            $this->user_id = $this->getLogin($login);
        }

        // Preloads main properties (assumes the loader will lazily get them
        // from variables already set in the object).
        $this->loadMainFields();
    }

    /**
     * Get the canonical user id for the @p login.
     *
     * @param $login An user login.
     * @return The canonical user id.
     * @throws UserNotFoundException when login is not found.
     */
    abstract protected function getLogin($login);

    /**
     * Loads the main properties (hruid, forlife, bestalias, ...) from the
     * database. Should return immediately when the properties are already
     * available.
     */
    abstract protected function loadMainFields();

    /**
     * Accessors to the main properties, ie. those available as top level
     * object variables.
     */
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
        return $this->bestalias;
    }
    public function forlifeEmail()
    {
        return $this->forlife;
    }

    public function displayName()
    {
        return $this->display_name;
    }
    public function fullName()
    {
        return $this->full_name;
    }

    /**
     * Other properties are available directly through the $data array, or as
     * standard object variables, using a getter.
     */
    public function data()
    {
        return $this->data;
     }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    public function __isset($name)
    {
        return isset($this->$name) || isset($this->data[$name]);
    }

    /**
     * Fills the object properties using the @p associative array; the intended
     * user case is to fill the object using SQL obtained arrays.
     *
     * @param $values Key-value array of user properties.
     */
    protected function fillFromArray(array $values)
    {
        // Merge main properties with existing ones.
        unset($values['data']);
        foreach ($values as $key => $value) {
            if (property_exists($this, $key) && !isset($this->$key)) {
                $this->$key = $value;
            }
        }

        // Merge all value into the $this->data placeholder.
        $this->data = array_merge($this->data, $values);
    }


    /**
     * Build the permissions flags for the user.
     */
    abstract protected function buildPerms();

    /**
     * Check wether the user got the given permission combination.
     */
    public function checkPerms($perms)
    {
        if (is_null($this->perm_flags)) {
            $this->buildPerms();
        }
        if (is_null($this->perm_flags)) {
            return false;
        }
        return $this->perm_flags->hasFlagCombination($perms);
    }


    /**
     * Returns a valid User object built from the @p id and optionnal @p values,
     * or returns false and calls the callback if the @p id is not valid.
     */
    public static function get($login, $callback = false)
    {
        return User::getWithValues($login, array(), $callback);
    }

    public static function getWithValues($login, $values, $callback = false)
    {
        if (!$callback) {
            $callback = array('User', '_default_user_callback');
        }

        try {
            return new User($login, $values);
        } catch (UserNotFoundException $e) {
            return call_user_func($callback, $login, $e->results);
        }
    }

    // Same as above, but using the silent callback as default.
    public static function getSilent($login)
    {
        return User::getWithValues($login, array(), array('User', '_silent_user_callback'));
    }

    public static function getSilentWithValues($login, $values)
    {
        return User::getWithValues($login, $values, array('User', '_silent_user_callback'));
    }

    /**
     * Retrieves User objects corresponding to the @p logins, and eventually
     * extracts and returns the @p property. If @p strict mode is disabled, it
     * also includes logins for which no forlife was found (but it still calls
     * the callback for them).
     * In all cases, email addresses which are not from the local domains are
     * kept.
     *
     * @param $logins Array of user logins.
     * @param $property Property to retrieve from the User objects.
     * @param $strict Should unvalidated logins be returned as-is or discarded ?
     * @param $callback Callback to call when a login is unknown to the system.
     * @return Array of validated user forlife emails.
     */
    private static function getBulkUserProperties($logins, $property, $strict, $callback)
    {
        if (!is_array($logins)) {
            if (strlen(trim($logins)) == 0) {
                return null;
            }
            $logins = split("[; ,\r\n\|]+", $logins);
        }

        if ($logins) {
            $list = array();
            foreach ($logins as $i => $login) {
                $login = trim($login);
                if (empty($login)) {
                    continue;
                }

                if (($user = User::get($login, $callback))) {
                    $list[$i] = $user->$property();
                } else if (!$strict || User::isForeignEmailAddress($login)) {
                    $list[$i] = $login;
                }
            }
            return $list;
        }
        return null;
    }

    /**
     * Returns hruid corresponding to the @p logins. See getBulkUserProperties()
     * for details.
     */
    public static function getBulkHruid($logins, $callback = false)
    {
        return self::getBulkUserProperties($logins, 'login', true, $callback);
    }

    /**
     * Returns forlife emails corresponding to the @p logins. See
     * getBulkUserProperties() for details.
     */
    public static function getBulkForlifeEmails($logins, $strict = true, $callback = false)
    {
        return self::getBulkUserProperties($logins, 'forlifeEmail', $strict, $callback);
    }

    /**
     * Predefined callbacks for the user lookup; they are called when a given
     * login is found not to be associated with any valid user. Silent callback
     * does nothing; default callback is supposed to display an error message,
     * using the Platal::page() hook.
     */
    public static function _silent_user_callback($login, $results)
    {
        return;
    }

    abstract public static function _default_user_callback($login, $results);

    /**
     * Determines if the @p login is an email address, and an email address not
     * served locally by plat/al.
     */
    abstract public static function isForeignEmailAddress($email);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
