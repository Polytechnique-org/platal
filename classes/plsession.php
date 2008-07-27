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

/** Authentication level.
 * Only AUTH_PUBLIC is mandatory. The others are defined as useful values,
 * but can be overwritten by others auth levels definitions.
 */
define('AUTH_PUBLIC', 0);
define('AUTH_COOKIE', 1);
define('AUTH_MDP',    2);


/** The PlSession is a wrapper around the user session management.
 */
abstract class PlSession
{
    /** Build a new session object.
     * If a session is already started, this just wraps the session... if no
     * session is currently started, this set the base session variables.
     *  * auth        contains the current authentication level. The level is
     *                an integer that grows with the security of the authentication
     *                method.
     *  * perms       contains the current permission flags of the user. This flags
     *                depends on the category of the user.
     *  * challenge   contains a random uniq id for authentication.
     *  * xsrf_token  contains a random uniq id for xsrf prevention.
     *  * user        contains a reference to the current user.
     */
    public function __construct()
    {
        $this->create();
    }

    /** Build the session structure with system fields.
     */
    private function fillSession()
    {
        S::bootstrap('user', null);
        S::bootstrap('auth', AUTH_PUBLIC);
        S::bootstrap('challenge', sha1(uniqid(rand(), true)));
        S::bootstrap('xsrf_token', rand_url_id());
        S::bootstrap('perms', new PlFlagSet());
    }

    /** Write current session and close it.
     */
    public function close()
    {
        session_write_close();
    }

    /** Create a new session
     */
    private function create()
    {
        session_start();
        $this->fillSession();
    }

    /** Kill the current session.
     */
    public function destroy()
    {
        session_destroy();
        unset($_SESSION);
        $this->create();
    }

    /** Check if the user has at least the given authentication level.
     */
    public function checkAuth($level)
    {
        return S::i('auth') >= $level;
    }

    /** Check if the user has the given permissions.
     */
    public function checkPerms($perms)
    {
        return S::v('perms')->hasFlagCombination($perms);
    }

    /** Run authentication procedure to reach at least the given level.
     */
    public function start($level)
    {
        $backup = S::i($level);
        if ($this->checkAuth($level)) {
            return true;
        }
        $user = $this->doAuth($level);
        if (is_null($user)) {
            return false;
        }
        if (!$this->checkAuth($level)) {
            $this->destroy();
            return false;
        }
        if ($this->startSessionAs($user, $level)) {
            if (is_null(S::v('user'))) {
                S::set('user', $user);
            }
            return true;
        } else {
            $this->destroy();
        }
        return false;
    }


    /*** Abstract methods ***/

    /** Function that check authentication at build time of the session object.
     * This is useful to perform authentication from a cookie or when coming
     * back from a authentication service.
     *
     * This function must NOT try to launch a new authenticatioin procedure. It
     * just tests if the environment contains sufficient information to start
     * a user session.
     *
     * This function return false if informations are available but lead to an
     * authentication failure (invalid cookie, invalid service return data...)
     */
    abstract public function startAvailableAuth();

    /** Run the effectively authentication procedure to reach the given user.
     * This method must return a user object (that will be used to fill the
     * $_SESSION['user'] field).
     *
     * If auth failed, the function MUST return null. If auth succeed, the
     * field $_SESSION['auth'] MUST be filled to the current effective level.
     */
    abstract protected function doAuth($level);

    /** Set the session environment to the given user and authentication level.
     * This function MUST return false if a session is already started and the
     * user mismatch.
     *
     * On succes, this function MUST return true.
     * If $level is set to -1, this means you are building a new SUID session.
     */
    abstract protected function startSessionAs($user, $level);

    /** Check authentication with the given token.
     *
     * Token authentication is a light-weight authentication based on a user-specific token.
     * This can be used for protocols that requires a 'cookie'-free authentication, such as
     * RSS, iCal registration...
     *
     * This function returns a valid user object if authentication is successful, or null if
     * token mismatch.
     */
    abstract public function tokenAuth($login, $token);


    /*** SUID management ***/

    /** Start a new SUID session.
     */
    public function startSUID($user)
    {
        if (S::has('suid')) {
            return false;
        }
        $backup   = $_SESSION;
        $_SESSION = array();
        $this->fillSession();
        S::set('suid', $backup);
        if (!$this->startSessionAs($user, -1)) {
            $this->stopSUID();
            return false;
        }
        S::set('user', $user);
        return true;
    }

    /** Stop a SUID session
     */
    public function stopSUID()
    {
        if (!S::has('suid')) {
            return false;
        }
        $_SESSION = $_SESSION['suid'];
        return true;
    }


    /*** Thresholds ***/

    /** Minimum level of authentication that is considered as sure.
     */
    abstract public function sureLevel();
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
